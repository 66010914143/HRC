<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('internal_memos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID ผู้ขอ
            $table->string('branch'); // สาขา
            $table->string('department'); // แผนก
            $table->string('memo_number')->unique(); // เลขที่เอกสาร (ห้ามซ้ำ)
            $table->date('request_date'); // วันที่ขอเอกสาร
            $table->string('subject'); // เรื่อง (หัวข้อย่อยที่เลือกจาก Dropdown)
            $table->decimal('amount', 15, 2)->nullable(); // จำนวนเงิน (ถ้ามี)
            
            // ระบบ Workflow การอนุมัติ (เพิ่ม comment ว่าอาจจะมี type 3 สำหรับ CEO)
            $table->integer('approval_type'); // รูปแบบ: 1 = อนุมัติ 1 คน, 2 = อนุมัติ 2 คน, 3 = รับทราบ/ดำเนินการ (สำหรับ CEO)
            
            // ผู้อนุมัติคนที่ 1 (หัวหน้าแผนก)
            $table->foreignId('approver_1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('approver_1_status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // ผู้อนุมัติคนที่ 2 (CEO / Level 0)
            $table->foreignId('approver_2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('approver_2_status', ['pending', 'approved', 'rejected'])->default('pending');

            // --- ส่วนที่เพิ่มใหม่ ---
            // แผนกปลายทาง (สำหรับ CEO ส่งเรื่องให้แผนกอื่นรับทราบ/ดำเนินการ)
            $table->string('target_department')->nullable(); 
            // ---------------------
            
            // สถานะรวมของเอกสาร (เพิ่ม 'processing' และ 'acknowledged' สำหรับเคส CEO)
            $table->enum('status', ['pending', 'approved', 'rejected', 'processing', 'acknowledged'])->default('pending');
            
            // สาเหตุที่ปฏิเสธ (ดักเก็บข้อความเมื่อมีการ Reject)
            $table->text('reject_comment')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('internal_memos');
    }
};