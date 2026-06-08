<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. เพิ่มฟิลด์ลายเซ็นในตาราง users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // เช็คก่อนว่ามีคอลัมน์นี้หรือยัง ป้องกัน error
                if (!Schema::hasColumn('users', 'signature')) {
                    $table->string('signature')->nullable()->comment('เก็บ path ไฟล์รูปลายเซ็น');
                }
            });
        }

        // 2. เพิ่มฟิลด์ระบุตัวผู้อนุมัติในตารางใบลา 
        // ** อย่าลืมเปลี่ยน 'leaves' เป็นชื่อตารางเก็บใบลาจริงๆ ของคุณ **
        if (Schema::hasTable('leaves')) {
            Schema::table('leaves', function (Blueprint $table) {
                if (!Schema::hasColumn('leaves', 'approver_id')) {
                    $table->unsignedBigInteger('approver_id')->nullable()->comment('ID ของหัวหน้าที่ถูกเลือก');
                }
            });
        }

        // 3. เพิ่มฟิลด์ระบุตัวผู้อนุมัติในตารางสวัสดิการ
        // ** อย่าลืมเปลี่ยน 'welfares' เป็นชื่อตารางเก็บสวัสดิการจริงๆ ของคุณ **
        if (Schema::hasTable('welfares')) {
            Schema::table('welfares', function (Blueprint $table) {
                if (!Schema::hasColumn('welfares', 'approver_id')) {
                    $table->unsignedBigInteger('approver_id')->nullable()->comment('ID ของหัวหน้าที่ถูกเลือก');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'signature')) {
                    $table->dropColumn('signature');
                }
            });
        }

        if (Schema::hasTable('leaves')) {
            Schema::table('leaves', function (Blueprint $table) {
                if (Schema::hasColumn('leaves', 'approver_id')) {
                    $table->dropColumn('approver_id');
                }
            });
        }

        if (Schema::hasTable('welfares')) {
            Schema::table('welfares', function (Blueprint $table) {
                if (Schema::hasColumn('welfares', 'approver_id')) {
                    $table->dropColumn('approver_id');
                }
            });
        }
    }
};