<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();
        DB::table('product_sizes')->insert([
            'product_id' => 1,
            'id'=>1,
            'size'=>'XL',
            'created_at' => $now,
            'updated_at' => $now
        ]);
        DB::table('product_sizes')->insert([
            'product_id' => 2,
            'id'=>2,
            'size'=>'S',
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
}
