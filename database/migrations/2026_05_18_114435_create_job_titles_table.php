<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('job_titles', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // เก็บชื่อตำแหน่ง เช่น เจ้าหน้าที่อาวุโส, รองผู้จัดการฝ่าย
        $table->timestamps();
    });
}
};
