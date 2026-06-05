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
        Schema::table('job_titles', function (Blueprint $table) {
            // เพิ่มคอลัมน์ position_type กำหนดค่าเริ่มต้นเป็น 'employee' (พนักงาน)
            // ตัวเลือกที่เราจะใช้เก็บคือ 'employee' (พนักงาน) และ 'head' (หัวหน้าแผนก)
            $table->string('position_type')->default('employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_titles', function (Blueprint $table) {
            // ยกเลิกคอลัมน์หากมีการ rollback
            $table->dropColumn('position_type');
        });
    }
};