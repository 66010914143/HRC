<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID ของผู้ที่ส่งใบลา
            $table->string('leave_type'); // ประเภทการลา เช่น ลาป่วย, ลากิจ, ลาพักร้อน
            $table->date('start_date'); // วันที่เริ่มลา
            $table->date('end_date'); // วันที่สิ้นสุดการลา
            $table->text('reason'); // เหตุผลการลา
            
            // เพิ่มคอลัมน์เก็บชื่อไฟล์รูปภาพหลักฐาน (รองรับค่าว่าง)
            $table->string('evidence_image')->nullable(); 
            
            /**
             * สถานะการลา: 
             * pending = รออนุมัติ
             * approved = อนุมัติแล้ว
             * rejected = ปฏิเสธ
             * auto_approved = อนุมัติอัตโนมัติ (สำหรับระดับบริหาร)
             * หมายเหตุ: ใช้ string(20) เพื่อให้รองรับคำว่า auto_approved ได้พอดี
             */
            $table->string('status', 20)->default('pending'); 
            
            // ID ของหัวหน้าหรือผู้ที่มีอำนาจอนุมัติในใบลาฉบับนี้
            $table->integer('approver_id')->nullable(); 
            
            // ความเห็นเพิ่มเติมจากผู้อนุมัติ (กรณีปฏิเสธหรือหมายเหตุเพิ่มเติม)
            $table->text('comment')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
};