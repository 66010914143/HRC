<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // เพิ่มคอลัมน์ profile_image ต่อท้าย position_level และอนุญาตให้เป็นค่าว่างได้ (nullable)
            $table->string('profile_image')->nullable()->after('position_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ลบคอลัมน์ profile_image ออกหากมีการ Rollback migration
            $table->dropColumn('profile_image');
        });
    }
};