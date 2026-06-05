<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * คอลัมน์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image',
        'target_departments', // คอลัมน์เป้าหมายฝ่าย (เก็บเป็น JSON)
        'target_branches',    // คอลัมน์เป้าหมายสาขา (เก็บเป็น JSON)
        'document_file',      // คอลัมน์เพิ่มเติมสำหรับเก็บชื่อไฟล์เอกสาร Word / Excel
    ];

    /**
     * การแปลงประเภทข้อมูล (Casting)
     * สำคัญมาก: เปลี่ยนจาก String ในฐานข้อมูลให้เป็น PHP Array อัตโนมัติ
     * วิธีนี้จะทำให้ฟังก์ชัน in_array ใน PostController ทำงานได้ถูกต้อง
     */
    protected $casts = [
        'target_departments' => 'array',
        'target_branches' => 'array',
    ];

    /**
     * ตัวช่วยในการดึงข้อมูล (Eager Loading)
     * ให้โหลดข้อมูล User และข้อมูลคนคอมเมนต์มาพร้อมกันเสมอ 
     * ช่วยลดปัญหา N+1 Query และทำให้การเช็คเงื่อนไขแสดงผลเร็วขึ้น
     */
    protected $with = ['user', 'comments.user'];

    /**
     * ความสัมพันธ์: โพสต์นี้เป็นของ User คนไหน (Owner)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ความสัมพันธ์: โพสต์นี้มีคอมเมนต์อะไรบ้าง
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}