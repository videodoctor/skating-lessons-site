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

    private function scrapeCreveCoeur(Rink $rink)
    {
        $response = Http::get($rink->schedule_url);
        
        if (!$response->successful()) {
            throw new \Exception("Failed to fetch page");
        }

        $html = $response->body();
        
        // Parse HTML to find schedule images
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Find all images with alt text containing "Public 20"
        $images = $xpath->query('//img[contains(@alt, "Public 20")]');
        
        if ($images->length === 0) {
            $this->warn("No schedule images found for Creve Coeur");
            return;
        }
        
        foreach ($images as $img) {
            $altText = $img->getAttribute('alt');
            $src = $img->getAttribute('src');
            
            // Extract month and year from alt text (e.g., "Feb Public 2026")
            if (preg_match('/(January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+Public\s+(\d{4})/i', $altText, $matches)) {
                $monthName = $matches[1];
                $year = $matches[2];
                
                // Extract document ID from src
                if (preg_match('/documentID=(\d+)/', $src, $docMatch)) {
                    $documentId = $docMatch[1];
                    
                    // Build full URL if src is relative
                    $imageUrl = $src;
                    if (!str_starts_with($src, 'http')) {
                        $imageUrl = 'https://www.crevecoeurmo.gov' . $src;
                    }
                    

                    // Check if we've already processed this exact document
                    $docCacheKey = "processed_creve_coeur_doc_{$documentId}";

                    if (cache()->has($docCacheKey)) {
                        $this->info("  Skipping {$monthName} {$year} (Doc ID: {$documentId}) - already processed");
                        continue;
                    }

                    $this->info("  Processing {$monthName} {$year} (Doc ID: {$documentId})...");

                    try {
                        $this->processCreveCoeurSchedule($rink, $imageUrl, $monthName, $year);
    
                        // Mark this document as processed (cache for 60 days)
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
        // Check cache first using image URL as key
        $cacheKey = 'paddle_html_' . md5($imageUrl);
        $htmlContent = cache($cacheKey);
    
        if (!$htmlContent) {
            $this->info("    Running PaddleOCR (not cached)...");
        
            // Download image
            $tempImage = tempnam(sys_get_temp_dir(), 'schedule_') . '.jpg';
            file_put_contents($tempImage, file_get_contents($imageUrl));
        
            // Run PaddleOCR table recognition
            $outputDir = sys_get_temp_dir() . '/paddle_' . uniqid();
            mkdir($outputDir);
        
            $paddleCommand = "/home/ubuntu/paddle-env/bin/paddleocr table_recognition_v2 --input {$tempImage} --save_path {$outputDir} 2>&1";
            exec($paddleCommand, $output, $returnCode);
        
            if ($returnCode !== 0) {
                throw new \Exception("PaddleOCR failed: " . implode("\n", $output));
            }
        
            // Find the HTML file
            $pattern = $outputDir . DIRECTORY_SEPARATOR . '*_table_*.html';
            $htmlFiles = glob($pattern);        
            
            if (empty($htmlFiles)) {
                throw new \Exception("No HTML table output found");
            }
        
            $htmlContent = file_get_contents($htmlFiles[0]);
        
            // Cache for 30 days
            cache([$cacheKey => $htmlContent], now()->addDays(30));
        
            // Clean up temp files
            unlink($tempImage);

            $files = glob($outputDir . DIRECTORY_SEPARATOR . '*');
            if ($files !== false) {
               array_map('unlink', $files);
            }

            rmdir($outputDir);
        } else {
            $this->info("    Using cached PaddleOCR output");
        }
    
        // Parse the HTML table
        $this->parseTableHtml($rink, $htmlContent, $monthName, $year);
    }

    private function parseTableHtml(Rink $rink, string $htmlContent, string $monthName, int $year)
    {
        $monthNumber = date('n', strtotime($monthName . ' 1'));
    
    // Parse HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($htmlContent);
    $xpath = new DOMXPath($dom);
    
    // Get all table cells (skip header row)
    $rows = $xpath->query('//table/tbody/tr');
    
    $sessionsCreated = 0;
    
    foreach ($rows as $rowIndex => $row) {
        // Skip header row (SUNDAY, MONDAY, etc.)
        if ($rowIndex === 0) {
            continue;
        }
        
        $cells = $xpath->query('.//td', $row);
        
        foreach ($cells as $cell) {
            $cellText = trim($cell->textContent);
            
            // Extract date number and look for "Public" sessions
            // Format: "1 Public 2:30P-4:00P" or "14 Public 12:00P-1:30P Heartto..."
            if (preg_match('/^(\d{1,2})\s+.*?Public\s+(\d{1,2}):(\d{2})([AP])-(\d{1,2}):(\d{2})([AP])/i', $cellText, $match)) {
                $day = (int)$match[1];
                $startHour = (int)$match[2];
                $startMin = $match[3];
                $startPeriod = strtoupper($match[4]);
                $endHour = (int)$match[5];
                $endMin = $match[6];
                $endPeriod = strtoupper($match[7]);
                
                // Convert to 24-hour format
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
    
    // Parse HTML to find PDF links with month names
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    
    // Find all links to DocumentCenter PDFs with month names
    $links = $xpath->query('//a[contains(@href, "/DocumentCenter/View/")]');
    
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        $linkText = trim($link->textContent);
        
        // Look for month and year in link text (e.g., "February 2026 Ice Schedule")
        if (preg_match('/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{4})/i', $linkText, $matches)) {
            $monthName = $matches[1];
            $year = $matches[2];
            
            // Extract document ID
            if (preg_match('/\/DocumentCenter\/View\/(\d+)/', $href, $docMatch)) {
                $documentId = $docMatch[1];
                $pdfUrl = 'https://www.brentwoodmo.org' . $href;
                
                $this->info("  Processing {$monthName} {$year} (Doc ID: {$documentId})...");
                
                try {
                    $this->processBrentwoodPdf($rink, $pdfUrl, $monthName, $year);
                } catch (\Exception $e) {
                    $this->error("    Failed: {$e->getMessage()}");
                }
            }
        }
    }
}

private function processBrentwoodPdf(Rink $rink, string $pdfUrl, string $monthName, int $year)
{
    // Download PDF
    $pdfContent = file_get_contents($pdfUrl);
    $tempPdf = tempnam(sys_get_temp_dir(), 'brentwood_') . '.pdf';
    file_put_contents($tempPdf, $pdfContent);
    
    // Parse PDF
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($tempPdf);
    $text = $pdf->getText();
    
    // Clean up temp file
    unlink($tempPdf);
    
    $monthNumber = date('n', strtotime($monthName . ' 1'));
    $lines = explode("\n", $text);
    
    $currentDate = null;
    $sessionsCreated = 0;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        
        // Look for date numbers (1-31 at start of line)
        if (preg_match('/^(\d{1,2})$/', $line, $dateMatch)) {
            $day = (int)$dateMatch[1];
            if ($day >= 1 && $day <= 31) {
                $currentDate = $day;
            }
        }
        
        // Look for "Public Session" followed by time on next line
        if ($currentDate && stripos($line, 'Public Session') !== false) {
            // Check next few lines for time
            for ($j = $i + 1; $j < $i + 5 && $j < count($lines); $j++) {
                $timeLine = trim($lines[$j]);
                
                // Parse time format like "3:00-5:00pm" or "10:45a-12:45p"
                if (preg_match('/(\d{1,2}):(\d{2})([ap]?)-(\d{1,2}):(\d{2})([ap]m?)/i', $timeLine, $timeMatch)) {
                    $startHour = (int)$timeMatch[1];
                    $startMin = $timeMatch[2];
                    $startPeriod = $timeMatch[3] ?: substr($timeMatch[6], 0, 1); // Handle "3:00-5:00pm" format
                    $endHour = (int)$timeMatch[4];
                    $endMin = $timeMatch[5];
                    $endPeriod = substr($timeMatch[6], 0, 1);
                    
                    $startPeriod = strtoupper($startPeriod);
                    $endPeriod = strtoupper($endPeriod);
                    
                    // Convert to 24-hour format
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
                    
                    break; // Found time for this Public Session, move on
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
    
        // Parse HTML to find PDF links with month names
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
    
        // Find all links to DocumentCenter PDFs
        $links = $xpath->query('//a[contains(@href, "/DocumentCenter/View/")]');
    
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $linkText = trim($link->textContent);
        
            // Look for "Skate Calendar" or month names in link text
            if (preg_match('/(January|February|March|April|May|June|July|August|September|October|November|December)\s+(Skate\s+)?Calendar/i', $linkText, $matches)) {
                $monthName = $matches[1];
            
                // Assume current year or next year based on current month
                $currentMonth = date('n');
                $matchedMonth = date('n', strtotime($monthName . ' 1'));
                $year = ($matchedMonth < $currentMonth) ? date('Y') + 1 : date('Y');
            
                // Extract document ID
                if (preg_match('/\/DocumentCenter\/View\/(\d+)/', $href, $docMatch)) {
                    $documentId = $docMatch[1];
                    $pdfUrl = 'https://www.webstergrovesmo.gov' . $href;
                
                    $this->info("  Processing {$monthName} {$year} (Doc ID: {$documentId})...");
                
                    try {
                        $this->processWebsterGrovesPdf($rink, $pdfUrl, $monthName, $year);
                    } catch (\Exception $e) {
                        $this->error("    Failed: {$e->getMessage()}");
                    }
                }
            }
        }
    }

    private function processWebsterGrovesPdf(Rink $rink, string $pdfUrl, string $monthName, int $year)
    {
    // Download PDF
    $pdfContent = file_get_contents($pdfUrl);
    $tempPdf = tempnam(sys_get_temp_dir(), 'webster_') . '.pdf';
    file_put_contents($tempPdf, $pdfContent);
    
    // Parse PDF
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($tempPdf);
    $text = $pdf->getText();
    
    // Clean up temp file
    unlink($tempPdf);
    
    $monthNumber = date('n', strtotime($monthName . ' 1'));
    $lines = explode("\n", $text);
    
    $currentDate = null;
    $sessionsCreated = 0;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        
        // Look for date numbers (1-31 at start of line)
        if (preg_match('/^(\d{1,2})$/', $line, $dateMatch)) {
            $day = (int)$dateMatch[1];
            if ($day >= 1 && $day <= 31) {
                $currentDate = $day;
            }
        }
        
        // Look for "Public" or "Public Skate" followed by time
        if ($currentDate && preg_match('/Public\s*(Skate)?/i', $line)) {
            // Check next few lines for time
            for ($j = $i; $j < $i + 5 && $j < count($lines); $j++) {
                $timeLine = trim($lines[$j]);
                
                // Parse time format like "3:00-5:00pm" or "10:45a-12:45p" or "1:30 PM - 3:00 PM"
                if (preg_match('/(\d{1,2}):(\d{2})\s*([ap]m?)?\s*-\s*(\d{1,2}):(\d{2})\s*([ap]m?)/i', $timeLine, $timeMatch)) {
                    $startHour = (int)$timeMatch[1];
                    $startMin = $timeMatch[2];
                    $startPeriod = strtoupper(substr($timeMatch[3] ?: $timeMatch[6], 0, 1));
                    $endHour = (int)$timeMatch[4];
                    $endMin = $timeMatch[5];
                    $endPeriod = strtoupper(substr($timeMatch[6], 0, 1));
                    
                    // Convert to 24-hour format
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
                    
                    break; // Found time for this Public session, move on
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
        
        // Remove data-json attributes to avoid duplicates
        $html = preg_replace('/data-json=\'[^\']*\'/', '', $html);
        
        // Parse schedule lines like "Sunday 2/22: 12:10 PM - 1:30 PM"
        preg_match_all('/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)\s+(\d{1,2})\/(\d{1,2}):\s+(\d{1,2}):(\d{2})\s+(AM|PM)\s+-\s+(\d{1,2}):(\d{2})\s+(AM|PM)/i', $html, $matches, PREG_SET_ORDER);
        
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
            
            // Determine year based on month sequence
            if ($lastMonth !== null && $month < $lastMonth) {
                // Month went backwards (Dec -> Jan), we crossed into next year
                $currentYear++;
            }
            $year = $currentYear;
            $lastMonth = $month;
            
            // Convert to 24-hour format
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
                'rink_id' => $rink->id,
                'date' => $date->toDateString(),
                'start_time' => $startTime->toTimeString(),
            ],
            [
                'end_time' => $endTime->toTimeString(),
                'session_type' => 'public_skate',
                'scraped_at' => now(),
            ]
        );
    }

    private function generateTimeSlotsFromSessions()
    {
        // Get all future sessions that don't have time slots yet
        $sessions = RinkSession::where('date', '>=', today())
            ->where('is_cancelled', false)
            ->get();

        $slotsCreated = 0;

        foreach ($sessions as $session) {
            $startTime = Carbon::parse($session->date->toDateString() . ' ' . $session->start_time);
            $endTime = Carbon::parse($session->date->toDateString() . ' ' . $session->end_time);
            
            // Generate 30-minute slots
            $currentTime = $startTime->copy();
            
            while ($currentTime->copy()->addMinutes(30) <= $endTime) {
                $slotStart = $currentTime->copy();
                $slotEnd = $currentTime->copy()->addMinutes(30);
                
		$this->info("  Creating slot: rink_id={$session->rink_id}, date={$session->date}, start={$slotStart->toTimeString()}");
            
                $slot = TimeSlot::updateOrCreate(
    		    [
        		'rink_id' => $session->rink_id,
        		'date' => $session->date,
        		'start_time' => $slotStart->toTimeString(),
   	 	    ],
    		    [
                        'rink_id' => $session->rink_id,
        		'rink_session_id' => $session->id,
                        'end_time' => $slotEnd->toTimeString(),
                        'duration_minutes' => 30,
                        'is_available' => true,
                    ]
                );

                $this->info("  Created slot ID {$slot->id} with rink_id: {$slot->rink_id}");                

                $slotsCreated++;
                $currentTime->addMinutes(30);
            }
        }

        $this->info("Created {$slotsCreated} time slots from sessions");
    }
}
