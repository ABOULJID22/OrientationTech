<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (SiteSetting::count() === 0) {
            SiteSetting::create([
                'email' => 'contact@Orientationtech.ma',
                'phone' => '+33 07 67 70 67 26',
                'address' => '14 rue Beffory, 92200 Neuilly-sur-Seine, France',
                'facebook_url' => 'https://www.facebook.com/',
                'linkedin_url' => 'https://www.linkedin.com/company/OrientationTech',
                'twitter_url' => 'https://twitter.com/',
                'instagram_url' => 'https://www.instagram.com/Orientationtech.ma',
                'youtube_url' => 'https://youtube.com/@OrientationTech',
            ]);
        }
    }
}
