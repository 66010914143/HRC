<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('job_levels', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // เก็บชื่อระดับ เช่น ผู้จัดการฝ่าย, ประธานสายงาน
        $table->integer('level_number');  // เก็บตัวเลขสิทธิ์ 0, 1, 2, 3, 4, 5, 6
        $table->timestamps();
    });
}
};
