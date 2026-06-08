<?php

namespace App\Http\Controllers;

use App\Models\InternalMemo;
use App\Models\InternalMemoFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InternalMemoController extends Controller
{
    /**
     * หน้าแรก: แสดงฟอร์มขอเอกสารและประวัติรายการ
     */
    public function index()
    {
        $user = Auth::user();

        // 1. ดึงรายชื่อหัวหน้าแผนก/ฝ่าย (ที่อยู่สาขาและแผนกเดียวกัน) สำหรับพนักงานทั่วไปเลือกส่ง
        $departmentHeads = User::where('branch', $user->branch)
            ->where('department', $user->department)
            ->where('id', '!=', $user->id)
            ->get();

        // 2. ดึงรายชื่อประธานเจ้าหน้าที่บริหาร (Level 0) ที่อยู่สาขาเดียวกัน
        $ceos = User::where('branch', $user->branch)
            ->where(function($q) {
                $q->where('position_level', 0)
                  ->orWhere('position_level', '0')
                  ->orWhere('position', 'LIKE', '%ประธานเจ้าหน้าที่บริหาร%');
            })
            ->where('id', '!=', $user->id)
            ->get();

        // 3. ดึงประวัติการขอเอกสารบันทึกภายในของคนล็อกอิน (พนักงานทั่วไปเห็นเฉพาะของตัวเอง, Admin เห็นทุกคน)
        if ($user->role === 'admin') {
            $memos = InternalMemo::with(['user', 'approver1', 'approver2'])->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $memos = InternalMemo::with(['approver1', 'approver2'])->where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);
        }

        // 🔥 ผูกตัวแปร $myMemos ให้ชี้ไปยังชุดข้อมูลประวัติเอกสาร ($memos) เพื่อรองรับหน้า View
        $myMemos = $memos;

        // 4. ดึงรายการที่รอคนล็อกอินเข้ามารออนุมัติ (ทั้งในฐานะผู้อนุมัติคนแรก หรือคนที่สอง)
        $pendingApprovals = InternalMemo::with('user')
            ->where(function($query) use ($user) {
                // รอหัวหน้าแผนกอนุมัติขั้นแรก
                $query->where('approver_1_id', $user->id)
                      ->where('approver_1_status', 'pending');
            })
            ->orWhere(function($query) use ($user) {
                // รอ CEO อนุมัติขั้นที่สอง (หลังจากหัวหน้าแผนกอนุมัติผ่านแล้ว หรือกรณีหัวหน้าแผนกส่งตรงหา CEO)
                $query->where('approver_2_id', $user->id)
                      ->where('approver_2_status', 'pending')
                      ->where(function($sub) {
                          $sub->where('approver_1_status', 'approved')
                              ->orWhereNull('approver_1_id'); // กรณีหัวหน้าขอเอง จะไม่มีผู้อนุมัติคนที่ 1
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('internal_memo.index', compact('departmentHeads', 'ceos', 'memos', 'myMemos', 'pendingApprovals'));
    }

    /**
     * แก้ไข: ฟังก์ชันแสดงหน้าสำหรับกรอกใบคำขอบันทึกภายใน (แก้เงื่อนไขเช็ค พนักงานทั่วไป/หัวหน้างาน)
     */
    public function create()
    {
        $user = Auth::user();

        // ดึงรายชื่อหัวหน้าแผนก/ฝ่าย (ที่อยู่สาขาและแผนกเดียวกัน)
        $departmentHeads = User::where('branch', $user->branch)
            ->where('department', $user->department)
            ->where('id', '!=', $user->id)
            ->get();

        // ดึงรายชื่อประธานเจ้าหน้าที่บริหาร (Level 0)
        // เอาเงื่อนไขเรื่องแผนกออกเพื่อให้เรียกข้ามสายงานเข้าหา CEO (level 0) ทุกคนได้
        $ceos = User::where(function($q) {
                $q->where('position_level', 0)
                  ->orWhere('position_level', '0')
                  ->orWhere('position', 'LIKE', '%ประธานเจ้าหน้าที่บริหาร%');
            })
            ->where('id', '!=', $user->id)
            ->get();

        // 🔥 ตรวจสอบสิทธิ์ระบบ: ถ้า level เป็น 'general' และในตำแหน่งไม่มีคำว่า LEVEL 3/ผู้จัดการ/หัวหน้า/กลุ่มงาน จะถือว่าเป็นพนักงานทั่วไป
        $isStaff = ($user->level === 'general' && 
                    !str_contains($user->position, 'LEVEL 3') && 
                    !str_contains($user->position, 'ผู้จัดการ') && 
                    !str_contains($user->position, 'หัวหน้า') &&
                    !str_contains($user->position, 'กลุ่มงาน'));

        return view('internal_memo.create', compact('user', 'departmentHeads', 'ceos', 'isStaff'));
    }

    /**
     * ฟังก์ชันบันทึกข้อมูลคำขอใบบันทึกภายใน
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 🔥 เช็กระดับผู้ใช้งานเพื่อนำมาแยกแยะสิทธิ์ในการคำนวณเงื่อนไข Validation
        $isStaff = ($user->level === 'general' && 
                    !str_contains($user->position, 'LEVEL 3') && 
                    !str_contains($user->position, 'ผู้จัดการ') && 
                    !str_contains($user->position, 'หัวหน้า') &&
                    !str_contains($user->position, 'กลุ่มงาน'));

        // ตั้งค่า Rule แบบไดนามิกตามเงื่อนไขที่ปรับปรุงใหม่
        if ($isStaff) {
            // พนักงานทั่วไป: บังคับเลือกหัวหน้าแผนก (approver_1) เสมอ ส่วน CEO (approver_2) บังคับเฉพาะเมื่อเลือกแบบ 2 ขั้นตอน
            $approver1Rule = 'required|exists:users,id';
            $approver2Rule = $request->approval_type == '2' ? 'required|exists:users,id' : 'nullable|exists:users,id';
        } else {
            // ระดับหัวหน้างานขึ้นไป: ส่งตรงหา CEO เท่านั้น (approver_1 เป็นโมฆะ/เว้นว่างได้, approver_2 บังคับกรอก)
            $approver1Rule = 'nullable|exists:users,id';
            $approver2Rule = 'required|exists:users,id';
        }

        // ตรวจสอบความถูกต้องของข้อมูลก่อนบันทึก
        $request->validate([
            'subject' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'approval_type' => 'required|in:1,2',
            'approver_1_id' => $approver1Rule,
            'approver_2_id' => $approver2Rule,
            'files.*' => 'nullable|file|max:10240' // จำกัดขนาดไฟล์ละไม่เกิน 10MB
        ]);

        // 🚀 ระบบสุ่มเลขที่เอกสารอัตโนมัติและตรวจสอบไม่ให้ซ้ำในระบบ
        do {
            $memoNumber = 'MEMO-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (InternalMemo::where('memo_number', $memoNumber)->exists());

        $approvalType = $request->approval_type;
        $approver1Id = $request->approver_1_id;
        $approver2Id = $request->approver_2_id;

        // จัดการและเคลียร์ค่า ID ตามสิทธิ์ผู้ใช้งานจริงก่อนลง Database
        if (!$isStaff) { 
            // หัวหน้างานบังคับวิ่งเข้า CEO โดยตรง (ข้ามผู้อนุมัติคนแรก)
            $approvalType = 1;
            $approver1Id = null; 
            $approver2Id = $request->approver_2_id; 
        } else {
            // พนักงานทั่วไปแต่เลือกแบบ 1 คน (ส่งหาหัวหน้าแผนกอย่างเดียว) ให้เคลียร์ค่าผู้อนุมัติคนที่สองออก
            if ($approvalType == 1) {
                $approver2Id = null;
            }
        }

        // 1. บันทึกข้อมูลเอกสารหลักลงฐานข้อมูล
        $memo = InternalMemo::create([
            'user_id' => $user->id,
            'branch' => $user->branch ?? 'สำนักงานใหญ่',
            'department' => $user->department ?? 'ทั่วไป',
            'memo_number' => $memoNumber,
            'request_date' => now()->toDateString(),
            'subject' => $request->subject,
            'amount' => $request->amount,
            'approval_type' => $approvalType,
            'approver_1_id' => $approver1Id,
            'approver_1_status' => $approver1Id ? 'pending' : 'approved', // ถ้าไม่มีผู้อนุมัติคนแรกให้ถือว่า Approved เลยเพื่อข้ามไปหาคนสอง
            'approver_2_id' => $approver2Id,
            'approver_2_status' => $approver2Id ? 'pending' : 'pending',
            'status' => 'pending'
        ]);

        // 2. จัดการบันทึกไฟล์แนบอัปโหลด
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $path = $file->store('public/internal_memos');

                    InternalMemoFile::create([
                        'internal_memo_id' => $memo->id,
                        'file_path' => Storage::url($path),
                        'file_name' => $originalName
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'สร้างเอกสารบันทึกภายในเลขที่ ' . $memoNumber . ' เรียบร้อยแล้ว');
    }

    /**
     * ฟังก์ชันการอนุมัติเอกสาร
     */
    public function approve($id)
    {
        $user = Auth::user();
        $memo = InternalMemo::findOrFail($id);

        if ($memo->approver_1_id == $user->id && $memo->approver_1_status == 'pending') {
            $memo->approver_1_status = 'approved';
            // ปรับตรรกะ: หากเป็นประเภทอนุมัติ 1 ขั้นตอน หรือไม่ได้เลือกผู้อนุมัติคนที่สองไว้ ให้เปลี่ยนสถานะเอกสารเป็นอนุมัติสมบูรณ์ (approved) ทันที
            if ($memo->approval_type == 1 || !$memo->approver_2_id) {
                $memo->status = 'approved';
            }
        } 
        elseif ($memo->approver_2_id == $user->id && $memo->approver_2_status == 'pending') {
            if (null === $memo->approver_1_id || $memo->approver_1_status == 'approved') {
                $memo->approver_2_status = 'approved';
                $memo->status = 'approved';
            } else {
                return back()->with('error', 'เอกสารนี้ยังไม่ได้รับการอนุมัติจากหัวหน้าแผนกในขั้นแรก');
            }
        } else {
            return back()->with('error', 'คุณไม่มีสิทธิ์อนุมัติเอกสารฉบับนี้ หรือสถานะไม่ถูกต้อง');
        }

        $memo->save();
        return back()->with('success', 'อนุมัติเอกสารบันทึกภายในเรียบร้อยแล้ว');
    }

    /**
     * ฟังก์ชันปฏิเสธเอกสารพร้อมระบุสาเหตุ
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $memo = InternalMemo::findOrFail($id);

        $request->validate([
            'reject_comment' => 'required|string|max:500'
        ]);

        if ($memo->approver_1_id == $user->id && $memo->approver_1_status == 'pending') {
            $memo->approver_1_status = 'rejected';
            $memo->status = 'rejected';
            $memo->reject_comment = $request->reject_comment;
        } elseif ($memo->approver_2_id == $user->id && $memo->approver_2_status == 'pending') {
            $memo->approver_2_status = 'rejected';
            $memo->status = 'rejected';
            $memo->reject_comment = $request->reject_comment;
        } else {
            return back()->with('error', 'คุณไม่มีสิทธิ์ปฏิเสธเอกสารฉบับนี้ หรือสถานะไม่ถูกต้อง');
        }

        $memo->save();
        return back()->with('success', 'ปฏิเสธเอกสารเรียบร้อยแล้ว');
    }

    /**
     * แสดงหน้าศูนย์รวมรายการคำขออนุมัติใบบันทึกภายในสำหรับ หัวหน้าแผนก และ CEO
     */
    public function approvals()
    {
        $user = Auth::user();

        $pendingApprovals = InternalMemo::with('user')
            ->where(function($query) use ($user) {
                $query->where('approver_1_id', $user->id)
                      ->where('approver_1_status', 'pending');
            })
            ->orWhere(function($query) use ($user) {
                $query->where('approver_2_id', $user->id)
                      ->where('approver_2_status', 'pending')
                      ->where(function($sub) {
                          $sub->where('approver_1_status', 'approved')
                              ->orWhereNull('approver_1_id');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('internal_memo.approvals', compact('pendingApprovals'));
    }

    /**
     * รองรับการส่ง Action อนุมัติ/ปฏิเสธผ่านแบบฟอร์มในหน้าศูนย์อนุมัติรวม
     */
    public function approveAction(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reject_comment' => 'nullable|string|max:500'
        ]);

        if ($request->status === 'approved') {
            return $this->approve($id);
        }

        return $this->reject($request, $id);
    }
}