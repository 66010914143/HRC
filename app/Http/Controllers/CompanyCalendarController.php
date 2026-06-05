<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyCalendar; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyCalendarController extends Controller
{
    /**
     * หน้าแสดงผลปฏิทินและคัดกรองประกาศตามสิทธิ์พนักงาน
     */
    public function index()
    {
        // 1. ดึงรายชื่อสาขาจากตาราง branches ไปแสดงในกล่อง Checkbox
        $branches = DB::table('branches')
                        ->whereNotNull('name')
                        ->where('name', '!=', '')
                        ->orderBy('name', 'asc')
                        ->pluck('name')
                        ->toArray();

        // 2. ดึงรายชื่อฝ่ายจากตาราง departments ไปแสดงในกล่อง Checkbox
        $departments = DB::table('departments')
                           ->whereNotNull('name')
                           ->where('name', '!=', '')
                           ->orderBy('name', 'asc')
                           ->pluck('name')
                           ->toArray();

        $user = Auth::user();
        $eventsQuery = CompanyCalendar::query();
        
        // 🔒 คัดกรองสิทธิ์: พนักงานทั่วไป (ไม่ใช่ระดับ 0, 1, 2, 3) จะเห็นประกาศเฉพาะที่มีชื่อสาขาและฝ่ายของตนเอง
        // 🔑 หากเป็น Admin (ระดับ 0, 1, 2, 3) เงื่อนไขนี้จะถูกข้าม ทำให้สืบค้นเห็นประกาศทั้งหมดทุกรายการ
        if (Auth::check() && !in_array((int)$user->position_level, [0, 1, 2, 3], true)) {
            $eventsQuery->where(function($q) use ($user) {
                $q->where('target_branch', 'LIKE', '%' . $user->branch . '%')
                  ->orWhereNull('target_branch')
                  ->orWhere('target_branch', '');
            })->where(function($q) use ($user) {
                $q->where('target_department', 'LIKE', '%' . $user->department . '%')
                  ->orWhereNull('target_department')
                  ->orWhere('target_department', '');
            });
        }
        
        // 🛠️ ดึงข้อมูลพร้อมโหลด Relationship 'user' และ Map เข้าโครงสร้างที่ FullCalendar ต้องการ
        $calendarEvents = $eventsQuery->with('user')->get()->map(function($event) {
            $isUrgent = (int)$event->is_urgent === 1;

            return [
                'id' => $event->id,
                'title' => $isUrgent ? '🚨 [ด่วน] ' . $event->title : $event->title,
                'start' => $event->event_date,
                
                // กำหนดสีแสดงผลตามประเภทประกาศ (ด่วน = สีแดง / ปกติ = สีน้ำเงิน)
                'backgroundColor' => $isUrgent ? '#ef4444' : '#2563eb', 
                'borderColor'     => $isUrgent ? '#dc2626' : '#1d4ed8', 
                'textColor'       => '#ffffff',                         
                
                'extendedProps' => [
                    'description' => $event->description,
                    'posted_by' => $event->user->name ?? 'ผู้ดูแลระบบ', 
                    'is_urgent' => $isUrgent
                ]
            ];
        });

        return view('company_calendar.index', [
            'branches' => $branches,
            'departments' => $departments,
            'events' => $calendarEvents
        ]);
    }

    /**
     * ฟังก์ชันบันทึกประกาศลงฐานข้อมูล (ปิดสิทธิ์ไม่ให้ Admin และพนักงานระดับ 4, 5 บันทึกได้)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 🚫 บล็อกสิทธิ์หลังบ้าน: หากเป็น Admin (ระดับ 0, 1, 2, 3) หรือระดับ 4, 5 ไม่ให้สิทธิ์โพสต์ประกาศ
        // (ปรับเปลี่ยนหมายเลขระดับใน array ด้านล่างนี้ได้ตามโครงสร้างสิทธิ์ระบบของคุณ)
        if ($user && in_array((int)$user->position_level, [0, 1, 2, 3, 4, 5], true)) {
            return redirect()->route('company_calendar.index')->with('error', 'คุณไม่มีสิทธิ์ในการลงบันทึกประกาศปฏิทิน');
        }

        // ตรวจสอบความถูกต้องของข้อมูลก่อนบันทึก
        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'target_branch' => 'required|array',       
            'target_department' => 'required|array', 
        ]);

        try {
            // มัดรวมรายการที่เลือก (Array) เข้าด้วยกันเป็นข้อความชุดเดียวคั่นด้วยเครื่องหมาย Comma (,)
            $branchString = implode(',', $request->input('target_branch'));
            $departmentString = implode(',', $request->input('target_department'));

            // บันทึกข้อมูลโดยอ้างอิงคอลัมน์ที่มีอยู่จริงในฐานข้อมูลเท่านั้น
            $calendar = new CompanyCalendar();
            $calendar->user_id = Auth::id() ?? 1; 
            $calendar->title = $request->input('title');
            $calendar->description = $request->input('description');
            $calendar->event_date = $request->input('event_date');
            $calendar->target_branch = $branchString;
            $calendar->target_department = $departmentString;
            
            // ตรวจสอบกล่องติ๊ก "ประกาศด่วน" ถ้าเลือกให้เก็บเป็น 1 (true) ถ้าไม่เลือกให้เก็บเป็น 0 (false)
            $calendar->is_urgent = $request->has('is_urgent') ? 1 : 0;
            
            $calendar->save();

            return redirect()->route('company_calendar.index')->with('success', 'ลงบันทึกประกาศปฏิทินเรียบร้อยแล้ว!');

        } catch (\Exception $e) {
            return redirect()->route('company_calendar.index')->with('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage());
        }
    }
}