<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('media-library:clean', ['--force' => true]);

        User::factory(1)->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'is_admin' => true,
            'email_verified_at' => now(),
            'timezone' => 'Asia/Jakarta',
            'locale' => 'en',
            'admin_locale' => 'en',
        ]);

        $this->call([
            CurrencySeeder::class,
            EmailTemplateSeeder::class,
            SettingsSeeder::class,
            MenuSeeder::class,
            ProjectSeeder::class,
            DummyDataSeeder::class,
            PageSeeder::class,
        ]);
    }
}
