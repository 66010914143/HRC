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

        // 2. ปรับปรุง: ดึงรายชื่อประธานเจ้าหน้าที่บริหารจากทุกสาขา เพื่อให้แสดงรายชื่อแน่นอน
        $ceos = User::where(function($q) {
                $q->where('position_level', 0)
                  ->orWhere('position_level', '0')
                  ->orWhere('position', 'LIKE', '%ประธานเจ้าหน้าที่บริหาร%')
                  ->orWhere('position', 'LIKE', '%CEO%');
            })
            ->where('id', '!=', $user->id)
            ->get();

        // 3. ดึงประวัติการขอเอกสารบันทึกภายในของคนล็อกอิน
        if ($user->role === 'admin') {
            $memos = InternalMemo::with(['user', 'approver1', 'approver2'])->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $memos = InternalMemo::with(['approver1', 'approver2'])->where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);
        }

        // ผูกตัวแปร $myMemos ให้ชี้ไปยังชุดข้อมูลประวัติเอกสาร เพื่อรองรับหน้า View
        $myMemos = $memos;

        // 4. ดึงรายการที่รอคนล็อกอินเข้ามารออนุมัติ
        $pendingApprovals = InternalMemo::with('user')
            ->where(function($query) use ($user) {
                // รอหัวหน้าแผนกอนุมัติขั้นแรก
                $query->where('approver_1_id', $user->id)
                      ->where('approver_1_status', 'pending');
            })
            ->orWhere(function($query) use ($user) {
                // รอ CEO อนุมัติขั้นที่สอง
                $query->where('approver_2_id', $user->id)
                      ->where('approver_2_status', 'pending')
                      ->where(function($sub) {
                          $sub->where('approver_1_status', 'approved')
                              ->orWhereNull('approver_1_id');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('internal_memo.index', compact('departmentHeads', 'ceos', 'memos', 'myMemos', 'pendingApprovals'));
    }

    /**
     * แก้ไข: ฟังก์ชันแสดงหน้าสร้างฟอร์ม (จำแนกสิทธิ์ พนักงานทั่วไป / หัวหน้าแผนก)
     */
    public function create()
    {
        // โหลดข้อมูลความสัมพันธ์ของตำแหน่งงาน โดยจับคู่ฟิลด์ข้อความตรงๆ
        $user = Auth::user()->load('jobTitle');

        // ดึงรายชื่อหัวหน้าแผนก/ฝ่าย
        $departmentHeads = User::where('branch', $user->branch)
            ->where('department', $user->department)
            ->where('id', '!=', $user->id)
            ->get();

        // 🎯 ปรับปรุง: ดึงรายชื่อประธานเจ้าหน้าที่บริหารจากทุกสาขา เพื่อแก้ปัญหารายชื่อไม่ขึ้น
        $ceos = User::where(function($q) {
                $q->where('position_level', 0)
                  ->orWhere('position_level', '0')
                  ->orWhere('position', 'LIKE', '%ประธานเจ้าหน้าที่บริหาร%')
                  ->orWhere('position', 'LIKE', '%CEO%');
            })
            ->where('id', '!=', $user->id)
            ->get();

        // 🎯 ตรวจสอบสิทธิ์ CEO / หัวหน้าแผนก
        $isCeo = ($user->role === 'ceo' || (isset($user->position) && (Str::contains(strtoupper($user->position), 'CEO') || Str::contains($user->position, 'ประธานเจ้าหน้าที่บริหาร'))));
        $isHead = ($user->jobTitle && $user->jobTitle->position_type === 'head');
        $isStaff = !$isHead && !$isCeo;

        return view('internal_memo.create', compact('user', 'departmentHeads', 'ceos', 'isStaff'));
    }

    /**
     * ฟังก์ชันบันทึกข้อมูลคำขอใบบันทึกภายใน
     */
    public function store(Request $request)
    {
        // โหลดข้อมูลความสัมพันธ์ของตำแหน่งงาน
        $user = Auth::user()->load('jobTitle');

        // 🎯 เช็คสิทธิ์เด็ดขาด: เป็น CEO หรือไม่ (ตรวจจาก role และ ชื่อตำแหน่งงานตรงๆ)
        $isCeo = ($user->role === 'ceo' || ($user->jobTitle && $user->jobTitle->position_level == 0) || (isset($user->position) && (Str::contains(strtoupper($user->position), 'CEO') || Str::contains($user->position, 'ประธานเจ้าหน้าที่บริหาร'))));
        $isHead = ($user->jobTitle && $user->jobTitle->position_type === 'head');
        $isStaff = !$isHead && !$isCeo;

        // ตั้งค่ากฎ Validation ตามเงื่อนไขของสิทธิ์ผู้ใช้งาน
        if ($isCeo || $isHead) {
            // หากเป็น CEO หรือระดับหัวหน้างาน: บันทึกลงระบบเลย ไม่ต้องตรวจสอบฟิลด์ผู้อนุมัติ
            $approver1Rule = 'nullable';
            $approver2Rule = 'nullable';
            $approvalTypeRule = 'nullable';
        } elseif ($isStaff) {
            // พนักงานทั่วไป: เลือกได้ 1 หรือ 2 คน (บังคับหัวหน้าแผนกก่อนเสมอ ส่วน CEO บังคับเฉพาะเมื่อเลือกแบบ 2 ขั้นตอน)
            $approver1Rule = 'required|exists:users,id';
            $approver2Rule = $request->approval_type == '2' ? 'required|exists:users,id' : 'nullable|exists:users,id';
            $approvalTypeRule = 'required|in:1,2';
        } else {
            $approver1Rule = 'nullable|exists:users,id';
            $approver2Rule = 'required|exists:users,id';
            $approvalTypeRule = 'required|in:1,2';
        }

        // ตรวจสอบความถูกต้องของข้อมูลก่อนบันทึก
        $request->validate([
            'subject' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'approval_type' => $approvalTypeRule,
            'approver_1_id' => $approver1Rule,
            'approver_2_id' => $approver2Rule,
            'files.*' => 'nullable|file|max:10240'
        ]);

        // รันเลขที่เอกสารอัตโนมัติ
        do {
            $memoNumber = 'MEMO-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (InternalMemo::where('memo_number', $memoNumber)->exists());

        // กำหนดค่าเริ่มต้นตามสถานะผู้ใช้
        if ($isCeo || $isHead) {
            // ✨ หากเป็น CEO หรือ หัวหน้างาน: อนุมัติอัตโนมัติทันทีเพื่อเก็บประวัติลงระบบ ให้ Admin ตรวจสอบได้หน้าบ้าน โดยไม่ต้องเลือกส่งหาใคร
            $approvalType = 1;
            $approver1Id = null;
            $approver2Id = null;
            $approver1Status = 'approved';
            $approver2Status = 'approved';
            $documentStatus = 'approved';
        } else {
            // พนักงานทั่วไป ใช้ Logic ปกติเดิม
            $approvalType = $request->approval_type;
            $approver1Id = $request->approver_1_id;
            $approver2Id = $request->approver_2_id;

            if ($approvalType == 1) {
                $approver2Id = null;
            }

            $approver1Status = $approver1Id ? 'pending' : 'approved';
            $approver2Status = $approver2Id ? 'pending' : null;
            $documentStatus = 'pending';
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
            'approver_1_status' => $approver1Status,
            'approver_2_id' => $approver2Id,
            'approver_2_status' => $approver2Status,
            'status' => $documentStatus
        ]);

        // 2. บันทึกไฟล์แนบอัปโหลด (ถ้ามี)
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

        $msg = ($isCeo || $isHead)
            ? 'บันทึกเอกสารบันทึกภายในเลขที่ ' . $memoNumber . ' เรียบร้อยแล้ว (อนุมัติอัตโนมัติเพื่อเก็บประวัติในระบบ)' 
            : 'สร้างเอกสารบันทึกภายในเลขที่ ' . $memoNumber . ' เรียบร้อยแล้ว';

        return redirect()->back()->with('success', $msg);
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

        $reasonField = $request->has('reject_reason') ? 'reject_reason' : 'reject_comment';

        $request->validate([
            $reasonField => 'required|string|max:1000'
        ]);

        $reasonText = $request->input($reasonField);

        if ($memo->approver_1_id == $user->id && $memo->approver_1_status == 'pending') {
            $memo->approver_1_status = 'rejected';
            $memo->status = 'rejected';
            
            if (\Schema::hasColumn('internal_memos', 'reject_comment')) {
                $memo->reject_comment = $reasonText;
            }
            if (\Schema::hasColumn('internal_memos', 'reject_reason')) {
                $memo->reject_reason = $reasonText;
            }
        } elseif ($memo->approver_2_id == $user->id && $memo->approver_2_status == 'pending') {
            $memo->approver_2_status = 'rejected';
            $memo->status = 'rejected';
            
            if (\Schema::hasColumn('internal_memos', 'reject_comment')) {
                $memo->reject_comment = $reasonText;
            }
            if (\Schema::hasColumn('internal_memos', 'reject_reason')) {
                $memo->reject_reason = $reasonText;
            }
        } else {
            return back()->with('error', 'คุณไม่มีสิทธิ์ปฏิเสธเอกสารฉบับนี้ หรือสถานะไม่ถูกต้อง');
        }

        $memo->save();
        return back()->with('success', 'ปฏิเสธเอกสารเรียบร้อยแล้ว');
    }

    /**
     * หน้าศูนย์รวมรายการคำขออนุมัติ
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

        $pendingMemos = $pendingApprovals;

        return view('internal_memo.approvals', compact('pendingApprovals', 'pendingMemos'));
    }

    /**
     * ดำเนินการ อนุมัติ/ปฏิเสธ จากหน้าศูนย์รวม
     */
    public function approveAction(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reject_comment' => $request->status === 'rejected' ? 'required|string|max:500' : 'nullable|string|max:500'
        ]);

        if ($request->status === 'approved') {
            return $this->approve($id);
        }

        return $this->reject($request, $id);
    }

    /**
     * แสดงหน้าจอรายละเอียดเอกสารเดี่ยว
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $relations = ['user', 'approver1', 'approver2'];
        if (method_exists(InternalMemo::class, 'attachments')) {
            $relations[] = 'attachments';
        } elseif (method_exists(InternalMemo::class, 'files')) {
            $relations[] = 'files';
        }

        if ($user->role === 'admin') {
            $memo = InternalMemo::with($relations)->findOrFail($id);
        } else {
            $memo = InternalMemo::with($relations)
                ->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('approver_1_id', $user->id)
                      ->orWhere('approver_2_id', $user->id);
                })
                ->findOrFail($id);
        }

        if (!$memo->user) {
            $memo->setRelation('user', new class { public $name = 'ไม่พบข้อมูลพนักงาน (หรือบัญชีถูกลบ)'; });
        }

        $memo->attachments = method_exists(InternalMemo::class, 'attachments') ? $memo->attachments : ($memo->files ?? collect());

        return view('internal_memo.show', compact('memo'));
    }

    /**
     * ส่งข้อมูล JSON สำหรับป๊อปอัปหน้าบ้าน
     */
    public function showJson($id)
    {
        try {
            $memo = InternalMemo::with(['user', 'approver1', 'approver2', 'attachments'])->findOrFail($id);
            $memo->files = $memo->attachments;

            return response()->json([
                'success' => true,
                'data' => $memo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}