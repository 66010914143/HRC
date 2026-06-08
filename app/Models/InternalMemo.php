<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalMemo extends Model
{
    use HasFactory;

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
        'status',
        'reject_comment'
    ];

    // ความสัมพันธ์: เชื่อมโยงกลับไปยังพนักงานผู้ขอเอกสาร
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ความสัมพันธ์: เชื่อมโยงไปยังผูอนุมัติคนที่ 1 (หัวหน้าแผนก)
    public function approver1()
    {
        return $this->belongsTo(User::class, 'approver_1_id');
    }

    // ความสัมพันธ์: เชื่อมโยงไปยังผูอนุมัติคนที่ 2 (CEO Level 0)
    public function approver2()
    {
        return $this->belongsTo(User::class, 'approver_2_id');
    }

    // ความสัมพันธ์: เชื่อมโยงไปยังไฟล์แนบ (มีได้หลายไฟล์)
    public function files()
    {
        return $this->hasMany(InternalMemoFile::class, 'internal_memo_id');
    }
}