<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewPostNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request) // เพิ่ม Request เพื่อรับค่าการค้นหา
    {
        $user = Auth::user();
        
        // 1. เริ่มต้นสร้าง Query ของ Post
        $query = Post::latest();

        // 2. ตรวจสอบว่ามีการระบุวันที่ค้นหา (search_date) หรือไม่
        if ($request->filled('search_date')) {
            $query->whereDate('created_at', $request->search_date);
        }

        // ดึงโพสต์ทั้งหมดตามเงื่อนไข (วันที่) พร้อมความสัมพันธ์
        $allPosts = $query->get();

        $posts = $allPosts->filter(function ($post) use ($user) {
            // 1. Admin เห็นทุกโพสต์
            if ($user->role === 'admin') return true;

            // 2. เจ้าของโพสต์เห็นโพสต์ตัวเองเสมอ
            if ($post->user_id === $user->id) return true;

            // เตรียมค่าของ User และล้างช่องว่าง
            $userDept = trim($user->department);
            $userBranch = trim($user->branch);

            // เตรียมค่าจากโพสต์
            $targetDepts = $post->target_departments ?? [];
            $targetBranches = $post->target_branches ?? [];

            // 3. เช็คว่าเป็นโพสต์สาธารณะ (ไม่ระบุทั้งฝ่ายและสาขา)
            $noDept = empty($targetDepts);
            $noBranch = empty($targetBranches);
            if ($noDept && $noBranch) return true;

            // --- Logic เงื่อนไขแบบ "และ" (AND) แต่ใช้การเช็คคำบางส่วน (Partial Match) ---
            $matchDept = $noDept; 
            $matchBranch = $noBranch; 

            // ตรวจสอบฝ่าย: เช็คว่าชื่อฝ่าย User มีคำที่ระบุในโพสต์หรือไม่
            if (!$noDept && !empty($userDept)) {
                foreach ($targetDepts as $dept) {
                    $cleanTargetDept = trim($dept);
                    if (mb_strpos($userDept, $cleanTargetDept) !== false || mb_strpos($cleanTargetDept, $userDept) !== false) {
                        $matchDept = true;
                        break;
                    }
                }
            }

            // ตรวจสอบสาขา: เช็คว่าชื่อสาขา User มีคำที่ระบุในโพสต์หรือไม่
            if (!$noBranch && !empty($userBranch)) {
                foreach ($targetBranches as $branch) {
                    $cleanTargetBranch = trim($branch);
                    if (mb_strpos($userBranch, $cleanTargetBranch) !== false || mb_strpos($cleanTargetBranch, $userBranch) !== false) {
                        $matchBranch = true;
                        break;
                    }
                }
            }

            // ต้องตรงเงื่อนไขที่ระบุไว้ (ถ้าโพสต์ระบุทั้งคู่ ต้องผ่านทั้งคู่)
            return $matchDept && $matchBranch;
        });

        return view('dashboard', compact('posts'));
    }

    public function create()
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->position_level > 3) {
            return redirect()->route('dashboard')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้าสร้างประกาศ');
        }
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target_departments' => 'nullable|array',
            'target_branches'    => 'nullable|array',
            'document_file'      => 'nullable|mimes:doc,docx,xls,xlsx|max:10240', // เพิ่มการตรวจสอบไฟล์ Word, Excel ขนาดไม่เกิน 10MB
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        // ระบบจัดการอัปโหลดไฟล์เอกสาร (เก็บไว้ใน storage/app/public/documents)
        $documentPath = null;
        if ($request->hasFile('document_file')) {
            $documentPath = $request->file('document_file')->store('documents', 'public');
        }

        $cleanDepts = $request->target_departments ? array_map('trim', $request->target_departments) : [];
        $cleanBranches = $request->target_branches ? array_map('trim', $request->target_branches) : [];

        $post = Post::create([
            'user_id' => Auth::id(), 
            'title'   => $request->title,
            'content' => $request->content,
            'image'   => $imagePath,
            'target_departments' => $cleanDepts, 
            'target_branches'    => $cleanBranches,
            'document_file'      => $documentPath, // บันทึกพาธไฟล์เอกสารลงฐานข้อมูล
        ]);

        $targetUsers = User::where('id', '!=', Auth::id())->get()->filter(function($u) use ($cleanDepts, $cleanBranches) {
            $uDept = trim($u->department);
            $uBranch = trim($u->branch);
            
            $matchDept = empty($cleanDepts);
            $matchBranch = empty($cleanBranches);

            if (!$matchDept && !empty($uDept)) {
                foreach ($cleanDepts as $d) {
                    if (mb_strpos($uDept, trim($d)) !== false || mb_strpos(trim($d), $uDept) !== false) {
                        $matchDept = true;
                        break;
                    }
                }
            }
            
            if (!$matchBranch && !empty($uBranch)) {
                foreach ($cleanBranches as $b) {
                    if (mb_strpos($uBranch, trim($b)) !== false || mb_strpos(trim($b), $uBranch) !== false) {
                        $matchBranch = true;
                        break;
                    }
                }
            }
            
            return $matchDept && $matchBranch;
        });

        if ($targetUsers->count() > 0) {
            Notification::send($targetUsers, new NewPostNotification($post, Auth::user()->name));
        }

        return redirect()->route('dashboard')->with('success', 'ประกาศเรียบร้อยแล้ว');
    }

    public function destroy(Post $post)
    {
        if (Auth::user()->id !== $post->user_id && Auth::user()->role !== 'admin') {
            return back()->with('error', 'คุณไม่มีสิทธิ์ลบโพสต์นี้');
        }
        if ($post->image) { Storage::disk('public')->delete($post->image); }
        
        // เพิ่มระบบลบไฟล์เอกสารออกจาก Storage เมื่อโพสต์ถูกลบ
        if ($post->document_file) { Storage::disk('public')->delete($post->document_file); }

        $post->delete();
        return back()->with('success', 'ลบประกาศเรียบร้อยแล้ว');
    }
}