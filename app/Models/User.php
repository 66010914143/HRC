<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * ฟิลด์ที่อนุญาตให้บันทึกข้อมูลแบบกลุ่มได้ (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'last_name',          // นามสกุล
        'username',           // User ID (ใช้ล็อกอินแทน email)
        'email',              // อีเมล
        'password',           // รหัสผ่าน
        'branch',             // สาขา (สำคัญ: ใช้หาหัวหน้าตามสาขา)
        'department',         // ฝ่าย (สำคัญ: ใช้หาหัวหน้าตามแผนก)
        'position',           // ชื่อตำแหน่ง (สำคัญ: ใช้เช็คสายอนุมัติ)
        'position_level',     // ระดับตำแหน่ง 0-5 (สำคัญ: ใช้เช็คสิทธิ์การลา)
        'role',               // สถานะระบบ (admin/user)
        'profile_image',
        'last_leave_view_at', // บันทึกเวลาเข้าดูเพื่อเคลียร์ Notification
        'signature',          // ลายเซ็นอิเล็กทรอนิกส์ (เพิ่มใหม่)
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_leave_view_at' => 'datetime', // แปลงเป็น Carbon object อัตโนมัติ
    ];

    /**
     * Relationship: หนึ่งคนสามารถส่งใบลาได้หลายครั้ง (One-to-Many)
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    /**
     * Relationship: หนึ่งคนสามารถเป็นผู้อนุมัติได้หลายใบลา (One-to-Many)
     */
    public function approvals()
    {
        return $this->hasMany(LeaveRequest::class, 'approver_id');
    }

    /**
     * Relationship: หนึ่งคนสามารถมีได้หลายโพสต์ (One-to-Many)
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Relationship: หนึ่งคนสามารถมีได้หลายคอมเมนต์ (One-to-Many)
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * ฟังก์ชันสำหรับเช็คสิทธิ์ว่าเป็น Admin หรือไม่
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}