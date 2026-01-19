<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting to seed database with 2K records...');

        // Create 500 users
        $this->command->info('Creating 500 users...');
        $users = User::factory(500)->create();

        // Create 500 products
        $this->command->info('Creating 500 products...');
        $products = Product::factory(500)->create();

        // Create 1000 orders using existing users and products
        $this->command->info('Creating 1000 orders...');
        Order::factory(1000)->create([
            'user_id' => fn() => $users->random()->id,
            'product_id' => fn() => $products->random()->id,
        ]);

        $this->command->info('Database seeded successfully! Total records: ~2000');
        $this->command->info('Users: 500, Products: 500, Orders: 1000');
    }
}
