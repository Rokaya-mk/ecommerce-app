<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSizeColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();
        DB::table('product_size_color_quantities')->insert([
            'product_id' => 1,
            'size_id'=>1,
            'id' =>1,
            'color' =>'red',
            'quantity'=>20,
            'created_at' => $now,
            'updated_at' => $now
        ]);
        DB::table('product_size_color_quantities')->insert([
            'product_id' => 2,
            'size_id'=>2,
            'id' =>2,
            'color' =>'black',
            'quantity'=>10,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
}
