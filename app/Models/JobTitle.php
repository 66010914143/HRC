<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    use HasFactory;

    // เพิ่ม 'job_level_id' เพื่ออนุญาตให้ระบบบันทึกค่ารหัสระดับสายงานลงฐานข้อมูลได้
    protected $fillable = ['name', 'job_level_id', 'position_type']; 
}