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
    Schema::table('users', function (Blueprint $table) {
        $table->string('username')->unique()->after('id'); // สำหรับ User ID ที่ Admin ตั้งให้
        $table->string('last_name')->after('name');
        $table->string('position')->after('last_name'); // ชื่อตำแหน่งเต็ม
        $table->integer('position_level')->default(5); // ระดับ 1-5 (1=สูงสุด)
        $table->enum('role', ['admin', 'user'])->default('user'); // แยก Admin/User
    });
}
};
