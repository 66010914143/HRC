<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // เพิ่มคอลัมน์ใหม่เข้าไป
            $table->json('target_departments')->after('image')->nullable();
            $table->json('target_branches')->after('target_departments')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['target_departments', 'target_branches']);
        });
    }
};