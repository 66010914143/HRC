<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * ทำเครื่องหมายแจ้งเตือนว่าอ่านแล้ว และเปลี่ยนเส้นทางไปยังลิงก์ที่เกี่ยวข้อง
     */
    public function markAsRead($id)
    {
        // ค้นหาการแจ้งเตือนของ User ที่ล็อกอินอยู่
        $notification = Auth::user()->notifications()->findOrFail($id);

        if ($notification) {
            // ทำเครื่องหมายว่าอ่านแล้ว
            $notification->markAsRead();
        }

        // 1. ดึงลิงก์หลักจาก data['link'] หากไม่มีให้กลับไปหน้า Dashboard
        $targetUrl = $notification->data['link'] ?? route('dashboard');

        // 2. ตรวจสอบว่ามี post_id หรือไม่ และป้องกันการเติม Anchor ซ้ำ
        // ถ้าใน $targetUrl ยังไม่มีคำว่า '#post-' ให้เติมเข้าไป
        if (isset($notification->data['post_id']) && !str_contains($targetUrl, '#post-')) {
            $targetUrl .= '#post-' . $notification->data['post_id'];
        }

        // 3. ปรับแต่ง URL ให้สะอาด (ลบ query string ที่อาจค้างมาจากระบบเก่าออกถ้าจำเป็น)
        // เพื่อป้องกันปัญหา dashboard#post-47?read=... แบบในรูปก่อนหน้า
        return redirect()->to($targetUrl);
    }

    /**
     * ลบการแจ้งเตือนทั้งหมดของ User รายนี้
     */
    public function clearAll()
    {
        $user = Auth::user();
        
        // ลบข้อมูลการแจ้งเตือนทั้งหมดออกจากฐานข้อมูล
        $user->notifications()->delete();

        return back()->with('success', 'ล้างการแจ้งเตือนทั้งหมดเรียบร้อยแล้ว');
    }
}