<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    public function run(): void
    {
        $sponsors = [
            ['title' => 'スポンサーA', 'image_path' => 'sponsors/ad1.png', 'sort_order' => 1],
            ['title' => 'スポンサーB', 'image_path' => 'sponsors/ad2.png', 'sort_order' => 2],
            ['title' => 'スポンサーC', 'image_path' => 'sponsors/ad3.png', 'sort_order' => 3],
        ];

        foreach ($sponsors as $data) {
            Sponsor::create([
                'title' => $data['title'],
                'image_path' => $data['image_path'],
                'link_url' => null,        // リンク先は今は無しでOK
                'is_active' => true,
                'display_seconds' => 5,
                'sort_order' => $data['sort_order'],
            ]);
        }
    }
}
