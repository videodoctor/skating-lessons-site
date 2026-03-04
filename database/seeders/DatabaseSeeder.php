<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Services
        DB::table('services')->insert([
            [
                'slug' => 'lesson',
                'name' => 'Private Skating Lesson',
                'description' => 'One-on-one instruction focused on your specific goals',
                'duration_minutes' => 30,
                'price' => 55.00,
                'features' => json_encode([
                    '30 minutes of personalized instruction',
                    'Customized skill development',
                    'Progress tracking',
                    'Flexible scheduling'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'assessment-basic',
                'name' => 'Basic Skills Assessment',
                'description' => 'Comprehensive evaluation with written report',
                'duration_minutes' => 30,
                'price' => 125.00,
                'features' => json_encode([
                    '60-minute evaluation session',
                    '8-category skills analysis',
                    'Written report with ratings',
                    'Detailed recommendations',
                    'Development plan'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'assessment-premium',
                'name' => 'Premium Skills Assessment',
                'description' => 'Full evaluation with photos and video analysis',
                'duration_minutes' => 60,
                'price' => 150.00,
                'features' => json_encode([
                    'Everything in Basic Assessment',
                    '6-8 professional photos',
                    '2-3 video clips with QR codes',
                    'Visual technique analysis',
                    'Shareable digital report'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'assessment-progress',
                'name' => 'Progress Package',
                'description' => 'Two assessments with progress comparison',
                'duration_minutes' => 120,
                'price' => 275.00,
                'features' => json_encode([
                    'Initial premium assessment',
                    'Follow-up assessment (3 months later)',
                    'Progress comparison report',
                    'Skill development tracking',
                    'Save $25'
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Availability Template (Example schedule - Kristine can update via admin)
        DB::table('availability_templates')->insert([
            ['day_of_week' => 1, 'start_time' => '16:00', 'end_time' => '19:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()], // Monday
            ['day_of_week' => 2, 'start_time' => '16:00', 'end_time' => '19:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()], // Tuesday
            ['day_of_week' => 3, 'start_time' => '16:00', 'end_time' => '19:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()], // Wednesday
            ['day_of_week' => 4, 'start_time' => '16:00', 'end_time' => '19:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()], // Thursday
            ['day_of_week' => 6, 'start_time' => '09:00', 'end_time' => '14:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()], // Saturday
        ]);

        // Generate time slots for the next 90 days
        $this->generateTimeSlots();
    }

    private function generateTimeSlots(): void
    {
        $templates = DB::table('availability_templates')->where('is_active', true)->get();
        $blockedDates = DB::table('blocked_dates')->pluck('date')->toArray();
        
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(90);
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0=Sunday, 6=Saturday
            
            // Skip if blocked
            if (in_array($currentDate->toDateString(), $blockedDates)) {
                $currentDate->addDay();
                continue;
            }
            
            // Find templates for this day of week
            foreach ($templates as $template) {
                if ($template->day_of_week == $dayOfWeek) {
                    $slotStart = Carbon::parse($currentDate->toDateString() . ' ' . $template->start_time);
                    $slotEnd = Carbon::parse($currentDate->toDateString() . ' ' . $template->end_time);
                    
                    // Generate 30-minute slots
                    while ($slotStart->addMinutes(30) <= $slotEnd) {
                        $slotEndTime = $slotStart->copy();
                        
                        DB::table('time_slots')->insert([
                            'date' => $currentDate->toDateString(),
                            'start_time' => $slotStart->copy()->subMinutes(30)->toTimeString(),
                            'end_time' => $slotStart->toTimeString(),
                            'duration_minutes' => 30,
                            'is_available' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            
            $currentDate->addDay();
        }
    }
}
