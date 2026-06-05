<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('departments', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // เก็บชื่อฝ่าย เช่น ฝ่ายบุคคล, ฝ่ายไอที
        $table->timestamps();
    });
}
};
