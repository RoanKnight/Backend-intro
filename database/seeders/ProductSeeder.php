<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Supplier;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::factory()->count(10)->create();

        $supplierIds = Supplier::all()->pluck('id')->toArray();

        Product::factory()->count(100)->create([
            'supplier_id' => function () use ($supplierIds) {
                return $supplierIds[array_rand($supplierIds)];
            }
        ]);
    }
}
