<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('internal_memo_files', function (Blueprint $table) {
        $table->id();
        // เชื่อมโยงกลับไปที่ตารางใบขอหลัก ถ้าใบบันทึกถูกลบ ไฟล์แนบจะถูกลบตามไปด้วย
        $table->foreignId('internal_memo_id')->constrained()->onDelete('cascade'); 
        $table->string('file_path'); // พาธที่เก็บไฟล์ในระบบ
        $table->string('file_name'); // ชื่อไฟล์ดั้งเดิม
        $table->timestamps();
    });
}
};
