<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCommentNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $commenter;

    /**
     * Create a new notification instance.
     * รับค่า Post และ User ที่มาเขียนคอมเมนต์
     */
    public function __construct($post, $commenter)
    {
        $this->post = $post;
        $this->commenter = $commenter;
    }

    /**
     * Get the notification's delivery channels.
     * ยืนยันการใช้ช่องทาง 'database' เพื่อลงตาราง notifications
     */
    public function via(object $notifiable): array
    {
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
            
            // ข้อมูลสำหรับแสดงผลในหน้า Dashboard
            'user_name' => $this->commenter->name, 
            'message'   => 'ได้แสดงความคิดเห็นในประกาศของคุณ',
            
            // ลิงก์ปลายทาง (ส่งกลับไปที่หน้า Dashboard) 
            // ตัว Controller จะนำ post_id ด้านบนไปต่อท้ายเป็น #post-id ให้เอง
            'link'      => route('dashboard'),
            
            // ระบุประเภทของการแจ้งเตือน
            'type'      => 'comment',
        ];
    }
}