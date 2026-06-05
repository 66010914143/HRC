<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
// นำเข้า Class Notification ให้เรียบร้อย
use App\Notifications\NewCommentNotification; 

class CommentController extends Controller
{
    public function store(Request $request, $postId)
    {
        // ตรวจสอบความถูกต้องของข้อมูล
        $request->validate([
            'comment_text' => 'required|string|max:500',
        ]);

        // ค้นหาโพสต์
        $post = Post::findOrFail($postId);

        // 1. บันทึกคอมเมนต์ลงตาราง comments
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'comment_text' => $request->comment_text,
        ]);

        /**
         * 2. ส่วนส่งแจ้งเตือน
         * ระบบจะทำการส่ง Notification ลงตาราง notifications ทันที
         */
        if ($post->user_id !== auth()->id()) {
            // ส่งแจ้งเตือนหาเจ้าของโพสต์
            $post->user->notify(new NewCommentNotification($post, auth()->user()));
        }

        // ส่งกลับหน้าเดิมพร้อมข้อความสำเร็จ
        return back()->with('success', 'แสดงความคิดเห็นเรียบร้อยแล้ว!');
    }

    // --- ฟังก์ชันสำหรับแก้ไขคอมเมนต์ ---
    public function update(Request $request, Comment $comment)
    {
        // ตรวจสอบสิทธิ์ว่าเป็นเจ้าของคอมเมนต์หรือไม่
        if (auth()->id() !== $comment->user_id) {
            return back()->with('error', 'คุณไม่มีสิทธิ์แก้ไขความคิดเห็นนี้');
        }

        $request->validate([
            'comment_text' => 'required|string|max:500',
        ]);

        $comment->update([
            'comment_text' => $request->comment_text
        ]);

        return back()->with('success', 'อัปเดตความคิดเห็นเรียบร้อยแล้ว');
    }

    /**
     * --- ฟังก์ชันสำหรับลบคอมเมนต์และลบแจ้งเตือน ---
     * ปรับปรุงให้ตรงกับข้อมูลใน image_044499.jpg
     */
    public function destroy(Comment $comment)
    {
        // 1. ตรวจสอบสิทธิ์ก่อนลบ
        if (auth()->id() !== $comment->user_id) {
            return back()->with('error', 'คุณไม่มีสิทธิ์ลบความคิดเห็นนี้');
        }

        // 2. ดึงเจ้าของโพสต์มาเพื่อลบการแจ้งเตือน
        $postOwner = $comment->post->user; 
        
        if ($postOwner) {
            /**
             * วนลูปเช็ค notifications โดยเน้นที่ post_id เป็นหลัก
             * เนื่องจากในฐานข้อมูลของบอย (image_044499.jpg) 
             * คอลัมน์ data มีค่าหลักคือ post_id และ post_title
             */
            foreach ($postOwner->notifications as $notification) {
                if (isset($notification->data['post_id']) && 
                    $notification->data['post_id'] == $comment->post_id) {
                    
                    // ลบแจ้งเตือนที่ตรงกับ post_id ของคอมเมนต์นี้
                    $notification->delete(); 
                }
            }
        }

        // 3. ลบคอมเมนต์
        $comment->delete();

        return back()->with('success', 'ลบความคิดเห็นและแจ้งเตือนเรียบร้อยแล้ว');
    }
}