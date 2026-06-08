<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalMemoFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_memo_id',
        'file_path',
        'file_name'
    ];

    // ความสัมพันธ์: เชื่อมโยงกลับไปยังใบบันทึกภายในหลัก
    public function internalMemo()
    {
        return $this->belongsTo(InternalMemo::class, 'internal_memo_id');
    }
}