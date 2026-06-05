<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('branches', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // เก็บชื่อสาขา เช่น สาขาหลัก, สาขา 2
        $table->timestamps();
    });
}
};
