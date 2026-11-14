<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create an admin user
        $adminEmail = env('POS_ADMIN_EMAIL', 'admin@gmail.com');
        $adminPassword = env('POS_ADMIN_PASSWORD');
        if (!$adminPassword && app()->environment('production') && !User::where('email', $adminEmail)->exists()) {
            throw new \RuntimeException('Set POS_ADMIN_PASSWORD before creating a production user.');
        }
        User::firstOrCreate(['email' => $adminEmail], [
            'name' => 'Admin User',
            'password' => Hash::make($adminPassword ?: '123456'),
        ]);

        // Create dummy products
        $products = [
            ['name' => 'Wireless Mouse', 'price' => 25.99, 'stock' => 50],
            ['name' => 'Mechanical Keyboard', 'price' => 89.99, 'stock' => 30],
            ['name' => '27-inch Monitor', 'price' => 199.99, 'stock' => 15],
            ['name' => 'USB-C Hub', 'price' => 45.00, 'stock' => 100],
            ['name' => 'Gaming Headset', 'price' => 59.50, 'stock' => 40],
            ['name' => 'Webcam 1080p', 'price' => 35.00, 'stock' => 25],
            ['name' => 'Mousepad Large', 'price' => 15.99, 'stock' => 200],
            ['name' => 'Laptop Stand', 'price' => 29.99, 'stock' => 60],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}
