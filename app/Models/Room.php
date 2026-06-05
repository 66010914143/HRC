<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * คอลัมน์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     */
    protected $fillable = [
        'name',   // ชื่อห้อง
        'branch', // สาขา
    ];

    /**
     * ความสัมพันธ์: หนึ่งห้องสามารถมีการจองได้หลายรายการ
     */
    public function bookings()
    {
        return $this->hasMany(RoomBooking::class);
    }
}