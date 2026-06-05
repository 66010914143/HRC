<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    // เพิ่มบรรทัดนี้เพื่ออนุญาตให้บันทึกข้อมูลลง Field เหล่านี้ได้
    protected $fillable = [
        'user_id',
        'post_id',
        'comment_text',
    ];

    // ความสัมพันธ์กับ User (คนคอมเมนต์)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ความสัมพันธ์กับ Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}