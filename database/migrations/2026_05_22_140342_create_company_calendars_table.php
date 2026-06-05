<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('company_calendars', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID ของผู้โพสต์ (ระดับ 0-3)
        $table->string('title'); // กล่องหัวข้อความ
        $table->text('description')->nullable(); // กล่องข้อความรายละเอียด
        $table->date('event_date'); // วันที่ลงบันทึกในปฏิทินองค์กร
        $table->string('target_branch'); // สาขาที่ต้องการให้เห็นประกาศ
        $table->string('target_department'); // ฝ่ายที่ต้องการให้เห็นประกาศ
        $table->timestamps();
    });
}
};
