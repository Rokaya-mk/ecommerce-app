<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();
        DB::table('users')->insert([
            'name' => 'Tessie Weber',
            'email' => 'norbert.schinner@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'accept_email' =>0,
            'is_Admin'=>1,
            'is_verify'=>1,
            'shippingAddress'=> 'Morocco',
            'accept_notification'=>0,
            'created_at' => $now,
            'updated_at' => $now


        ]);
        DB::table('users')->insert([
            'name' => 'Chance Gerlach',
            'email' => 'effie.von@example.org',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'accept_email' =>1,
            'is_Admin'=>0,
            'is_verify'=>1,
            'shippingAddress'=> 'Morocco',
            'accept_notification'=>1,
            'created_at' => $now,
            'updated_at' => $now
        ]);
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'ciel.emi@example.org',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'accept_email' =>0,
            'is_Admin'=>1,
            'is_verify'=>1,
            'shippingAddress'=> 'Morocco',
            'accept_notification'=>0,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
}
