<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewPostNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $userName;

    /**
     * Create a new notification instance.
     * รับค่า Post และชื่อผู้ประกาศ
     */
    public function __construct($post, $userName)
    {
        $this->post = $post;
        $this->userName = $userName;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        // ให้เก็บการแจ้งเตือนลง Database เพื่อไปโชว์ในหน้า Dashboard
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * ข้อมูลชุดนี้จะถูกเก็บลงในคอลัมน์ 'data' ของตาราง notifications
     */
    public function toArray($notifiable)
    {
        return [
            // ส่ง ID ของโพสต์ไปเพื่อให้ NotificationController สร้าง Anchor Link (#post-xx) ได้
            'post_id'   => $this->post->id,
            
            // ข้อมูลสำหรับแสดงผลในแจ้งเตือน
            'user_name' => $this->userName,
            'message'   => 'ได้สร้างประกาศใหม่: ' . Str::limit($this->post->title, 40),
            
            // ส่งกลับไปที่หน้า Dashboard 
            // ตัว Controller จะนำ post_id ด้านบนไปต่อท้ายเป็น #post-id ให้เองเมื่อคลิก
            'link'      => route('dashboard'),
            
            // ระบุประเภทของการแจ้งเตือน
            'type'      => 'new_post',
        ];
    }
}