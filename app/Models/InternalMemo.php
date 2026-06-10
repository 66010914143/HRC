<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalMemo extends Model
{
    use HasFactory;

    // 1. เพิ่มฟิลด์ควบคุม Flow และสาเหตุปฏิเสธลงใน $fillable เพื่อให้สร้าง/แก้ไขข้อมูลได้
    protected $fillable = [
        'user_id',
        'branch',
        'department',
        'memo_number',
        'request_date',
        'subject',
        'amount',
        'approval_type',
        'approver_1_id',
        'approver_1_status',
        'approver_2_id',
        'approver_2_status',
        'target_department',
        'status',
        'reject_comment', // บันทึกสาเหตุที่ปฏิเสธ
    ];

    // 2. ดึงข้อมูลพนักงานผู้ขอเอกสาร
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 3. ดึงข้อมูลผูอนุมัติคนที่ 1 (หัวหน้าแผนก)
    public function approver1()
    {
        return $this->belongsTo(User::class, 'approver_1_id');
    }

    // 4. ดึงข้อมูลผู้อนุมัติคนที่ 2 (CEO)
    public function approver2()
    {
        return $this->belongsTo(User::class, 'approver_2_id');
    }

    /**
     * ➕ เพิ่มใหม่: เชื่อมโยงความสัมพันธ์ไปยังตารางไฟล์แนบประกอบใบบันทึกภายใน
     * รองรับคำสั่งเรียกดึงข้อมูลแบบ Eager Loading (with('attachments')) ใน Controller และการลูปของสคริปต์หน้าบ้าน
     */
    public function attachments()
    {
        return $this->hasMany(InternalMemoFile::class, 'internal_memo_id');
    }
}