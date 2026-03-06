<?php

namespace App\Console\Commands;

use App\Models\Rink;
use App\Models\RinkSession;
use App\Models\RinkScrapeRun;
use App\Models\TimeSlot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;

class ScrapeRinkSchedules extends Command
{
    protected $signature = 'scrape:rink-schedules {rink?}';
    protected $description = 'Scrape public skate schedules from rink websites';

    private array $runLog = [];

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
            $this->runLog = [];

            try {
                switch ($rink->slug) {
                    case 'creve-coeur':    $this->scrapeCreveCoeur($rink);    break;
                    case 'brentwood':      $this->scrapeBrentwood($rink);     break;
                    case 'webster-groves': $this->scrapeWebsterGroves($rink); break;
                    case 'kirkwood':       $this->scrapeKirkwood($rink);      break;
                    case 'maryville':      $this->scrapeMaryville($rink);     break;
                    default: $this->warn("No scraper for {$rink->slug}");
                }
                $rink->update(['last_scraped_at' => now()]);
                $this->info("✓ Completed {$rink->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed {$rink->name}: {$e->getMessage()}");
                Log::error("Scraper error for {$rink->slug}", ['error' => $e->getMessage()]);
            }
        }

        $this->info("\nGenerating time slots...");
        $this->generateTimeSlotsFromSessions();
        $this->info("\nScraping complete!");
        return 0;
    }

    // ── Storage helpers ────────────────────────────────────────────────────────

    private function storeRawSource(Rink $rink, int $month, int $year, string $content, string $ext): string
    {
        $path = "scrapes/{$rink->slug}/{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . ".{$ext}";
        Storage::disk('local')->put($path, $content);
        return $path;
    }

    private function storeRawSourceVersioned(Rink $rink, int $month, int $year, string $docId, string $content, string $ext): string
    {
        $path = "scrapes/{$rink->slug}/{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-doc{$docId}.{$ext}";
        Storage::disk('local')->put($path, $content);
        return $path;
    }

    private function recordScrapeRun(Rink $rink, int $month, int $year, string $storagePath, string $sourceType, string $sourceUrl, int $found, int $added, int $removed, bool $hadErrors = false): void
    {
        RinkScrapeRun::updateOrCreate(
            ['rink_id' => $rink->id, 'month' => $month, 'year' => $year],
            [
                'source_file_path' => $storagePath,
                'source_type'      => $sourceType,
                'source_url'       => $sourceUrl,
                'sessions_found'   => $found,
                'sessions_added'   => $added,
                'sessions_removed' => $removed,
                'scrape_log'       => implode("\n", $this->runLog),
                'had_errors'       => $hadErrors,
                'scraped_at'       => now(),
            ]
        );
    }

    private function log(string $msg): void
    {
        $this->info($msg);
        $this->runLog[] = $msg;
    }

    // ── Cleanup ────────────────────────────────────────────────────────────────

    private function clearFutureUnbookedSlots(Rink $rink): int
    {
        $bookedSlotIds = DB::table('bookings')->whereNotNull('time_slot_id')->pluck('time_slot_id')->toArray();
        $deleted = TimeSlot::where('rink_id', $rink->id)
            ->where('date', '>=', today())
            ->whereNull('booking_id')
            ->whereNotIn('id', $bookedSlotIds)
            ->delete();
        $this->log("  Cleared {$deleted} unbooked future slot(s) for {$rink->name}");
        return $deleted;
    }

    private function clearFutureSessions(Rink $rink): int
    {
        $deleted = RinkSession::where('rink_id', $rink->id)->where('date', '>=', today())->delete();
        $this->log("  Cleared {$deleted} future session(s) for {$rink->name}");
        return $deleted;
    }

    // ── Creve Coeur ────────────────────────────────────────────────────────────

    private function scrapeCreveCoeur(Rink $rink)
    {
        $response = Http::get($rink->schedule_url);
        if (!$response->successful()) throw new \Exception("Failed to fetch page");

        $dom = new DOMDocument();
        @$dom->loadHTML($response->body());
        $xpath  = new DOMXPath($dom);
        $images = $xpath->query('//img[contains(@alt, "Public 20")]');

        if ($images->length === 0) { $this->warn("No schedule images found"); return; }

        $this->clearFutureUnbookedSlots($rink);
        $this->clearFutureSessions($rink);

        foreach ($images as $img) {
            $altText = $img->getAttribute('alt');
            $src     = $img->getAttribute('src');

            if (!preg_match('/(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+Public\s+(\d{4})/i', $altText, $matches)) continue;

            $monthName = $matches[1];
            $year      = (int)$matches[2];
            $month     = (int)date('n', strtotime($monthName . ' 1'));

            if (!preg_match('/documentID=(\d+)/', $src, $docMatch)) continue;
            $documentId = $docMatch[1];
            $imageUrl   = str_starts_with($src, 'http') ? $src : 'https://www.crevecoeurmo.gov' . $src;

            $isCurrentOrFuture = Carbon::create($year, $month, 1)->gte(now()->startOfMonth());
            $docCacheKey       = "processed_creve_coeur_doc_{$documentId}";

            if (cache()->has($docCacheKey) && !$isCurrentOrFuture) {
                $this->log("  Skipping {$monthName} {$year} (Doc {$documentId}) - past month cached");
                continue;
            }

            $this->log("  Processing {$monthName} {$year} (Doc {$documentId})...");

            try {
                $imageContent = file_get_contents($imageUrl);
                $storagePath  = $this->storeRawSourceVersioned($rink, $month, $year, $documentId, $imageContent, 'jpg');
                $this->log("    Stored: {$storagePath}");

                $added = $this->processCreveCoeurSchedule($rink, $imageUrl, $monthName, $year);
                $this->recordScrapeRun($rink, $month, $year, $storagePath, 'image', $imageUrl, $added, $added, 0);
                cache([$docCacheKey => true], now()->addDays(60));
            } catch (\Exception $e) {
                $this->error("    Failed: {$e->getMessage()}");
                $this->recordScrapeRun($rink, $month, $year, '', 'image', $imageUrl, 0, 0, 0, true);
            }
        }
    }

    private function processCreveCoeurSchedule(Rink $rink, string $imageUrl, string $monthName, int $year): int
    {
        $cacheKey    = 'paddle_html_' . md5($imageUrl);
        $htmlContent = cache($cacheKey);

        if (!$htmlContent) {
            $this->log("    Running PaddleOCR...");
            $tempImage = tempnam(sys_get_temp_dir(), 'schedule_') . '.jpg';
            file_put_contents($tempImage, file_get_contents($imageUrl));
            $outputDir = sys_get_temp_dir() . '/paddle_' . uniqid();
            mkdir($outputDir);
            exec("/home/ubuntu/paddle-env/bin/paddleocr table_recognition_v2 --input {$tempImage} --save_path {$outputDir} 2>&1", $output, $rc);
            if ($rc !== 0) throw new \Exception("PaddleOCR failed: " . implode("\n", $output));
            $htmlFiles = glob($outputDir . '/*_table_*.html');
            if (empty($htmlFiles)) throw new \Exception("No HTML table output found");
            $htmlContent = file_get_contents($htmlFiles[0]);
            cache([$cacheKey => $htmlContent], now()->addDays(30));
            unlink($tempImage);
            array_map('unlink', glob($outputDir . '/*') ?: []);
            rmdir($outputDir);
        } else {
            $this->log("    Using cached PaddleOCR output");
        }

        return $this->parseTableHtml($rink, $htmlContent, $monthName, $year);
    }

    private function parseTableHtml(Rink $rink, string $htmlContent, string $monthName, int $year): int
    {
        $monthNumber = (int)date('n', strtotime($monthName . ' 1'));
        $dom = new DOMDocument();
        @$dom->loadHTML($htmlContent);
        $xpath = new DOMXPath($dom);
        $rows  = $xpath->query('//table/tbody/tr');
        $count = 0;

        foreach ($rows as $i => $row) {
            if ($i === 0) continue;
            foreach ($xpath->query('.//td', $row) as $cell) {
                $txt = trim($cell->textContent);
                if (!preg_match('/^(\d{1,2})\s+.*?Public\s+(\d{1,2}):(\d{2})([AP])-(\d{1,2}):(\d{2})([AP])/i', $txt, $m)) continue;
                $day = (int)$m[1];
                $sh = (int)$m[2]; $sm = $m[3]; $sp = strtoupper($m[4]);
                $eh = (int)$m[5]; $em = $m[6]; $ep = strtoupper($m[7]);
                if ($sp==='P'&&$sh!==12) $sh+=12; if ($sp==='A'&&$sh===12) $sh=0;
                if ($ep==='P'&&$eh!==12) $eh+=12; if ($ep==='A'&&$eh===12) $eh=0;
                try {
                    $date  = Carbon::create($year, $monthNumber, $day);
                    $start = Carbon::create($year, $monthNumber, $day, $sh, $sm);
                    $end   = Carbon::create($year, $monthNumber, $day, $eh, $em);
                    $this->createSession($rink, $date, $start, $end);
                    $this->log("    Added: {$date->format('M d')} {$start->format('g:i A')} - {$end->format('g:i A')}");
                    $count++;
                } catch (\Exception $e) { $this->warn("    Skipped day {$day}"); }
            }
        }

        $count === 0 ? $this->warn("    No Public sessions found") : $this->log("    Created {$count} sessions for {$monthName} {$year}");
        return $count;
    }

    // ── Brentwood ──────────────────────────────────────────────────────────────

    private function scrapeBrentwood(Rink $rink)
    {
        $response = Http::get($rink->schedule_url);
        if (!$response->successful()) throw new \Exception("Failed to fetch page");

        $dom = new DOMDocument();
        @$dom->loadHTML($response->body());
        $xpath = new DOMXPath($dom);
        $links = $xpath->query('//a[contains(@href, "/DocumentCenter/View/")]');

        $schedules = [];
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $txt  = trim($link->textContent);
            if (!preg_match('/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{4})/i', $txt, $m)) continue;
            if (!preg_match('/\/DocumentCenter\/View\/(\d+)/', $href, $dm)) continue;
            $schedules[$dm[1]] = ['month' => $m[1], 'year' => (int)$m[2], 'url' => 'https://www.brentwoodmo.org' . $href, 'doc_id' => $dm[1]];
        }

        if (empty($schedules)) { $this->warn("  No PDFs found for Brentwood"); return; }

        $this->clearFutureUnbookedSlots($rink);
        $this->clearFutureSessions($rink);

        foreach ($schedules as $docId => $s) {
            $month             = (int)date('n', strtotime($s['month'] . ' 1'));
            $isCurrentOrFuture = Carbon::create($s['year'], $month, 1)->gte(now()->startOfMonth());
            $cacheKey          = "processed_brentwood_doc_{$docId}";

            if (cache()->has($cacheKey) && !$isCurrentOrFuture) {
                $this->log("  Skipping {$s['month']} {$s['year']} - past month cached"); continue;
            }

            $this->log("  Processing {$s['month']} {$s['year']} (Doc {$docId})...");
            try {
                $pdfContent = file_get_contents($s['url']);
                if (empty($pdfContent) || substr($pdfContent, 0, 4) !== '%PDF') {
                    throw new \Exception("Invalid PDF content from {$s['url']}");
                }
                $storagePath = $this->storeRawSourceVersioned($rink, $month, $s['year'], $docId, $pdfContent, 'pdf');
                $this->log("    Stored: {$storagePath}");
                $added = $this->processBrentwoodPdf($rink, $storagePath, $s['month'], $s['year']);
                $this->recordScrapeRun($rink, $month, $s['year'], $storagePath, 'pdf', $s['url'], $added, $added, 0);
                cache([$cacheKey => true], now()->addDays(60));
            } catch (\Exception $e) {
                $this->error("    Failed: {$e->getMessage()}");
                $this->recordScrapeRun($rink, $month, $s['year'], '', 'pdf', $s['url'], 0, 0, 0, true);
            }
        }
    }

    private function processBrentwoodPdf(Rink $rink, string $storagePath, string $monthName, int $year): int
    {
        $this->log("    Parsing stored PDF...");
        $tempPdf = tempnam(sys_get_temp_dir(), 'brentwood_') . '.pdf';
        file_put_contents($tempPdf, Storage::disk('local')->get($storagePath));
        $text = (new \Smalot\PdfParser\Parser())->parseFile($tempPdf)->getText();
        unlink($tempPdf);

        $monthNumber = (int)date('n', strtotime($monthName . ' 1'));
        $lines       = array_map('trim', explode("\n", $text));
        $total       = count($lines);
        $currentDate = null;
        $count       = 0;

        for ($i = 0; $i < $total; $i++) {
            $line = $lines[$i];

            // Match bare date OR date at start of line followed by spaces/text (e.g. "16   Stick & Puck")
            if (preg_match('/^(\d{1,2})(\s|$)/', $line, $dm)) {
                $d = (int)$dm[1];
                if ($d >= 1 && $d <= 31) {
                    $currentDate = $d;
                    if (preg_match('/^\d{1,2}$/', $line)) continue;
                }
            }

            if ($currentDate && stripos($line, 'Public Session') !== false) {
                for ($j = $i + 1; $j < min($i + 6, $total); $j++) {
                    $tl = $lines[$j];
                    if ($tl === '') continue;
                    if (preg_match('/^\d{1,2}$/', $tl)) break;
                    if (!preg_match('/^\d{1,2}:/', $tl)) continue;
                    if (preg_match('/^(\d{1,2}):(\d{2})\s*([ap]m?)?\s*-\s*(\d{1,2}):(\d{2})\s*([ap]m?)/i', $tl, $m)) {
                        $sh = (int)$m[1]; $sm = $m[2]; $eh = (int)$m[4]; $em = $m[5];
                        $ep = strtoupper(substr($m[6], 0, 1));
                        $sp = $m[3] !== '' ? strtoupper(substr($m[3], 0, 1)) : $ep;
                        if ($sp==='P'&&$sh!==12) $sh+=12; if ($sp==='A'&&$sh===12) $sh=0;
                        if ($ep==='P'&&$eh!==12) $eh+=12; if ($ep==='A'&&$eh===12) $eh=0;
                        try {
                            $date  = Carbon::create($year, $monthNumber, $currentDate);
                            $start = Carbon::create($year, $monthNumber, $currentDate, $sh, $sm);
                            $end   = Carbon::create($year, $monthNumber, $currentDate, $eh, $em);
                            $this->createSession($rink, $date, $start, $end);
                            $this->log("    Added: {$date->format('M d')} {$start->format('g:i A')} - {$end->format('g:i A')}");
                            $count++;
                        } catch (\Exception $e) { $this->warn("    Skipped day {$currentDate}"); }
                        break;
                    }
                }
            }
        }

        $count === 0 ? $this->warn("    No Public sessions found") : $this->log("    Created {$count} sessions for {$monthName} {$year}");
        return $count;
    }

    // ── Webster Groves ─────────────────────────────────────────────────────────

    private function scrapeWebsterGroves(Rink $rink)
    {
        // Webster Groves uses CivicPlus OAuth — their schedule page cannot be scraped server-side.
        // strategy:
        //   1. Try to scrape schedule_url for DocumentCenter PDF links (works if they ever fix it)
        //   2. Fall back to schedule_pdf_url — admin manually sets this to the current month's PDF
        //      Update via admin panel when they publish a new month's schedule.

        $pdfUrl   = null;
        $docId    = 'manual';
        $month    = (int)date('n');
        $year     = (int)date('Y');
        $monthName = date('F');

        // Attempt to scrape the schedule page for a PDF link
        try {
            $response = Http::timeout(10)->get($rink->schedule_url);
            if ($response->successful()) {
                $dom = new DOMDocument();
                @$dom->loadHTML($response->body());
                $xpath = new DOMXPath($dom);
                $links = $xpath->query('//a[contains(@href, "/DocumentCenter/View/")]');
                foreach ($links as $link) {
                    $href = $link->getAttribute('href');
                    $txt  = trim($link->textContent);
                    if (!preg_match('/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(Skate\s+)?Calendar/i', $txt)) continue;
                    if (!preg_match('/\/DocumentCenter\/View\/(\d+)/', $href, $dm)) continue;
                    $pdfUrl = 'https://www.webstergrovesmo.gov' . $href;
                    $docId  = $dm[1];
                    $this->log("  Found PDF link on schedule page: {$pdfUrl}");
                    // Update schedule_pdf_url automatically
                    $rink->update(['schedule_pdf_url' => $pdfUrl]);
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->warn("  Could not scrape schedule page: {$e->getMessage()}");
        }

        // Fall back to manually-set schedule_pdf_url
        if (!$pdfUrl) {
            $pdfUrl = $rink->schedule_pdf_url;
            if (empty($pdfUrl)) {
                $this->warn("  No PDF URL available for Webster Groves. Set schedule_pdf_url in admin.");
                return;
            }
            $this->log("  Using manually-set PDF URL: {$pdfUrl}");
            if (preg_match('/\/View\/(\d+)/', $pdfUrl, $dm)) {
                $docId = $dm[1];
            }
        }

        $this->log("  Processing {$monthName} {$year} (Doc {$docId})...");
        $this->clearFutureUnbookedSlots($rink);
        $this->clearFutureSessions($rink);

        try {
            // CivicPlus returns 404 HTTP status but serves valid PDF body — use curl to bypass
            $ch = curl_init($pdfUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $pdfContent = curl_exec($ch);
            $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->log("    Fetched " . strlen($pdfContent) . " bytes (HTTP {$httpCode}), starts: " . substr($pdfContent, 0, 4));

            if (strlen($pdfContent) < 100 || substr($pdfContent, 0, 4) !== '%PDF') {
                throw new \Exception("Invalid PDF received (HTTP {$httpCode})");
            }

            $storagePath = $this->storeRawSourceVersioned($rink, $month, $year, $docId, $pdfContent, 'pdf');
            $this->log("    Stored: {$storagePath}");

            $added = $this->processWebsterGrovesPdf($rink, $storagePath, $monthName, $year);
            $this->recordScrapeRun($rink, $month, $year, $storagePath, 'pdf', $pdfUrl, $added, $added, 0);

        } catch (\Exception $e) {
            $this->error("    Failed: {$e->getMessage()}");
            $this->recordScrapeRun($rink, $month, $year, '', 'pdf', $pdfUrl, 0, 0, 0, true);
        }
    }

    private function processWebsterGrovesPdf(Rink $rink, string $storagePath, string $monthName, int $year): int
    {
        $this->log("    Parsing stored PDF...");
        $tempPdf = tempnam(sys_get_temp_dir(), 'webster_') . '.pdf';
        file_put_contents($tempPdf, Storage::disk('local')->get($storagePath));
        $text = (new \Smalot\PdfParser\Parser())->parseFile($tempPdf)->getText();
        unlink($tempPdf);

        $monthNumber = (int)date('n', strtotime($monthName . ' 1'));
        $lines       = explode("\n", $text);
        $currentDate = null;
        $count       = 0;

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (preg_match('/^(\d{1,2})$/', $line, $dm)) {
                $d = (int)$dm[1];
                if ($d >= 1 && $d <= 31) $currentDate = $d;
            }
            if ($currentDate && preg_match('/Public\s*(Skate)?/i', $line)) {
                for ($j = $i; $j < $i + 5 && $j < count($lines); $j++) {
                    $tl = trim($lines[$j]);
                    if (preg_match('/(\d{1,2}):(\d{2})\s*([ap]m?)?\s*-\s*(\d{1,2}):(\d{2})\s*([ap]m?)/i', $tl, $m)) {
                        $sh = (int)$m[1]; $sm = $m[2];
                        $sp = strtoupper(substr($m[3] ?: $m[6], 0, 1));
                        $eh = (int)$m[4]; $em = $m[5];
                        $ep = strtoupper(substr($m[6], 0, 1));
                        if ($sp==='P'&&$sh!==12) $sh+=12; if ($sp==='A'&&$sh===12) $sh=0;
                        if ($ep==='P'&&$eh!==12) $eh+=12; if ($ep==='A'&&$eh===12) $eh=0;
                        try {
                            $date  = Carbon::create($year, $monthNumber, $currentDate);
                            $start = Carbon::create($year, $monthNumber, $currentDate, $sh, $sm);
                            $end   = Carbon::create($year, $monthNumber, $currentDate, $eh, $em);
                            $this->createSession($rink, $date, $start, $end);
                            $this->log("    Added: {$date->format('M d')} {$start->format('g:i A')} - {$end->format('g:i A')}");
                            $count++;
                        } catch (\Exception $e) {}
                        break;
                    }
                }
            }
        }

        $count === 0 ? $this->warn("    No Public sessions found") : $this->log("    Created {$count} sessions for {$monthName} {$year}");
        return $count;
    }

    // ── Maryville ──────────────────────────────────────────────────────────────

    private function scrapeMaryville(Rink $rink)
    {
        $response = Http::get($rink->schedule_url);
        if (!$response->successful()) throw new \Exception("Failed to fetch page");

        $html        = $response->body();
        $now         = now();
        $month       = (int)$now->format('n');
        $year        = (int)$now->format('Y');
        $storagePath = $this->storeRawSource($rink, $month, $year, $html, 'html');
        $this->log("  Stored raw HTML: {$storagePath}");

        $html = preg_replace('/data-json=\'[^\']*\'/', '', $html);
        preg_match_all('/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)\s+(\d{1,2})\/(\d{1,2}):\s+(\d{1,2}):(\d{2})\s+(AM|PM)\s+-\s+(\d{1,2}):(\d{2})\s+(AM|PM)/i', $html, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            $this->warn("  No sessions found for Maryville");
            $this->recordScrapeRun($rink, $month, $year, $storagePath, 'html', $rink->schedule_url, 0, 0, 0, true);
            return;
        }

        $this->clearFutureUnbookedSlots($rink);
        $this->clearFutureSessions($rink);

        $count       = 0;
        $currentYear = $year;
        $lastMonth   = null;

        foreach ($matches as $match) {
            $m  = (int)$match[2]; $day = (int)$match[3];
            $sh = (int)$match[4]; $sm = $match[5]; $sp = strtoupper($match[6]);
            $eh = (int)$match[7]; $em = $match[8]; $ep = strtoupper($match[9]);
            if ($lastMonth !== null && $m < $lastMonth) $currentYear++;
            $lastMonth = $m;
            if ($sp==='PM'&&$sh!==12) $sh+=12; if ($sp==='AM'&&$sh===12) $sh=0;
            if ($ep==='PM'&&$eh!==12) $eh+=12; if ($ep==='AM'&&$eh===12) $eh=0;
            try {
                $date  = Carbon::create($currentYear, $m, $day);
                $start = Carbon::create($currentYear, $m, $day, $sh, $sm);
                $end   = Carbon::create($currentYear, $m, $day, $eh, $em);
                $this->createSession($rink, $date, $start, $end);
                $this->log("    Added: {$date->format('M d, Y')} {$start->format('g:i A')} - {$end->format('g:i A')}");
                $count++;
            } catch (\Exception $e) { $this->warn("    Skipped {$m}/{$day}/{$currentYear}"); }
        }

        $this->recordScrapeRun($rink, $month, $year, $storagePath, 'html', $rink->schedule_url, $count, $count, 0);
        $count === 0 ? $this->warn("    No sessions found") : $this->log("    Created {$count} sessions for Maryville");
    }

    // ── Kirkwood ───────────────────────────────────────────────────────────────

    private function scrapeKirkwood(Rink $rink)
    {
        $this->warn("Kirkwood scraper not yet implemented");
    }

    // ── Shared ─────────────────────────────────────────────────────────────────

    private function createSession(Rink $rink, Carbon $date, Carbon $startTime, Carbon $endTime): void
    {
        RinkSession::updateOrCreate(
            ['rink_id' => $rink->id, 'date' => $date->toDateString(), 'start_time' => $startTime->toTimeString()],
            ['end_time' => $endTime->toTimeString(), 'session_type' => 'public_skate', 'scraped_at' => now()]
        );
    }

    private function generateTimeSlotsFromSessions(): void
    {
        $sessions = RinkSession::where('date', '>=', today())->where('is_cancelled', false)->get();
        $created  = 0;

        foreach ($sessions as $session) {
            $start   = Carbon::parse($session->date->toDateString() . ' ' . $session->start_time);
            $end     = Carbon::parse($session->date->toDateString() . ' ' . $session->end_time);
            $current = $start->copy();

            while ($current->copy()->addMinutes(30) <= $end) {
                $slotStart = $current->copy();
                $slotEnd   = $current->copy()->addMinutes(30);

                $booked = TimeSlot::where('rink_id', $session->rink_id)
                    ->where('date', $session->date)
                    ->where('start_time', $slotStart->toTimeString())
                    ->whereNotNull('booking_id')
                    ->first();

                if (!$booked) {
                    TimeSlot::updateOrCreate(
                        ['rink_id' => $session->rink_id, 'date' => $session->date, 'start_time' => $slotStart->toTimeString()],
                        ['rink_session_id' => $session->id, 'end_time' => $slotEnd->toTimeString(), 'duration_minutes' => 30, 'is_available' => true, 'booking_id' => null]
                    );
                    $created++;
                } else {
                    $this->info("  Preserving booked slot: {$session->date} {$slotStart->format('g:i A')} (booking #{$booked->booking_id})");
                }
                $current->addMinutes(30);
            }
        }
        $this->info("Created/updated {$created} time slots");
    }
}
