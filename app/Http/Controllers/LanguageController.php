<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * สลับภาษาและบันทึกลง Session
     */
    public function switchLang($lang)
    {
        // ตรวจสอบว่าภาษาที่ส่งมาอยู่ในกลุ่มที่เรากำหนดไว้หรือไม่
        if (in_array($lang, ['th', 'en', 'my'])) {
            // บันทึกภาษาลงใน Session
            Session::put('locale', $lang);
        }
        
        // รีเฟรชกลับไปหน้าเดิมที่ผู้ใช้กดมา
        return redirect()->back();
    }
}