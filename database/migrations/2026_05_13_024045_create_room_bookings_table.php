<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('room_bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ใครจอง
        $table->foreignId('room_id')->constrained()->onDelete('cascade'); // ห้องไหน
        $table->string('title');         // จองทำไม (วัตถุประสงค์)
        $table->dateTime('start_time');  // เริ่มจอง
        $table->dateTime('end_time');    // สิ้นสุดการจอง
        $table->timestamps();
    });
}
};
