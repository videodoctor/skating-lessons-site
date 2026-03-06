<?php

namespace App\Console\Commands;

use App\Models\Rink;
use App\Models\RinkSession;
use App\Models\TimeSlot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;

class ScrapeRinkSchedules extends Command
{
    protected $signature = 'scrape:rink-schedules {rink?}';
    protected $description = 'Scrape public skate schedules from rink websites';

    public function handle()
    {
        $rinkSlug = $this->argument('rink');

        $rinks = $rinkSlug
            ? Rink::where('slug', $rinkSlug)->where('is_active', true)->get()
            : Rink::where('is_active', true)->get();

        if ($rinks->isEmpty()) {
            $this->error('No active rinks found.');
            return 1;
        }

        foreach ($rinks as $rink) {
            $this->info("Scraping {$rink->name}...");

            try {
                switch ($rink->slug) {
                    case 'creve-coeur':
                        $this->scrapeCreveCoeur($rink);
                        break;
                    case 'brentwood':
                        $this->scrapeBrentwood($rink);
                        break;
                    case 'webster-groves':
                        $this->scrapeWebsterGroves($rink);
                        break;
                    case 'kirkwood':
                        $this->scrapeKirkwood($rink);
                        break;
                    case 'maryville':
                        $this->scrapeMaryville($rink);
                        break;
                    default:
                        $this->warn("No scraper implemented for {$rink->slug}");
                }

                $rink->update(['last_scraped_at' => now()]);
                $this->info("✓ Completed {$rink->name}");

            } catch (\Exception $e) {
                $this->error("✗ Failed to scrape {$rink->name}: {$e->getMessage()}");
                Log::error("Scraper error for {$rink->slug}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\nGenerating time slots...");
        $this->generateTimeSlotsFromSessions();

        $this->info("\nScraping complete!");
        return 0;
    }

    /**
     * Clear all future unbooked slots for a rink before re-scraping.
     * Slots with an active booking are preserved.
     */
    private function clearFutureUnbookedSlots(Rink $rink): int
    {
        $deleted = TimeSlot::where('rink_id', $rink->id)
            ->where('date', '>=', today())
            ->whereNull('booking_id')
            ->delete();

        $this->info("  Cleared {$deleted} existing unbooked future slot(s) for {$rink->name}");
        return $deleted;
    }

    /**
     * Clear all future rink sessions for a rink before re-scraping.
     * We always re-derive sessions from the freshly scraped schedule.
     */
    private function clearFutureSessions(Rink $rink): int
    {
        $deleted = RinkSession::where('rink_id', $rink->id)
            ->where('date', '>=', today())
            ->delete();

        $this->info("  Cleared {$deleted} existing future session(s) for {$rink->name}");
        return $deleted;
    }

    private function scrapeCreveCoeur(Rink $rink)
    {
        $response = Http::get($rink->schedule_url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch page");
        }

        $html = $response->body();

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $images = $xpath->query('//img[contains(@alt, "Public 20")]');

        if ($images->length === 0) {
            $this->warn("No schedule images found for Creve Coeur");
            return;
        }

        // Collect all document IDs found on the page
        $foundDocIds = [];
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if (preg_match('/documentID=(\d+)/', $src, $docMatch)) {
                $foundDocIds[] = $docMatch[1];
            }
        }

        // Clear future unbooked slots ONCE before processing all months
        $this->clearFutureUnbookedSlots($rink);
        $this->clearFutureSessions($rink);

        foreach ($images as $img) {
            $altText = $img->getAttribute('alt');
            $src = $img->getAttribute('src');

            if (preg_match('/(January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+Public\s+(\d{4})/i', $altText, $matches)) {
                $monthName = $matches[1];
                $year = $matches[2];

                if (preg_match('/documentID=(\d+)/', $src, $docMatch)) {
                    $documentId = $docMatch[1];

                    $imageUrl = $src;
                    if (!str_starts_with($src, 'http')) {
                        $imageUrl = 'https://www.crevecoeurmo.gov' . $src;
                    }

                    // Only skip if cached AND it's not a future month
                    // (always re-process current month in case schedule changed)
                    $docCacheKey = "processed_creve_coeur_doc_{$documentId}";
                    $isCurrentOrFutureMonth = Carbon::create($year, date('n', strtotime($monthName . ' 1')), 1)->gte(now()->startOfMonth());

                    if (cache()->has($docCacheKey) && !$isCurrentOrFutureMonth) {
                        $this->info("  Skipping {$monthName} {$year} (Doc ID: {$documentId}) - past month, already processed");
                        continue;
                    }

                    $this->info("  Processing {$monthName} {$year} (Doc ID: {$documentId})...");

                    try {
                        $this->processCreveCoeurSchedule($rink, $imageUrl, $monthName, $year);
                        cache([$docCacheKey => true], now()->addDays(60));
                    } catch (\Exception $e) {
                        $this->error("    Failed: {$e->getMessage()}");
                    }
                }
            }
        }
    }

    private function processCreveCoeurSchedule(Rink $rink, string $imageUrl, string $monthName, int $year)
    {
        $cacheKey = 'paddle_html_' . md5($imageUrl);
        $htmlContent = cache($cacheKey);

        if (!$htmlContent) {
            $this->info("    Running PaddleOCR (not cached)...");

            $tempImage = tempnam(sys_get_temp_dir(), 'schedule_') . '.jpg';
            file_put_contents($tempImage, file_get_contents($imageUrl));

            $outputDir = sys_get_temp_dir() . '/paddle_' . uniqid();
            mkdir($outputDir);

            $paddleCommand = "/home/ubuntu/paddle-env/bin/paddleocr table_recognition_v2 --input {$tempImage} --save_path {$outputDir} 2>&1";
            exec($paddleCommand, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("PaddleOCR failed: " . implode("\n", $output));
            }

            $pattern = $outputDir . DIRECTORY_SEPARATOR . '*_table_*.html';
            $htmlFiles = glob($pattern);

            if (empty($htmlFiles)) {
                throw new \Exception("No HTML table output found");
            }

            $htmlContent = file_get_contents($htmlFiles[0]);
            cache([$cacheKey => $htmlContent], now()->addDays(30));

            unlink($tempImage);
            $files = glob($outputDir . DIRECTORY_SEPARATOR . '*');
            if ($files !== false) array_map('unlink', $files);
            rmdir($outputDir);
        } else {
            $this->info("    Using cached PaddleOCR output");
        }

        $this->parseTableHtml($rink, $htmlContent, $monthName, $year);
    }

    private function parseTableHtml(Rink $rink, string $htmlContent, string $monthName, int $year)
    {
        $monthNumber = date('n', strtotime($monthName . ' 1'));

        $dom = new DOMDocument();
        @$dom->loadHTML($htmlContent);
        $xpath = new DOMXPath($dom);

        $rows = $xpath->query('//table/tbody/tr');
        $sessionsCreated = 0;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 0) continue;

            $cells = $xpath->query('.//td', $row);

            foreach ($cells as $cell) {
                $cellText = trim($cell->textContent);

                if (preg_match('/^(\d{1,2})\s+.*?Public\s+(\d{1,2}):(\d{2})([AP])-(\d{1,2}):(\d{2})([AP])/i', $cellText, $match)) {
                    $day = (int)$match[1];
                    $startHour = (int)$match[2];
                    $startMin = $match[3];
                    $startPeriod = strtoupper($match[4]);
                    $endHour = (int)$match[5];
                    $endMin = $match[6];
                    $endPeriod = strtoupper($match[7]);

                    if ($startPeriod === 'P' && $startHour !== 12) $startHour += 12;
                    if ($startPeriod === 'A' && $startHour === 12) $startHour = 0;
                    if ($endPeriod === 'P' && $endHour !== 12) $endHour += 12;
                    if ($endPeriod === 'A' && $endHour === 12) $endHour = 0;

                    try {
                        $date = Carbon::create($year, $monthNumber, $day);
                        $startTime = Carbon::create($year, $monthNumber, $day, $startHour, $startMin);
                        $endTime = Carbon::create($year, $monthNumber, $day, $endHour, $endMin);

                        $this->createSession($rink, $date, $startTime, $endTime);
                        $this->info("    Added: {$date->format('M d')} {$startTime->format('g:i A')} - {$endTime->format('g:i A')}");
                        $sessionsCreated++;
                    } catch (\Exception $e) {
                        $this->warn("    Skipped invalid date: {$day}");
                    }
                }
            }
        }

        if ($sessionsCreated === 0) {
            $this->warn("    No Public sessions found in table");
        } else {
            $this->info("    Created {$sessionsCreated} sessions for {$monthName} {$year}");
        }
    }

    private function scrapeBrentwood(Rink $rink)
    {
        $response = Http::get($rink->schedule_url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch page");
        }

        $html = $response->body();

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $links = $xpath->query('//a[contains(@href, "/DocumentCenter/View/")]');

        // Collect all document IDs on the page first
        $schedulesToProcess = [];
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $linkText = trim($link->textContent);

            if (preg_match('/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{4})/i', $linkText, $matches)) {
                $monthName = $matches[1];
                $year = (int)$matches[2];

                if (preg_match('/\/DocumentCenter\/View\/(\d+)/', $href, $docMatch)) {
                    $documentId = $docMatch[1];
                    $schedulesToProcess[$documentId] = [
                        'month' => $monthName,
                        'year'  => $year,
                        'url'   => 'https://www.brentwoodmo.org' . $href,
                        'doc_id'=> $documentId,
                    ];
                }
            }
        }

        if (empty($schedulesToProcess)) {
            $this->warn("  No schedule PDFs found for Brentwood");
            return;
        }

        // Clear future unbooked slots ONCE before processing
        $this->clearFutureUnbookedSlots($rink);
        $this->clearFutureSessions($rink);

        foreach ($schedulesToProcess as $documentId => $schedule) {
            $cacheKey = "processed_brentwood_doc_{$documentId}";
            $isCurrentOrFutureMonth = Carbon::create($schedule['year'], date('n', strtotime($schedule['month'] . ' 1')), 1)->gte(now()->startOfMonth());

            // Skip past months if already cached; always re-process current/future months
            if (cache()->has($cacheKey) && !$isCurrentOrFutureMonth) {
                $this->info("  Skipping {$schedule['month']} {$schedule['year']} (Doc ID: {$documentId}) - past month, already processed");
                continue;
            }

            $this->info("  Processing {$schedule['month']} {$schedule['year']} (Doc ID: {$documentId})...");

            try {
                $this->processBrentwoodPdf($rink, $schedule['url'], $schedule['month'], $schedule['year']);
                cache([$cacheKey => true], now()->addDays(60));
            } catch (\Exception $e) {
                $this->error("    Failed: {$e->getMessage()}");
            }
        }
    }

    private function processBrentwoodPdf(Rink $rink, string $pdfUrl, string $monthName, int $year)
    {
        $cacheKey = 'brentwood_pdf_text_' . md5($pdfUrl . $year . $monthName);
        $text = cache($cacheKey);

        if (!$text) {
            $this->info("    Downloading and parsing PDF...");
            $pdfContent = file_get_contents($pdfUrl);
            $tempPdf = tempnam(sys_get_temp_dir(), 'brentwood_') . '.pdf';
            file_put_contents($tempPdf, $pdfContent);
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($tempPdf);
            $text = $pdf->getText();
            unlink($tempPdf);
            cache([$cacheKey => $text], now()->addDays(7));
        } else {
            $this->info("    Using cached PDF text...");
        }

        $monthNumber = date('n', strtotime($monthName . ' 1'));
        $lines = array_map('trim', explode("\n", $text));
        $total = count($lines);
        $currentDate = null;
        $sessionsCreated = 0;

        for ($i = 0; $i < $total; $i++) {
            $line = $lines[$i];

            // Track current date: bare number on its own line
            if (preg_match('/^(\d{1,2})$/', $line, $dm)) {
                $d = (int)$dm[1];
                if ($d >= 1 && $d <= 31) {
                    $currentDate = $d;
                }
                continue;
            }

            // Found a Public Session line
            if ($currentDate && stripos($line, 'Public Session') !== false) {
                // Scan forward for the very next line that looks like a time range
                // Stop if we hit a bare date number (next day started)
                for ($j = $i + 1; $j < min($i + 6, $total); $j++) {
                    $tl = $lines[$j];
                    if ($tl === '') continue;
                    // New date — stop
                    if (preg_match('/^\d{1,2}$/', $tl)) break;
                    // Must start with digit:digit to be a time
                    if (!preg_match('/^\d{1,2}:/', $tl)) continue;
                    // Match full time range
                    if (preg_match('/^(\d{1,2}):(\d{2})\s*([ap]m?)?\s*-\s*(\d{1,2}):(\d{2})\s*([ap]m?)/i', $tl, $m)) {
                        $sh = (int)$m[1]; $sm = $m[2];
                        $eh = (int)$m[4]; $em = $m[5];
                        $ep = strtoupper(substr($m[6], 0, 1));
                        $sp = $m[3] !== '' ? strtoupper(substr($m[3], 0, 1)) : $ep;
                        if ($sp==='P' && $sh!==12) $sh+=12;
                        if ($sp==='A' && $sh===12) $sh=0;
                        if ($ep==='P' && $eh!==12) $eh+=12;
                        if ($ep==='A' && $eh===12) $eh=0;
                        try {
                            $date  = Carbon::create($year, $monthNumber, $currentDate);
                            $start = Carbon::create($year, $monthNumber, $currentDate, $sh, $sm);
                            $end   = Carbon::create($year, $monthNumber, $currentDate, $eh, $em);
                            $this->createSession($rink, $date, $start, $end);
                            $this->info("    Added: {$date->format('M d')} {$start->format('g:i A')} - {$end->format('g:i A')}");
                            $sessionsCreated++;
                        } catch (\Exception $e) {
                            $this->warn("    Skipped invalid date: {$currentDate}");
                        }
                        break; // done with this Public Session
                    }
                }
            }
        }

        if ($sessionsCreated === 0) {
            $this->warn("    No Public sessions found in PDF");
        } else {
            $this->info("    Created {$sessionsCreated} sessions for {$monthName} {$year}");
        }
    }

    private function scrapeWebsterGroves(Rink $rink)
    {
        $response = Http::get($rink->schedule_url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch page");
        }

        $html = $response->body();

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $links = $xpath->query('//a[contains(@href, "/DocumentCenter/View/")]');

        $schedulesToProcess = [];
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $linkText = trim($link->textContent);

            if (preg_match('/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(Skate\s+)?Calendar/i', $linkText, $matches)) {
                $monthName = $matches[1];
                $currentMonth = date('n');
                $matchedMonth = date('n', strtotime($monthName . ' 1'));
                $year = ($matchedMonth < $currentMonth) ? date('Y') + 1 : date('Y');

                if (preg_match('/\/DocumentCenter\/View\/(\d+)/', $href, $docMatch)) {
                    $documentId = $docMatch[1];
                    $schedulesToProcess[$documentId] = [
                        'month'  => $monthName,
                        'year'   => $year,
                        'url'    => 'https://www.webstergrovesmo.gov' . $href,
                        'doc_id' => $documentId,
                    ];
                }
            }
        }

        if (empty($schedulesToProcess)) {
            $this->warn("  No schedule PDFs found for Webster Groves");
            return;
        }

        // Clear future unbooked slots ONCE before processing
        $this->clearFutureUnbookedSlots($rink);
        $this->clearFutureSessions($rink);

        foreach ($schedulesToProcess as $documentId => $schedule) {
            $cacheKey = "processed_webster_doc_{$documentId}";
            $isCurrentOrFutureMonth = Carbon::create($schedule['year'], date('n', strtotime($schedule['month'] . ' 1')), 1)->gte(now()->startOfMonth());

            if (cache()->has($cacheKey) && !$isCurrentOrFutureMonth) {
                $this->info("  Skipping {$schedule['month']} {$schedule['year']} (Doc ID: {$documentId}) - past month, already processed");
                continue;
            }

            $this->info("  Processing {$schedule['month']} {$schedule['year']} (Doc ID: {$documentId})...");

            try {
                $this->processWebsterGrovesPdf($rink, $schedule['url'], $schedule['month'], $schedule['year']);
                cache([$cacheKey => true], now()->addDays(60));
            } catch (\Exception $e) {
                $this->error("    Failed: {$e->getMessage()}");
            }
        }
    }

    private function processWebsterGrovesPdf(Rink $rink, string $pdfUrl, string $monthName, int $year)
    {
        $cacheKey = 'webster_pdf_text_' . md5($pdfUrl . $year . $monthName);
        $text = cache($cacheKey);

        if (!$text) {
            $this->info("    Downloading and parsing PDF...");
            $pdfContent = file_get_contents($pdfUrl);
            $tempPdf = tempnam(sys_get_temp_dir(), 'webster_') . '.pdf';
            file_put_contents($tempPdf, $pdfContent);

            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($tempPdf);
            $text = $pdf->getText();
            unlink($tempPdf);

            cache([$cacheKey => $text], now()->addDays(7));
        } else {
            $this->info("    Using cached PDF text...");
        }

        $monthNumber = date('n', strtotime($monthName . ' 1'));
        $lines = explode("\n", $text);

        $currentDate = null;
        $sessionsCreated = 0;

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            if (preg_match('/^(\d{1,2})$/', $line, $dateMatch)) {
                $day = (int)$dateMatch[1];
                if ($day >= 1 && $day <= 31) {
                    $currentDate = $day;
                }
            }

            if ($currentDate && preg_match('/Public\s*(Skate)?/i', $line)) {
                for ($j = $i; $j < $i + 5 && $j < count($lines); $j++) {
                    $timeLine = trim($lines[$j]);

                    if (preg_match('/(\d{1,2}):(\d{2})\s*([ap]m?)?\s*-\s*(\d{1,2}):(\d{2})\s*([ap]m?)/i', $timeLine, $timeMatch)) {
                        $startHour = (int)$timeMatch[1];
                        $startMin = $timeMatch[2];
                        $startPeriod = strtoupper(substr($timeMatch[3] ?: $timeMatch[6], 0, 1));
                        $endHour = (int)$timeMatch[4];
                        $endMin = $timeMatch[5];
                        $endPeriod = strtoupper(substr($timeMatch[6], 0, 1));

                        if ($startPeriod === 'P' && $startHour !== 12) $startHour += 12;
                        if ($startPeriod === 'A' && $startHour === 12) $startHour = 0;
                        if ($endPeriod === 'P' && $endHour !== 12) $endHour += 12;
                        if ($endPeriod === 'A' && $endHour === 12) $endHour = 0;

                        try {
                            $date = Carbon::create($year, $monthNumber, $currentDate);
                            $startTime = Carbon::create($year, $monthNumber, $currentDate, $startHour, $startMin);
                            $endTime = Carbon::create($year, $monthNumber, $currentDate, $endHour, $endMin);

                            $this->createSession($rink, $date, $startTime, $endTime);
                            $this->info("    Added: {$date->format('M d')} {$startTime->format('g:i A')} - {$endTime->format('g:i A')}");
                            $sessionsCreated++;
                        } catch (\Exception $e) {
                            // Skip invalid dates
                        }
                        break; // Found the time for this Public Session — stop scanning

                        break;
                    }
                }
            }
        }

        if ($sessionsCreated === 0) {
            $this->warn("    No Public sessions found in PDF");
        } else {
            $this->info("    Created {$sessionsCreated} sessions for {$monthName} {$year}");
        }
    }

    private function scrapeMaryville(Rink $rink)
    {
        $response = Http::get($rink->schedule_url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch page");
        }

        $html = $response->body();
        $html = preg_replace('/data-json=\'[^\']*\'/', '', $html);

        preg_match_all('/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)\s+(\d{1,2})\/(\d{1,2}):\s+(\d{1,2}):(\d{2})\s+(AM|PM)\s+-\s+(\d{1,2}):(\d{2})\s+(AM|PM)/i', $html, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            $this->warn("  No sessions found for Maryville");
            return;
        }

        // Clear future unbooked slots before re-processing
        $this->clearFutureUnbookedSlots($rink);
        $this->clearFutureSessions($rink);

        $sessionsCreated = 0;
        $currentYear = date('Y');
        $lastMonth = null;

        foreach ($matches as $match) {
            $month = (int)$match[2];
            $day = (int)$match[3];
            $startHour = (int)$match[4];
            $startMin = $match[5];
            $startPeriod = strtoupper($match[6]);
            $endHour = (int)$match[7];
            $endMin = $match[8];
            $endPeriod = strtoupper($match[9]);

            if ($lastMonth !== null && $month < $lastMonth) {
                $currentYear++;
            }
            $year = $currentYear;
            $lastMonth = $month;

            if ($startPeriod === 'PM' && $startHour !== 12) $startHour += 12;
            if ($startPeriod === 'AM' && $startHour === 12) $startHour = 0;
            if ($endPeriod === 'PM' && $endHour !== 12) $endHour += 12;
            if ($endPeriod === 'AM' && $endHour === 12) $endHour = 0;

            try {
                $date = Carbon::create($year, $month, $day);
                $startTime = Carbon::create($year, $month, $day, $startHour, $startMin);
                $endTime = Carbon::create($year, $month, $day, $endHour, $endMin);

                $this->createSession($rink, $date, $startTime, $endTime);
                $this->info("    Added: {$date->format('M d, Y')} {$startTime->format('g:i A')} - {$endTime->format('g:i A')}");
                $sessionsCreated++;
            } catch (\Exception $e) {
                $this->warn("    Skipped invalid date: {$month}/{$day}/{$year}");
            }
        }

        if ($sessionsCreated === 0) {
            $this->warn("    No Public sessions found");
        } else {
            $this->info("    Created {$sessionsCreated} sessions for Maryville");
        }
    }

    private function scrapeKirkwood(Rink $rink)
    {
        $this->warn("Kirkwood scraper not yet implemented (closed for maintenance)");
    }

    private function createSession(Rink $rink, Carbon $date, Carbon $startTime, Carbon $endTime)
    {
        RinkSession::updateOrCreate(
            [
                'rink_id'    => $rink->id,
                'date'       => $date->toDateString(),
                'start_time' => $startTime->toTimeString(),
            ],
            [
                'end_time'     => $endTime->toTimeString(),
                'session_type' => 'public_skate',
                'scraped_at'   => now(),
            ]
        );
    }

    private function generateTimeSlotsFromSessions()
    {
        $sessions = RinkSession::where('date', '>=', today())
            ->where('is_cancelled', false)
            ->get();

        $slotsCreated = 0;

        foreach ($sessions as $session) {
            $startTime = Carbon::parse($session->date->toDateString() . ' ' . $session->start_time);
            $endTime   = Carbon::parse($session->date->toDateString() . ' ' . $session->end_time);

            $currentTime = $startTime->copy();

            while ($currentTime->copy()->addMinutes(30) <= $endTime) {
                $slotStart = $currentTime->copy();
                $slotEnd   = $currentTime->copy()->addMinutes(30);

                // Only create slot if there's no booked slot already at this time for this rink
                $existing = TimeSlot::where('rink_id', $session->rink_id)
                    ->where('date', $session->date)
                    ->where('start_time', $slotStart->toTimeString())
                    ->whereNotNull('booking_id')
                    ->first();

                if (!$existing) {
                    TimeSlot::updateOrCreate(
                        [
                            'rink_id'    => $session->rink_id,
                            'date'       => $session->date,
                            'start_time' => $slotStart->toTimeString(),
                        ],
                        [
                            'rink_session_id' => $session->id,
                            'end_time'        => $slotEnd->toTimeString(),
                            'duration_minutes'=> 30,
                            'is_available'    => true,
                            'booking_id'      => null,
                        ]
                    );
                    $slotsCreated++;
                } else {
                    $this->info("  Preserving booked slot: {$session->date} {$slotStart->format('g:i A')} (booking #{$existing->booking_id})");
                }

                $currentTime->addMinutes(30);
            }
        }

        $this->info("Created/updated {$slotsCreated} time slots from sessions");
    }
}
