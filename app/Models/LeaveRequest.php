<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * กำหนดฟิลด์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     */
    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'evidence_image',
        'status',
        'approver_id',
        'comment' // เพิ่มฟิลด์สำหรับเก็บเหตุผลการปฏิเสธคำขอลา เรียบร้อยแล้ว
    ];

    /**
     * ความสัมพันธ์: ใบลาใบนี้เป็นของ User คนไหน (ผู้ลา)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ความสัมพันธ์: ใบลาใบนี้ใครเป็นคนพิจารณา (ผู้อนุมัติ)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}