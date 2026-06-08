<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    public function updateImage(Request $request)
    {
        // 1. ตรวจสอบไฟล์
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            try {
                // 2. ลบรูปเก่า (ถ้ามี)
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                // 3. เก็บไฟล์ใหม่
                $path = $request->file('profile_image')->store('profile_images', 'public');
                
                // 4. บันทึกลง Database
                $user->update([
                    'profile_image' => $path
                ]);

                return response()->json([
                    'success' => true, 
                    'message' => 'อัปโหลดรูปภาพสำเร็จ',
                    'path' => asset('storage/' . $path)
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false, 
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => false, 
            'message' => 'ไม่พบไฟล์ที่ส่งมา'
        ]);
    }

    // เพิ่มฟังก์ชันใหม่สำหรับการอัปโหลดลายเซ็นอิเล็กทรอนิกส์
    public function updateSignature(Request $request)
    {
        // 1. ตรวจสอบไฟล์ (อนุญาตเฉพาะไฟล์รูปภาพ)
        $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('signature')) {
            try {
                // 2. ลบรูปเก่า (ถ้ามี)
                if ($user->signature) {
                    Storage::disk('public')->delete($user->signature);
                }

                // 3. เก็บไฟล์ใหม่ไปที่โฟลเดอร์ signatures
                $path = $request->file('signature')->store('signatures', 'public');
                
                // 4. บันทึกลง Database
                $user->update([
                    'signature' => $path
                ]);

                return response()->json([
                    'success' => true, 
                    'message' => 'อัปโหลดลายเซ็นสำเร็จ',
                    'path' => asset('storage/' . $path)
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false, 
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => false, 
            'message' => 'ไม่พบไฟล์ที่ส่งมา'
        ]);
    }
}