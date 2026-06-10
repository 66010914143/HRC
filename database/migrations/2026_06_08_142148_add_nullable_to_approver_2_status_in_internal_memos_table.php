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
        Schema::table('internal_memos', function (Blueprint $table) {
            // สั่งปรับปรุงคอลัมน์เดิมให้ยอมรับค่า Null (ค่าว่าง) ได้
            $table->string('approver_2_status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_memos', function (Blueprint $table) {
            // เผื่อกรณีต้องการย้อนกลับ (Rollback) ให้กลับไปเป็นแบบเดิมที่ไม่ยอมรับค่าว่าง
            $table->string('approver_2_status')->nullable(false)->change();
        });
    }
};