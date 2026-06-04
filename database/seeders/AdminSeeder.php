<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'username'     => 'admin',
            'password'     => 'admin123',
            'nama_lengkap' => 'Administrator',
        ]);
    }
}
