<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_date',
        'target_branch',
        'target_department',
        'is_urgent'
    ];

    // ความสัมพันธ์เชื่อมกลับไปหาผู้โพสต์
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}