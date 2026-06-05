<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomBooking;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RoomBookingController extends Controller
{
    /**
     * หน้าหลัก: แสดงปฏิทินการจอง
     */
    public function index()
    {
        $rooms = Room::all();
        $bookings = RoomBooking::with(['user', 'room'])->get();
        
        return view('room_bookings.index', compact('rooms', 'bookings'));
    }

    /**
     * สำหรับ Admin: หน้าเพิ่มห้อง และ จัดการประวัติการจอง (Paginate 5)
     */
    public function createRoom()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'เฉพาะผู้ดูแลระบบเท่านั้นที่เข้าถึงหน้านี้ได้');
        }

        // 1. ดึงข้อมูลห้องแบบแบ่งหน้า 5 รายการ (สำหรับตารางซ้ายมือ)
        $rooms = Room::orderBy('created_at', 'desc')->paginate(5, ['*'], 'rooms_page');

        // 2. ดึงข้อมูลการจองในอนาคต แบบแบ่งหน้า 5 รายการ (ตารางบน)
        $upcomingBookings = RoomBooking::with(['user', 'room'])
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->paginate(5, ['*'], 'upcoming_page');

        // 3. ดึงข้อมูลประวัติการใช้ห้องที่ผ่านมา แบบแบ่งหน้า 5 รายการ (ตารางล่าง)
        $historyBookings = RoomBooking::with(['user', 'room'])
            ->where('start_time', '<', now())
            ->orderBy('start_time', 'desc')
            ->paginate(5, ['*'], 'history_page');

        return view('room_bookings.create_room', compact('rooms', 'upcomingBookings', 'historyBookings'));
    }

    /**
     * สำหรับ Admin: บันทึกการเพิ่มห้อง
     */
    public function storeRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
        ]);

        Room::create($request->all());

        return redirect()->back()->with('success', 'เพิ่มห้องเรียบร้อยแล้ว');
    }

    /**
     * สำหรับ Admin: อัปเดตข้อมูลห้อง (แก้ไขจาก Popup วงเหลือง)
     */
    public function updateRoom(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
        ]);

        $room = Room::findOrFail($id);
        $room->update($request->all());

        return redirect()->back()->with('success', 'อัปเดตข้อมูลห้องเรียบร้อยแล้ว');
    }

    /**
     * สำหรับ Admin: ลบห้องประชุม
     */
    public function destroyRoom($id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        $room = Room::findOrFail($id);
        $room->delete();

        return redirect()->back()->with('success', 'ลบห้องประชุมเรียบร้อยแล้ว');
    }

    /**
     * สำหรับ User/Admin: บันทึกการจองห้อง
     */
    public function storeBooking(Request $request)
    {
        // 🛠️ ตรวจสอบสิทธิ์โดยใช้ฟิลด์ 'position_level' (อ้างอิงข้อมูลจาก UserController)
        $user = Auth::user();
        $level = (string)($user->position_level ?? ''); 

        if ($level === '4' || $level === '5') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['permission' => '❌ พนักงานระดับ 4 และ 5 ไม่ได้รับอนุญาตให้จองห้องประชุม']);
        }

        // --- โค้ดเดิมทั้งหมดคงเดิม ไม่มีการปรับเปลี่ยนใดๆ ---
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'title' => 'required|string|max:255',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $start = Carbon::parse($request->start_time);
        $end = Carbon::parse($request->end_time);

        if ($start->diffInDays(Carbon::now()) > 30) {
            return redirect()->back()->with('error', 'ไม่สามารถจองห้องล่วงหน้าเกิน 30 วันได้');
        }

        // ตรวจสอบการจองซ้อน
        $isOverlap = RoomBooking::where('room_id', $request->room_id)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_time', [$start, $end])
                      ->orWhereBetween('end_time', [$start, $end])
                      ->orWhere(function ($q) use ($start, $end) {
                          $q->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                      });
            })->exists();

        if ($isOverlap) {
            return redirect()->back()->with('error', 'ช่วงเวลานี้ห้องถูกจองไปแล้ว กรุณาเลือกเวลาอื่น');
        }

        RoomBooking::create([
            'user_id' => Auth::id(),
            'room_id' => $request->room_id,
            'title' => $request->title,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return redirect()->route('room_bookings.index')->with('success', 'จองห้องสำเร็จแล้ว');
    }

    /**
     * ระบบลบการจอง (ทั้ง User และ Admin)
     */
    public function destroyBooking($id)
    {
        $booking = RoomBooking::findOrFail($id);
        
        if ($booking->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ลบรายการนี้');
        }

        $booking->delete();
        return redirect()->back()->with('success', 'ยกเลิกการจองเรียบร้อยแล้ว');
    }
}