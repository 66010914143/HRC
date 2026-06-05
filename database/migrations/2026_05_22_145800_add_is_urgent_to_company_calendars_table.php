<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('company_calendars', function (Blueprint $table) {
        // เพิ่มคอลัมน์เก็บสถานะประกาศด่วน (ค่าเริ่มต้นเป็น false คือไม่ด่วน)
        $table->boolean('is_urgent')->default(false)->after('target_department');
    });
}

public function down(): void
{
    Schema::table('company_calendars', function (Blueprint $table) {
        $table->dropColumn('is_urgent');
    });
}
};
