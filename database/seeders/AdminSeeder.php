<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // นำเข้า Model User
use Illuminate\Support\Facades\Hash; // สำหรับเข้ารหัสผ่าน

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'System',
            'last_name' => 'Admin',
            'username' => 'admin01', // ใช้ไอดีนี้ในการ Login
            'email' => 'admin@hrc.com',
            'password' => Hash::make('12345678'), // รหัสผ่านคือ 12345678
            'position' => 'ผู้ดูแลระบบ',
            'position_level' => 1,
            'role' => 'admin',
        ]);
    }
}