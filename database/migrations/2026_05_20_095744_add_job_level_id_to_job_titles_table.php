<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('job_titles', function (Blueprint $table) {
        // เพิ่มคอลัมน์ job_level_id ต่อท้ายคอลัมน์ name และอนุญาตให้เป็น NULL ได้ก่อนเพื่อความปลอดภัย
        $table->unsignedInteger('job_level_id')->nullable()->after('name');
    });
}

public function down()
{
    Schema::table('job_titles', function (Blueprint $table) {
        $table->dropColumn('job_level_id');
    });
}
};
