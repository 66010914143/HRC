<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\JobTitle; // <-- เรียกใช้โมเดล JobTitle เพื่อตรวจสอบประเภทตำแหน่งงาน
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * 1. ฟังก์ชันหน้าแรกของระบบลา แสดงประวัติและรายการรออนุมัติ
     * (ปรับปรุงสำหรับ Admin: เพิ่มช่องค้นหาชื่อ-นามสกุลพนักงานแยกแท็บ และทำระบบแบ่งหน้าพนักงานระดับสูง/ทั่วไป หน้าละ 10 รายการ)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // บันทึกเวลาที่ผู้ใช้เข้าดูหน้านี้ล่าสุด เพื่อเคลียร์ Badge แจ้งเตือน (ปรับปรุงเป็น save ตรงเพื่อเลี่ยงปัญหา fillable)
        $user->last_leave_view_at = now();
        $user->save();

        // 🚀 ดึงข้อมูลผู้ใช้งานที่เป็นระดับหัวหน้า (head) หรือแอดมิน เพื่อส่งให้ตัวแปร $managers ป้องกัน Error ในหน้า View
        $managers = User::whereIn('position', function($query) {
                        $query->select('name')->from('job_titles')->where('position_type', 'head');
                    })
                    ->orWhere('role', 'admin')
                    ->get();

        // --- เพิ่มเติม: Logic แยกการแสดงผลสำหรับสิทธิ์ผู้ดูแลระบบ (Admin) ---
        if ($user->role === 'admin') {
            
            // รับค่าคำค้นหาจากฟอร์ม (ถ้ามี)
            $search = $request->input('search');

            // 1. ประวัติการลาของพนักงานระดับสูง (เงื่อนไขระบุตำแหน่งเฉพาะเจาะจง) + ระบบค้นหาและแบ่งหน้า
            $highLevelLeaves = LeaveRequest::whereHas('user', function($query) use ($search) {
                                    $query->where(function($q) {
                                        $q->whereIn('position', [
                                              'ประธานเจ้าหน้าที่บริหาร', 
                                              'ประธานผู้บริหารสายงาน',
                                              'ประธานสายงาน', 
                                              'ผู้อำนวยการอาวุโสกลุ่มงาน', 
                                              'ผู้จัดการอาวุโสกลุ่มงาน', 
                                              'ผู้จัดการฝ่าย', 
                                              'ผู้ชำนาญการ'
                                          ]);
                                    });
                                    // เงื่อนไขสำหรับค้นหาชื่อพนักงาน (ปรับปรุงให้ค้นหาแบบ Partial Match ค้นหาคำบางส่วนได้)
                                    if ($search) {
                                        $query->where(function($q) use ($search) {
                                            $q->where('name', 'LIKE', "%{$search}%")
                                              ->orWhere('last_name', 'LIKE', "%{$search}%")
                                              ->orWhereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                                        });
                                    }
                                })
                                ->with(['user', 'approver'])
                                ->orderBy('created_at', 'desc')
                                ->paginate(10, ['*'], 'high_page') // แบ่งหน้าละ 10 รายการ แยกชื่อ Parameter หน้าไม่ให้ตีกัน
                                ->appends($request->all()); // ผูกค่า Search ยิงตามไปเวลากดเปลี่ยนหน้า

            // 2. ประวัติการลาของพนักงานทั่วไป (ดึงทุกคนที่ไม่อยู่ในกลุ่มระดับสูง เพื่อป้องกันข้อมูลของพนักงานบางคนหล่นหาย) + ระบบค้นหาและแบ่งหน้า
            $generalLeaves = LeaveRequest::whereHas('user', function($query) use ($search) {
                                    $query->where(function($q) {
                                        $q->whereNotIn('position', [
                                              'ประธานเจ้าหน้าที่บริหาร', 
                                              'ประธานผู้บริหารสายงาน',
                                              'ประธานสายงาน', 
                                              'ผู้อำนวยการอาวุโสกลุ่มงาน', 
                                              'ผู้จัดการอาวุโสกลุ่มงาน', 
                                              'ผู้จัดการฝ่าย', 
                                              'ผู้ชำนาญการ'
                                          ]);
                                    });
                                    // เงื่อนไขสำหรับค้นหาชื่อพนักงาน (ปรับปรุงให้ค้นหาแบบ Partial Match ค้นหาคำบางส่วนได้)
                                    if ($search) {
                                        $query->where(function($q) use ($search) {
                                            $q->where('name', 'LIKE', "%{$search}%")
                                              ->orWhere('last_name', 'LIKE', "%{$search}%")
                                              ->orWhereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                                        });
                                    }
                                })
                                ->with(['user', 'approver'])
                                ->orderBy('created_at', 'desc')
                                ->paginate(10, ['*'], 'general_page') // แบ่งหน้าละ 10 รายการ แยกชื่อ Parameter หน้าไม่ให้ตีกัน
                                ->appends($request->all()); // ผูกค่า Search ยิงตามไปเวลากดเปลี่ยนหน้า

            // ประวัติการลาของตัวแอดมินเอง ดึงเผื่อไว้ใช้งานร่วมกับแท็บข้อมูลฝั่ง Admin
            $myLeaves = LeaveRequest::where('user_id', $user->id)->with('approver')->get();
            $pendingApprovals = collect();

            // ส่งข้อมูลออกไปยังหน้า View สำหรับ Adminพร้อมตัวแปร $managers เพื่อใช้งานในฟอร์มลา
            return view('leave.index', compact('highLevelLeaves', 'generalLeaves', 'myLeaves', 'pendingApprovals', 'managers'));
        }

        // --- โครงสร้างการทำงานเดิม: สำหรับ User/พนักงานทั่วไป หรือหัวหน้างานล็อกอินเข้ามา ---
        $myLeaves = LeaveRequest::where('user_id', $user->id)
                    ->with('approver') // ดึงข้อมูลผู้ตรวจสอบเพื่อส่งไปแสดงผลที่หน้า View อย่างสมบูรณ์
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        $pendingApprovals = LeaveRequest::where('approver_id', $user->id)
                            ->where('status', 'pending')
                            ->with('user')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        // 定กำหนดค่าเริ่มต้นคอลเลกชันว่างให้ตัวแปรของสิทธิ์ Admin ป้องกันการพังเมื่อแชร์หน้าวิวร่วมกัน
        $highLevelLeaves = collect();
        $generalLeaves = collect();

        // ส่งข้อมูลออกไปยังหน้า View สำหรับ User พร้อมตัวแปร $managers
        return view('leave.index', compact('myLeaves', 'pendingApprovals', 'highLevelLeaves', 'generalLeaves', 'managers'));
    }

    /**
     * 2. ฟังก์ชันบันทึกการลา (แก้ไขเงื่อนไขตามชื่อตำแหน่งงาน และรองรับตำแหน่งใหม่ให้เป็นพนักงานปกติ)
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required',
            'evidence' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = Auth::user();
        $status = 'pending';
        $approver_id = null;

        // ดึงข้อมูล Admin หลักไว้สำหรับกรณีหาหัวหน้าไม่เจอ (Fallback)
        $admin = User::where('role', 'admin')->first();
        $adminId = $admin ? $admin->id : 1;

        // ตัดช่องว่างของตำแหน่งออก
        $pos = trim($user->position); 

        // --- 🚀 เริ่มตรวจสอบเงื่อนไขสายการอนุมัติตามประเภทสิทธิ์ใหม่ (พนักงาน / หัวหน้าแผนก) ---
        // 1. ดึงประเภทสิทธิ์ของตำแหน่งนี้จากฐานข้อมูล JobTitle
        $jobTitle = JobTitle::where('name', $pos)->first();
        $positionType = $jobTitle ? $jobTitle->position_type : 'employee';

        // 2. แยก Workflow ตามสิทธิ์ที่ตั้งไว้
        if ($positionType === 'head') {
            // กรณีเป็นหัวหน้าแผนก: อนุมัติผ่านอัตโนมัติ (เปลี่ยนสถานะ และผูกกับ Admin เพื่อเก็บประวัติไว้ตรวจสอบ)
            $status = 'approved';
            $approver_id = $adminId; 
        } else {
            // เช็คว่าใน Request มีการระบุเลือกผู้อนุมัติ (approver_id) จาก Dropdown หน้าบ้านมาหรือไม่
            if ($request->has('approver_id') && $request->approver_id != '') {
                $approver_id = $request->approver_id;
            } else {
                // กรณีสิทธิ์พนักงาน (ค้นหาผู้ใช้งานในแผนกและสาขาเดียวกัน ที่มีตำแหน่งเป็น "หัวหน้าแผนก" (head) อัตโนมัติเป็นระบบสำรอง)
                $approver = User::where('department', $user->department)
                                ->where('branch', $user->branch)
                                ->where('id', '!=', $user->id) // ไม่เอาตัวเอง
                                ->whereIn('position', function($query) {
                                    $query->select('name')->from('job_titles')->where('position_type', 'head');
                                })->first();
                
                $approver_id = $approver ? $approver->id : null;
            }
        }

        // --- กรณี Fallback: ถ้าต้องมีคนอนุมัติแต่ในระบบยังไม่มีพนักงานระดับหัวหน้าในแผนก/สาขานั้น ให้ส่งหา Admin หลักเพื่อป้องกันคำขอลาหาย ---
        if ($status == 'pending' && !$approver_id) {
             $approver_id = $adminId;
        }

        // จัดการไฟล์รูปภาพหลักฐาน
        $imageName = null;
        if ($request->hasFile('evidence')) {
            $imageName = 'leave_' . time() . '_' . uniqid() . '.' . $request->evidence->extension();
            $request->evidence->storeAs('public/leave_evidence', $imageName);
        }

        // บันทึกข้อมูลลงฐานข้อมูล
        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'evidence_image' => $imageName,
            'status' => $status,
            'approver_id' => $approver_id
        ]);

        return redirect()->back()->with('success', 'ส่งใบลาเรียบร้อยแล้ว');
    }

    /**
     * 3. อนุมัติใบลา (ทำรายการผ่านหน้ารวม leave.index คงโค้ดเดิมไว้)
     */
    public function approve($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        
        // อนุญาตให้คนที่เป็น approver_id หรือ Admin เป็นคนกดอนุมัติได้
        if ($leave->approver_id != Auth::id() && Auth::user()->role !== 'admin') {
            return back()->with('error', 'คุณไม่มีสิทธิ์อนุมัติใบลาฉบับนี้');
        }

        // อัปเดตสถานะ พร้อมกับบันทึกไอดีของคนที่กดอนุมัติจริง ณ เวลานั้น
        $leave->update([
            'status' => 'approved',
            'approver_id' => Auth::id() 
        ]);
        return back()->with('success', 'อนุมัติใบลาเรียบร้อยแล้ว');
    }

    /**
     * 4. ปฏิเสธใบลา (ทำรายการผ่านหน้ารวม leave.index คงโค้ดเดิมไว้)
     */
    public function reject(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        
        // อนุญาตให้คนที่เป็น approver_id หรือ Admin เป็นคนกดปฏิเสธได้
        if ($leave->approver_id != Auth::id() && Auth::user()->role !== 'admin') {
            return back()->with('error', 'คุณไม่มีสิทธิ์ปฏิเสธใบลาฉบับนี้');
        }

        // อัปเดตสถานะ คอมเมนต์ พร้อมกับบันทึกไอดีของคนที่กดปฏิเสธจริง ณ เวลานั้น
        $leave->update([
            'status' => 'rejected',
            'comment' => $request->comment,
            'approver_id' => Auth::id() 
        ]);
        return back()->with('success', 'ปฏิเสธใบลาเรียบร้อยแล้ว');
    }

    /**
     * ฟังก์ชันช่วยค้นหาผู้อนุมัติจากตำแหน่ง แผนก และสาขา (คงโค้ดเดิมไว้เผื่อระบบอื่นเรียกใช้)
     */
    private function findApprover($positionName, $department, $branch)
    {
        $approver = User::where('position', $positionName)
                        ->where('department', $department)
                        ->where('branch', $branch)
                        ->first();
                        
        return $approver ? $approver->id : null;
    }

    /**
     * 5. หน้าประวัติการกดยอมรับ/ปฏิเสธของพนักงาน และประวัติการขอลาของหัวหน้างานเองทั้งหมด (คงโค้ดเดิมไว้)
     */
    public function approvals()
    {
        $user = Auth::user();

        // --- 🚀 เช็คสิทธิ์ตามระบบใหม่ ว่าคนล็อกอินมีสถานะเป็นหัวหน้าแผนก (head) หรือไม่ ---
        $jobTitle = JobTitle::where('name', trim($user->position))->first();
        $isHead = $jobTitle && $jobTitle->position_type === 'head';

        // รายชื่อกลุ่มตำแหน่งพนักงานระดับสูงที่มีสิทธิ์เป็นผู้อนุมัติในระบบสิทธิ์องค์กร (เก็บไว้รองรับระบบเก่า)
        $leaveApproverPositions = [
            'ประธานเจ้าหน้าที่บริหาร',
            'ประธานผู้บริหารสายงาน',
            'ประธานสายงาน',
            'ผู้อำนวยการอาวุโสกลุ่มงาน',
            'ผู้จัดการกลุ่มงาน',
            'ผู้จัดการฝ่าย',
            'ผู้ชำนาญการ'
        ];

        // ตรวจสอบความปลอดภัย: หากไม่ใช่หัวหน้าแผนก (isHead), ตำแหน่งไม่อยู่ในกลุ่มพนักงานระดับสูง, และไม่ใช่ admin จะดีดกลับ
        if (!$isHead && !in_array($user->position, $leaveApproverPositions) && $user->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงศูนย์อนุมัติการลา');
        }

        // ส่วนที่ 1: ประวัติการอนุมัติ/ปฏิเสธใบลาของลูกน้อง (ดึงเฉพาะสถานะ approved และ rejected ไม่เอา pending)
        $approvedHistory = LeaveRequest::where('approver_id', $user->id)
                            ->whereIn('status', ['approved', 'rejected'])
                            ->with('user')
                            ->orderBy('updated_at', 'desc')
                            ->get();

        // ส่วนที่ 2: ประวัติคำขอลา "ทุกสถานะ" ของตัวหัวหน้าคนนี้เองที่เคยกดลาในระบบ
        $myLeaveHistory = LeaveRequest::where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->get();

        // แก้ไขจุดนี้: ใส่ ->with('user') เพื่อดึงข้อมูล ชื่อตำแหน่ง และฝ่ายของพนักงานผู้ส่งใบลาแนบมาด้วย
        $leaveRequests = LeaveRequest::where('approver_id', $user->id)
                            ->where('status', 'pending')
                            ->with('user')
                            ->orderBy('created_at', 'desc')
                            ->get();

        // ดึงไฟล์วิวจาก leave.historyleave.approvals โดยส่งตัวแปรที่หน้า View ต้องการไปให้ครบทั้งหมด
        return view('leave.historyleave.approvals', compact('approvedHistory', 'myLeaveHistory', 'leaveRequests'));
    }

    /**
     * 6. ฟังก์ชันสำหรับเปิดหน้าพิมพ์ใบลา (PDF) แยกต่างหาก (เพิ่มใหม่ตามคำขอ)
     */
    public function print($id)
    {
        // ดึงข้อมูลใบลาพร้อมข้อมูลผู้ใช้งานและผู้อนุมัติแบบ Eager Loading
        $leave = LeaveRequest::with(['user', 'approver'])->findOrFail($id);
        
        // ส่งข้อมูลไปยังหน้า View ตัวแยกสำหรับการพิมพ์โดยเฉพาะ
        return view('leave.print', compact('leave'));
    }
}