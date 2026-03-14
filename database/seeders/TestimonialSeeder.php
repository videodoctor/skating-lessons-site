<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'quote'         => "Coach Kristine is a great instructor who has helped my child increase his skating ability by leaps and bounds. She helps her students to grow by pushing them to their limits, being both firm and very encouraging. My child loves the sessions.",
                'author'        => 'Nikki C.',
                'author_detail' => null,
                'is_active'     => true,
                'sort_order'    => 1,
            ],
            [
                'quote'         => "If you can't skate, you can't play. If you can't skate after lessons with Kristine, you don't want to play.",
                'author'        => 'Kyle A.',
                'author_detail' => null,
                'is_active'     => true,
                'sort_order'    => 2,
            ],
            [
                'quote'         => "Years of working with Kristine and I can confidently say she is the real deal. No-nonsense, elite edge work, and unmatched results. I hunted her down in the parking lot several years ago and I'd do it again! She turns skaters into artists on edges. ⛸️🔥",
                'author'        => 'Chad C.',
                'author_detail' => null,
                'is_active'     => true,
                'sort_order'    => 3,
            ],
        ];

        foreach ($testimonials as $t) {
            Testimonial::firstOrCreate(['author' => $t['author'], 'quote' => substr($t['quote'], 0, 50)], $t);
        }
    }
}
