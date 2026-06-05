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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // เชื่อมกับ User
            $table->string('title')->nullable(); // เพิ่มฟิลด์หัวข้อประกาศ
            $table->text('content');
            $table->string('image')->nullable(); // เก็บพาธรูปภาพ
            
            // --- เพิ่มใหม่: ฟิลด์สำหรับกำหนดกลุ่มเป้าหมายที่มองเห็นได้ ---
            $table->json('target_departments')->nullable(); // เก็บรายชื่อฝ่ายที่เลือก (เช่น ["IT", "HR"])
            $table->json('target_branches')->nullable();    // เก็บรายชื่อสาขาที่เลือก (เช่น ["สำนักงานใหญ่"])
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};