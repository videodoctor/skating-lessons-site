<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RinksSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rinks')->insert([
            [
                'name' => 'Creve Coeur Ice Arena',
                'slug' => 'creve-coeur',
                'address' => '11250 Olde Cabin Rd, Creve Coeur, MO 63141',
                'website_url' => 'https://www.crevecoeurmo.gov/562/Public-Ice-Sessions',
                'schedule_url' => 'https://www.crevecoeurmo.gov/562/Public-Ice-Sessions',
                'schedule_format' => 'html',
                'scraper_notes' => 'HTML table format. Parse table with class containing schedule.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kirkwood Ice Arena',
                'slug' => 'kirkwood',
                'address' => '111 S Geyer Rd, Kirkwood, MO 63122',
                'website_url' => 'https://www.kirkwoodparksandrec.org/Home/Components/FacilityDirectory/FacilityDirectory/38/445',
                'schedule_url' => 'https://www.kirkwoodparksandrec.org/Home/Components/FacilityDirectory/FacilityDirectory/38/445',
                'schedule_format' => 'pdf',
                'scraper_notes' => 'CLOSED for maintenance Feb 27 - Jul 31, 2026. PDF link on page.',
                'is_active' => false, // Closed for maintenance
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Brentwood Ice Rink',
                'slug' => 'brentwood',
                'address' => '2505 S Brentwood Blvd, Brentwood, MO 63144',
                'website_url' => 'https://www.brentwoodmo.org/2358/Skating-Sessions',
                'schedule_url' => 'https://www.brentwoodmo.org/2358/Skating-Sessions',
                'schedule_format' => 'html',
                'scraper_notes' => 'HTML table format similar to Creve Coeur.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Webster Groves Ice Arena',
                'slug' => 'webster-groves',
                'address' => '33 E Glendale Rd, Webster Groves, MO 63119',
                'website_url' => 'https://www.webstergrovesmo.gov/197/Ice-Arena',
                'schedule_url' => 'https://www.webstergrovesmo.gov/197/Ice-Arena',
                'schedule_format' => 'html',
                'scraper_notes' => 'Has PDF link and HTML calendar. Parse HTML first.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
