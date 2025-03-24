<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("users")->insert([
            "uuid" => Str::uuid()->toString(),
            "username" => "admin",
            "phone" => "081234567890",
            "email" => "admin@gmail.com",
            "email_verified_at" => NOW(),
            "password" => Hash::make("admin"),
            "is_admin" => true,
            "created_at" => NOW()
        ]);
    }
}
