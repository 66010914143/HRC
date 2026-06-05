<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelfareRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'amount',
        'description',
        'attachment',
        'status',
        'approver_id', // เพิ่มเติม: อนุญาตให้บันทึก ID ของหัวหน้าที่จะต้องส่งใบคำขอไปหาเพื่อรอตรวจตามสายงาน
        'remark',      // เพิ่มคอลัมน์นี้เพื่อให้ระบบยอมรับการบันทึกเหตุผลการปฏิเสธ
        'approved_by',
        'approved_at',
    ];

    /**
     * ความสัมพันธ์กับ Model User (ผู้ส่งคำขอ)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ความสัมพันธ์กับ Model User (ผู้อนุมัติขั้นสุดท้าย)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * เพิ่มเติม: ความสัมพันธ์กับ Model User สำหรับดึงข้อมูลหัวหน้าผู้ตรวจคำขอ (ตามสายงานที่รอนุมัติ)
     */
    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}