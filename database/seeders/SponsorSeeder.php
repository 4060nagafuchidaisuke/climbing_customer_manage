<?php

namespace Database\Seeders;

use App\Enums\SponsorMediaType;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    public function run(): void
    {
        $sponsors = [
            ['title' => 'スポンサーA', 'media_path' => 'sponsors/ad1.png', 'sort_order' => 1],
            ['title' => 'スポンサーB', 'media_path' => 'sponsors/ad2.png', 'sort_order' => 2],
            ['title' => 'スポンサーC', 'media_path' => 'sponsors/ad3.png', 'sort_order' => 3],
        ];

        foreach ($sponsors as $data) {
            Sponsor::create([
                'title' => $data['title'],
                'media_path' => $data['media_path'],
                'media_type' => SponsorMediaType::IMAGE,
                'is_active' => true,
                'display_seconds' => 5,
                'sort_order' => $data['sort_order'],
            ]);
        }
    }
}
