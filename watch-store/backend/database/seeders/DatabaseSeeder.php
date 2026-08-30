<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create admin user for Filament panel access
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@watchstore.com',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        // Seed brands, categories, products, blogs, sliders, shipping, coupons
        $this->call([
            DemoDataSeeder::class,
            ContentPageSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
