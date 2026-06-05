<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * สั่งสร้างตาราง welfare_requests พร้อมโครงสร้างที่กำหนด
     */
    public function up()
    {
        // ตรวจสอบก่อนว่ามีตารางนี้อยู่แล้วหรือไม่ เพื่อป้องกัน Error: Table already exists
        if (!Schema::hasTable('welfare_requests')) {
            Schema::create('welfare_requests', function (Blueprint $table) {
                $table->id();
                
                // 1. ข้อมูลผู้เบิกและรายละเอียด
                // foreignId สร้างคอลัมน์ user_id ที่เชื่อมกับ id ในตาราง users
                $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
                $table->string('type'); // ประเภทการเบิก
                $table->string('title'); // หัวข้อ/รายการ
                $table->decimal('amount', 10, 2); // จำนวนเงิน (ทศนิยม 2 ตำแหน่ง)
                $table->text('description')->nullable(); // รายละเอียด (ใส่หรือไม่ก็ได้)
                $table->string('attachment')->nullable(); // ชื่อไฟล์แนบ
                
                // 2. ระบบสถานะและการอนุมัติ
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedBigInteger('approver_id')->nullable()->after('status'); // เพิ่มเติม: เก็บ ID ของหัวหน้าที่มีสิทธิ์อนุมัติตามสายงาน (NULL = อนุมัติทันที)
                $table->integer('approved_by')->nullable(); // เก็บ ID ของหัววันที่อนุมัติ
                $table->timestamp('approved_at')->nullable(); // วันที่เวลาที่กดอนุมัติ
                
                $table->timestamps(); // สร้าง created_at และ updated_at
            });
        }
    }

    /**
     * Reverse the migrations.
     * สั่งลบตารางเมื่อมีการใช้คำสั่ง rollback
     */
    public function down()
    {
        Schema::dropIfExists('welfare_requests');
    }
};