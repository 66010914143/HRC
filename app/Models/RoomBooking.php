<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'room_id', 'title', 'start_time', 'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function room() { return $this->belongsTo(Room::class); }
}