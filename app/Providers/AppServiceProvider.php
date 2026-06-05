<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use App\Models\CompanyCalendar; // นำเข้า Model ปฏิทิน
use Carbon\Carbon;              // นำเข้า Carbon สำหรับจัดการวันที่

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ใช้ View Composer เพื่อส่งข้อมูลไปยังไฟล์ layouts.app ในทุกๆ หน้า
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $lastView = $user->last_leave_view_at;

                // ==========================================
                // 📝 ส่วนที่ 1: ระบบแจ้งเตือนการลา (โค้ดเดิมของคุณทั้งหมด)
                // ==========================================
                
                // 1. นับรายการรออนุมัติสำหรับหัวหน้า (เฉพาะที่มาใหม่หลังจากกดดูล่าสุด)
                $pendingCount = LeaveRequest::where('approver_id', $user->id)
                    ->where('status', 'pending')
                    ->when($lastView, function ($query) use ($lastView) {
                        return $query->where('created_at', '>', $lastView);
                    })
                    ->count();

                // 2. นับรายการที่อนุมัติ/ปฏิเสธสำหรับลูกน้อง (เฉพาะที่อัปเดตใหม่หลังจากกดดูล่าสุด)
                $myUpdateCount = LeaveRequest::where('user_id', $user->id)
                    ->whereIn('status', ['approved', 'rejected', 'auto_approved'])
                    ->when($lastView, function ($query) use ($lastView) {
                        return $query->where('updated_at', '>', $lastView);
                    })
                    ->count();

                // รวมผลลัพธ์ทั้งหมด
                $totalNotifications = $pendingCount + $myUpdateCount;

                // ส่งตัวแปรไปให้ View
                $view->with('leaveNotificationCount', $totalNotifications);


                // ==========================================
                // 📅 ส่วนที่ 2: ระบบแจ้งเตือนปฏิทินองค์กร (แยกประเภท ด่วน / ปกติ)
                // ==========================================
                
                // เก็บเวลาเปิดดูปฏิทินล่าสุดลงใน Cookie เมื่อผู้ใช้งานอยู่ในหน้าปฏิทินองค์กร
                if (request()->routeIs('company_calendar.index')) {
                    \Illuminate\Support\Facades\Cookie::queue('calendar_read_at_' . $user->id, now()->toIso8601String(), 1440);
                }
                
                // ดึงเวลาการเปิดดูล่าสุดจาก Cookie
                $calendarReadAt = request()->routeIs('company_calendar.index') ? now()->toIso8601String() : \Illuminate\Support\Facades\Cookie::get('calendar_read_at_' . $user->id);

                // ดึงเฉพาะประกาศของ "วันนี้" และ "วันในอนาคต"
                $calendarQuery = CompanyCalendar::whereDate('event_date', '>=', Carbon::today());

                // คัดกรองสิทธิ์ให้ตรงกับสาขา/ฝ่ายของพนักงานที่ล็อกอินอยู่
                if (!in_array((int)$user->position_level, [0, 1, 2, 3], true)) {
                    $calendarQuery->where(function($q) use ($user) {
                        $q->where('target_branch', 'LIKE', '%' . $user->branch . '%')
                          ->orWhereNull('target_branch')
                          ->orWhere('target_branch', '');
                    })->where(function($q) use ($user) {
                        $q->where('target_department', 'LIKE', '%' . $user->department . '%')
                          ->orWhereNull('target_department')
                          ->orWhere('target_department', '');
                    });
                }

                // 🆕 คัดกรองและนับจำนวนแจ้งเตือนตามเงื่อนไขประเภทประกาศ ด่วน หรือ ไม่ด่วน
                $calendarQuery->where(function($query) use ($calendarReadAt) {
                    $query->where('is_urgent', 1) // 1. ถ้าเป็น "ประกาศด่วน" -> ให้นับแจ้งเตือนตลอดจนกว่าจะหมดวันกิจกรรม
                          ->orWhere(function($subQuery) use ($calendarReadAt) {
                              // 2. ถ้าเป็น "ประกาศปกติ" -> นับเฉพาะรายการที่สร้างใหม่หลังจากการกดดูครั้งล่าสุดเท่านั้น
                              $subQuery->where('is_urgent', 0);
                              if ($calendarReadAt) {
                                  $subQuery->where('created_at', '>', \Carbon\Carbon::parse($calendarReadAt));
                              }
                          });
                });

                // นับจำนวนและส่งไปในชื่อตัวแปร $calendarNotiCount
                $view->with('calendarNotiCount', $calendarQuery->count());
            }
        });
    }
}