<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level_number']; // อนุญาตให้บันทึกชื่อระดับ และตัวเลข 0-6 ได้
}