<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WelfareRequest;
use App\Models\User;
use App\Models\JobTitle; // <-- เพิ่มการเรียกใช้โมเดล JobTitle เพื่อดึงข้อมูลประเภทสิทธิ์ใหม่
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WelfareRequestController extends Controller
{
    /**
     * หน้าแสดงรายการใบเบิกสวัสดิการ (หน้าหลักของผู้ใช้งานทั่วไป)
     */
    public function index()
    {
        $user = Auth::user();
        
        // 🟢 เพิ่มเติม: เคลียร์แจ้งเตือนที่ยังไม่ได้อ่านเมื่อผู้ใช้กดเข้ามาดูหน้าหลักสวัสดิการ
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
        
        $myRequests = WelfareRequest::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        $welfareRequests = $myRequests;

        // แก้ไข: เพิ่ม 'ผู้จัดการกลุ่มงาน' เข้าไปในสิทธิ์ผู้อนุมัติ
        $approverPositions = [
            'ประธานเจ้าหน้าที่บริหาร', 
            'ประธานสายงาน', 
            'ผู้อำนวยการอาวุโสกลุ่มงาน', 
            'ผู้จัดการอาวุโสกลุ่มงาน', 
            'ผู้จัดการกลุ่มงาน',
            'ผู้จัดการฝ่าย',
            'ผู้ชำนาญการ'
        ];

        $pendingApprovals = collect();
        
        // ตรวจสอบสิทธิ์หัวหน้าแผนกจากตารางตำแหน่งใหม่
        $jobTitle = JobTitle::where('name', trim($user->position))->first();
        $isHead = $jobTitle && $jobTitle->position_type === 'head';

        if ($isHead || in_array($user->position, $approverPositions)) {
            // ดึงเฉพาะรายการคำขอเบิกที่ระบุ approver_id ตรงกับ ID ของหัวหน้าที่เข้าสู่ระบบอยู่ขณะนั้น
            $pendingApprovals = WelfareRequest::with('user')
                                ->where('status', 'pending')
                                ->where('approver_id', $user->id)
                                ->where('user_id', '!=', $user->id)
                                ->orderBy('created_at', 'asc')
                                ->get();
        }

        return view('welfare.index', compact('myRequests', 'pendingApprovals', 'welfareRequests'));
    }

    /**
     * หน้าประวัติการเบิก (แยกตามสิทธิ์ Admin และ User พร้อมระบบแบ่งกลุ่มและค้นหา)
     */
    public function history(Request $request) // 🟢 เพิ่มการรับค่า Request $request เพื่อเอามาทำระบบค้นหา
    {
        $user = Auth::user();
        
        // 🟢 เพิ่มเติม: เคลียร์แจ้งเตือนที่ยังไม่ได้อ่านเมื่อผู้ใช้กดเข้ามาดูหน้าประวัติสวัสดิการ
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
        
        // แก้ไข: เพิ่ม 'ผู้จัดการกลุ่มงาน' เข้าไป
        $highLevelPositions = [
            'ประธานเจ้าหน้าที่บริหาร', 
            'ประธานสายงาน', 
            'ผู้อำนวยการอาวุโสกลุ่มงาน', 
            'ผู้จัดการอาวุโสกลุ่มงาน', 
            'ผู้จัดการกลุ่มงาน',
            'ผู้จัดการฝ่าย', 
            'ผู้ชำนาญการ'
        ];

        if ($user->role === 'admin') {
            // 🟢 1. รับค่าคำค้นหา (ชื่อ-นามสกุล) จากช่องค้นหาในหน้า View
            $search = $request->input('search');

            // 🟢 2. ดึงข้อมูลกลุ่มพนักงานระดับสูง (หัวหน้าแผนก/ผู้บริหาร) + เงื่อนไขการค้นหา
            $highLevelRequests = WelfareRequest::with('user')
                ->whereHas('user', function($query) use ($highLevelPositions, $search) {
                    $query->where(function($q) use ($highLevelPositions) {
                        $q->whereIn('position', $highLevelPositions)
                          ->orWhereIn('position', function($subQuery) {
                              $subQuery->select('name')->from('job_titles')->where('position_type', 'head');
                          });
                    });
                    
                    // เงื่อนไขการค้นหาชื่อ-นามสกุล
                    if ($search) {
                        $query->where(function($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('last_name', 'LIKE', "%{$search}%")
                              ->orWhereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                        });
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // 🟢 3. ดึงข้อมูลกลุ่มพนักงานทั่วไป (Employee) + เงื่อนไขการค้นหา
            $generalRequests = WelfareRequest::with('user')
                ->whereHas('user', function($query) use ($highLevelPositions, $search) {
                    $query->where(function($q) use ($highLevelPositions) {
                        $q->whereNotIn('position', $highLevelPositions)
                          ->whereNotIn('position', function($subQuery) {
                              $subQuery->select('name')->from('job_titles')->where('position_type', 'head');
                          });
                    });
                    
                    // เงื่อนไขการค้นหาชื่อ-นามสกุล
                    if ($search) {
                        $query->where(function($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('last_name', 'LIKE', "%{$search}%")
                              ->orWhereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                        });
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            $isAdminView = true;
            $welfareRequests = collect(); // ส่งตัวแปรเปล่าหลบ Error ของโครงสร้างเดิม

            // ส่งข้อมูลทั้งหมดรวมถึงตัวแปรแยกฝั่ง และค่า search กลับไปที่หน้า View
            return view('welfare.history', compact('highLevelRequests', 'generalRequests', 'welfareRequests', 'isAdminView', 'search'));

        } else {
            // สำหรับพนักงานธรรมดา ดูประวัติเฉพาะของตนเอง
            $welfareRequests = WelfareRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $isAdminView = false;
            $highLevelRequests = collect();
            $generalRequests = collect();
            $search = null;

            return view('welfare.history', compact('welfareRequests', 'isAdminView', 'highLevelRequests', 'generalRequests', 'search'));
        }
    }

    /**
     * หน้าจัดการคำขออนุมัติ (สำหรับผู้บริหาร/หัวหน้างาน)
     */
    public function approvals()
    {
        $user = Auth::user();
        // แก้ไข: เพิ่ม 'ผู้จัดการกลุ่มงาน' เข้าไป
        $approverPositions = ['ประธานเจ้าหน้าที่บริหาร', 'ประธานสายงาน', 'ผู้อำนวยการอาวุโสกลุ่มงาน', 'ผู้จัดการอาวุโสกลุ่มงาน', 'ผู้จัดการกลุ่มงาน', 'ผู้จัดการฝ่าย', 'ผู้ชำนาญการ'];

        // ตรวจสอบผ่านระบบสิทธิ์ใหม่
        $jobTitle = JobTitle::where('name', trim($user->position))->first();
        $isHead = $jobTitle && $jobTitle->position_type === 'head';

        if (!$isHead && !in_array($user->position, $approverPositions)) {
            return redirect()->route('welfare.index')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        // ดึงเฉพาะรายการที่สถานะรอนุมัติ และระบุ approver_id มาที่ตัวหัวหน้าคนปัจจุบันเท่านั้น
        $pendingRequests = WelfareRequest::with('user')
                            ->where('status', 'pending')
                            ->where('approver_id', $user->id)
                            ->where('user_id', '!=', $user->id)
                            ->orderBy('created_at', 'asc')
                            ->get();

        return view('welfare.approvals', compact('pendingRequests'));
    }

    public function create()
    {
        return view('welfare.create');
    }

    public function show($id)
    {
        $welfareRequest = WelfareRequest::with('user')->findOrFail($id);
        $user = Auth::user();

        // แก้ไข: เพิ่ม 'ผู้จัดการกลุ่มงาน' เข้าไป
        $approverPositions = ['ประธานเจ้าหน้าที่บริหาร', 'ประธานสายงาน', 'ผู้อำนวยการอาวุโสกลุ่มงาน', 'ผู้จัดการอาวุโสกลุ่มงาน', 'ผู้จัดการกลุ่มงาน', 'ผู้จัดการฝ่าย', 'ผู้ชำนาญการ'];

        // ตรวจสอบผ่านระบบสิทธิ์ใหม่
        $jobTitle = JobTitle::where('name', trim($user->position))->first();
        $isHead = $jobTitle && $jobTitle->position_type === 'head';

        if ($welfareRequest->user_id !== $user->id && !$isHead && !in_array($user->position, $approverPositions) && $user->role !== 'admin') {
            return redirect()->route('welfare.index')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้');
        }

        return view('welfare.show', compact('welfareRequest'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $user = Auth::user();
        $status = 'pending';
        $approverId = null;
        
        // --- 🚀 เริ่มตรวจสอบเงื่อนไขสายการอนุมัติตามประเภทสิทธิ์ใหม่ (พนักงาน / หัวหน้าแผนก) ---
        $jobTitle = JobTitle::where('name', trim($user->position))->first();
        $positionType = $jobTitle ? $jobTitle->position_type : 'employee';

        if ($positionType === 'head') {
            // กรณีเป็นหัวหน้าแผนก: อนุมัติผ่านอัตโนมัติทันที
            $status = 'approved';
        } else {
            // กรณีเป็นพนักงานทั่วไป: สืบค้นหาผู้ใช้ในแผนกและสาขาเดียวกัน ที่ถูกตั้งประเภทตำแหน่งเป็น "head"
            $approver = User::where('department', $user->department)
                            ->where('branch', $user->branch)
                            ->where('id', '!=', $user->id)
                            ->whereIn('position', function($query) {
                                $query->select('name')->from('job_titles')->where('position_type', 'head');
                            })->first();
            $approverId = $approver ? $approver->id : null;
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('welfare_attachments', 'public');
        }

        WelfareRequest::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'attachment' => $attachmentPath,
            'status' => $status,
            'approver_id' => $approverId, // บันทึก ID เป้าหมายของหัวหน้าที่จะเป็นผู้มีสิทธิ์ตรวจใบนี้
            'approved_by' => ($status == 'approved') ? $user->id : null,
            'approved_at' => ($status == 'approved') ? now() : null,
        ]);

        $message = $status == 'approved' ? 'อนุมัติอัตโนมัติเรียบร้อย' : 'ส่งคำขอเรียบร้อยแล้ว';
        return redirect()->route('welfare.history')->with('success', $message);
    }

    public function approve($id)
    {
        $welfare = WelfareRequest::findOrFail($id);
        // แก้ไข: เพิ่ม 'ผู้จัดการกลุ่มงาน' เข้าไป
        $approverPositions = ['ประธานเจ้าหน้าที่บริหาร', 'ประธานสายงาน', 'ผู้อำนวยการอาวุโสกลุ่มงาน', 'ผู้จัดการอาวุโสกลุ่มงาน', 'ผู้จัดการกลุ่มงาน', 'ผู้จัดการฝ่าย', 'ผู้ชำนาญการ'];
        
        // ตรวจสอบผ่านระบบสิทธิ์ใหม่
        $jobTitle = JobTitle::where('name', trim(Auth::user()->position))->first();
        $isHead = $jobTitle && $jobTitle->position_type === 'head';

        if (!$isHead && !in_array(Auth::user()->position, $approverPositions)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ในการอนุมัติ');
        }

        $welfare->update([
            'status' => 'approved',
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'อนุมัติเรียบร้อยแล้ว');
    }

    /**
     * ฟังก์ชันปฏิเสธรายการ: รับค่าจากช่อง 'admin_remark' และบันทึกลงคอลัมน์ 'remark'
     */
    public function rejectWithRemark(Request $request, $id)
    {
        // ตรวจสอบว่ามีข้อความส่งมาจาก textarea ชื่อ admin_remark หรือไม่
        $request->validate([
            'admin_remark' => 'required|string|max:500',
        ]);

        $welfare = WelfareRequest::findOrFail($id);
        // แก้ไข: เพิ่ม 'ผู้จัดการกลุ่มงาน' เข้าไป
        $approverPositions = ['ประธานเจ้าหน้าที่บริหาร', 'ประธานสายงาน', 'ผู้อำนวยการอาวุโสกลุ่มงาน', 'ผู้จัดการอาวุโสกลุ่มงาน', 'ผู้จัดการกลุ่มงาน', 'ผู้จัดการฝ่าย', 'ผู้ชำนาญการ'];
        
        // ตรวจสอบผ่านระบบสิทธิ์ใหม่
        $jobTitle = JobTitle::where('name', trim(Auth::user()->position))->first();
        $isHead = $jobTitle && $jobTitle->position_type === 'head';

        if (!$isHead && !in_array(Auth::user()->position, $approverPositions)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ในการดำเนินการนี้');
        }

        // อัปเดตข้อมูล: สำคัญมากคือต้องใช้คอลัมน์ 'remark' ให้ตรงกับ Database
        $welfare->update([
            'status' => 'rejected',
            'remark' => $request->admin_remark, 
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('welfare.approvals')->with('success', 'ปฏิเสธรายการและบันทึกเหตุผลเรียบร้อยแล้ว');
    }

    /**
     * ฟังก์ชัน reject (รองรับการเรียกใช้แบบเดิม)
     */
    public function reject(Request $request, $id)
    {
        return $this->rejectWithRemark($request, $id);
    }
}