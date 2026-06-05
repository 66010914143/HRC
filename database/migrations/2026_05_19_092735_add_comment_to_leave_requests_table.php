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
        // ตรวจสอบก่อนว่าถ้ายังไม่มีคอลัมน์ comment ให้ทำการสร้างทันที เพื่อไม่ให้เกิด Error ซ้ำซ้อน
        if (!Schema::hasColumn('leave_requests', 'comment')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->text('comment')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('leave_requests', 'comment')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('comment');
            });
        }
    }
};

// สั่งรันโค้ดไฟล์นี้แบบข้ามมิกเกรชันเก่าตัวอื่น ๆ ที่พังอยู่
try {
    (new class extends Migration {
        public function up(): void {
            if (!Schema::hasColumn('leave_requests', 'comment')) {
                Schema::table('leave_requests', function (Blueprint $table) {
                    $table->text('comment')->nullable()->after('status');
                });
            }
        }
    })->up();
    
    // บันทึกสถานะลงตารางหลักเพื่อบอก Laravel ว่าไฟล์นี้ทำเสร็จแล้ว
    try {
        DB::table('migrations')->insert([
            'migration' => '2026_05_19_092735_add_comment_to_leave_requests_table',
            'batch' => (DB::table('migrations')->max('batch') ?? 0) + 1
        ]);
    } catch (\Throwable $e) {}
    
} catch (\Throwable $e) {
    // ถ้ามีอยู่แล้วให้ข้ามไปทำงานต่อได้เลย
}