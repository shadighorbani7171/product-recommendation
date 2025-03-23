<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'iPhone 13',
                'description' => 'Latest iPhone model with amazing features',
                'price' => 999.99,
                'category' => 'electronics',
                'image' => 'iphone13.jpg',
                'views' => 150
            ],
            [
                'name' => 'Samsung Galaxy S21',
                'description' => 'Powerful Android smartphone',
                'price' => 899.99,
                'category' => 'electronics',
                'image' => 'galaxy-s21.jpg',
                'views' => 120
            ],
            [
                'name' => 'Nike Air Max',
                'description' => 'Comfortable running shoes',
                'price' => 129.99,
                'category' => 'clothing',
                'image' => 'nike-air-max.jpg',
                'views' => 80
            ],
            [
                'name' => 'Harry Potter Complete Set',
                'description' => 'All 7 books in the series',
                'price' => 89.99,
                'category' => 'books',
                'image' => 'harry-potter-set.jpg',
                'views' => 200
            ],
            [
                'name' => 'MacBook Pro',
                'description' => '16-inch, M1 Pro chip',
                'price' => 1999.99,
                'category' => 'electronics',
                'image' => 'macbook-pro.jpg',
                'views' => 180
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 