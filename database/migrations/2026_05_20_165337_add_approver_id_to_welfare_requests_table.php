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
        Schema::table('welfare_requests', function (Blueprint $blueprint) {
            // เพิ่มคอลัมน์ approver_id ต่อท้ายคอลัมน์ user_id และตั้งให้เป็น Nullable ได้ (เผื่อกรณีไม่มีหัวหน้า)
            $blueprint->unsignedBigInteger('approver_id')->nullable()->after('user_id');

            // (Optional) หากต้องการทำ Foreign Key ผูกกับตาราง users สามารถเปิดคอมเมนต์บรรทัดล่างนี้ได้ครับ
            // $blueprint->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('welfare_requests', function (Blueprint $blueprint) {
            // ลบคอลัมน์ออกเมื่อสั่ง rollback
            $blueprint->dropColumn('approver_id');
        });
    }
};