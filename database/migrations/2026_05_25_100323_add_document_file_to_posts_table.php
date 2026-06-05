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
        Schema::table('posts', function (Blueprint $table) {
            // เพิ่มคอลัมน์สำหรับเก็บชื่อไฟล์ (สามารถเว้นว่างได้ nullable)
            $table->string('document_file')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // ลบคอลัมน์ออกหากมีการ rollback
            $table->dropColumn('document_file');
        });
    }
};