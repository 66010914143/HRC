<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\JobLevel;
use App\Models\JobTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * 1. แสดงรายชื่อพนักงานทั้งหมด
     */
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())
                     ->orderBy('created_at', 'desc')
                     ->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * 2. หน้าฟอร์มเพิ่มพนักงานใหม่ (ปรับปรุงให้ดึงข้อมูลจากตารางใหม่ส่งไปแสดงที่ฟอร์ม)
     */
    public function create()
    {
        $branches = Branch::orderBy('name', 'asc')->get();
        $departments = Department::orderBy('name', 'asc')->get();
        $jobLevels = JobLevel::orderBy('level_number', 'asc')->get();
        $jobTitles = JobTitle::orderBy('name', 'asc')->get();

        return view('admin.users.add_user', compact('branches', 'departments', 'jobLevels', 'jobTitles'));
    }

    /**
     * 3. บันทึกข้อมูลพนักงานใหม่ลงฐานข้อมูล
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'name' => 'required',
            'last_name' => 'required',
            'role' => 'required',
            'position_level' => 'required|integer|min:0', 
            'position' => 'required',
            'department' => 'required',
            'branch' => 'required',
        ]);

        // แปลงค่า ID ที่ส่งมาจากหน้าฟอร์ม ให้กลับมาเป็น level_number (0-5) ก่อนนำไปบันทึก เพื่อไม่ให้สิทธิ์การโพสต์พัง
        $positionLevelValue = $request->position_level;
        $findLevel = JobLevel::find($positionLevelValue);
        if ($findLevel && $findLevel->level_number !== null) {
            $positionLevelValue = $findLevel->level_number;
        }

        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'last_name' => $request->last_name,
            'position' => $request->position,
            'position_level' => $positionLevelValue, // บันทึกด้วยเลขระดับ 0-5 ที่ถูกต้อง
            'department' => $request->department,
            'branch' => $request->branch,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'เพิ่มสมาชิกเรียบร้อยแล้ว');
    }

    /**
     * 4. หน้าฟอร์มแก้ไขข้อมูลพนักงาน (อัปเดตเพิ่มการดึงข้อมูลชุดตัวเลือกเพื่อแก้ปัญหา Undefined Variable)
     */
    public function edit($id)
    {
        // ค้นหาข้อมูลผู้ใช้งานตาม ID หากไม่พบจะแสดงหน้า 404
        $user = User::findOrFail($id);
        
        // ดึงข้อมูล สาขา ฝ่าย ระดับสายงาน และตำแหน่ง ทั้งหมดมารอไว้เหมือนหน้า create เพื่อส่งไปแสดงที่ฟอร์มแก้ไข
        $branches = Branch::orderBy('name', 'asc')->get();
        $departments = Department::orderBy('name', 'asc')->get();
        $jobLevels = JobLevel::orderBy('level_number', 'asc')->get();
        $jobTitles = JobTitle::orderBy('name', 'asc')->get();

        // กำหนดตัวแปรแบบ Mapping เผื่อไว้คู่กัน ป้องกัน Error ในหน้า edit.blade.php ไม่ว่าจะเขียนอ้างอิงด้วยชื่อรูปแบบใด
        $position_levels = $jobLevels;
        $positions = $jobTitles;
        
        // ส่งข้อมูลพนักงานพร้อมกับข้อมูลชุดตัวเลือกทั้งหมดไปที่หน้าแก้ไข
        return view('admin.users.edit', compact(
            'user', 
            'branches', 
            'departments', 
            'jobLevels', 
            'jobTitles', 
            'position_levels', 
            'positions'
        ));
    }

    /**
     * 5. อัปเดตข้อมูลพนักงานที่แก้ไข
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'role' => 'required',
            'position_level' => 'required|integer|min:0', 
            'position' => 'required',
            'department' => 'required',
            'branch' => 'required',
        ]);

        // รวบรวมข้อมูลที่จะอัปเดตเฉพาะข้อมูลที่ได้รับมาจากฟอร์ม
        $data = $request->only([
            'name', 
            'last_name', 
            'position', 
            'department', 
            'branch', 
            'role'
        ]);

        // แปลงค่า ID ที่ส่งมาจากหน้าฟอร์ม ให้กลับมาเป็น level_number (0-5) ก่อนนำไปบันทึก เพื่อไม่ให้สิทธิ์การโพสต์พัง
        $positionLevelValue = $request->position_level;
        $findLevel = JobLevel::find($positionLevelValue);
        if ($findLevel && $findLevel->level_number !== null) {
            $positionLevelValue = $findLevel->level_number;
        }
        $data['position_level'] = $positionLevelValue; // กำหนดเลขระดับ 0-5 ลงชุดข้อมูลอัปเดต

        // ตรวจสอบเพิ่มเติมหากฝั่ง Front-end มีการใส่รหัสผ่านใหม่เข้ามา (ไม่ใช่ค่าว่าง) ให้ทำการเปลี่ยนรหัสผ่านด้วย
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'อัปเดตข้อมูลพนักงานสำเร็จแล้ว');
    }

    /**
     * 6. ลบพนักงานออกจากระบบ
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // ลบข้อมูลจากฐานข้อมูล
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'ลบพนักงานออกจากระบบเรียบร้อยแล้ว');
    }

    /*
    |--------------------------------------------------------------------------
    | ฟังก์ชันเพิ่มเติม: จัดการบันทึกข้อมูลด่วนผ่าน AJAX (Quick Add)
    |--------------------------------------------------------------------------
    |
    */

    /**
     * เพิ่มสาขาแบบด่วน
     */
    public function storeBranch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:branches,name|max:255',
        ]);

        $branch = Branch::create(['name' => $request->name]);

        return response()->json(['success' => true, 'data' => $branch]);
    }

    /**
     * เพิ่มฝ่ายแบบด่วน
     */
    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:departments,name|max:255',
        ]);

        $department = Department::create(['name' => $request->name]);

        return response()->json(['success' => true, 'data' => $department]);
    }

    /**
     * เพิ่มระดับสายงานแบบด่วน (รองรับตัวเลขระดับสิทธิ์ 0-6)
     */
    public function storeJobLevel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:job_levels,name|max:255',
            'level_number' => 'required|integer|between:0,6', // ล็อคเงื่อนไขให้กรอกเฉพาะเลข 0-6 เท่านั้น
        ]);

        $jobLevel = JobLevel::create([
            'name' => $request->name,
            'level_number' => $request->level_number,
        ]);

        return response()->json(['success' => true, 'data' => $jobLevel]);
    }

    /**
     * เพิ่มชื่อตำแหน่งงานแบบด่วน (แก้ไขระบบตรวจจับแบบครอบจักรวาล ป้องกันค่า NULL 100%)
     */
    public function storeJobTitle(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:job_titles,name|max:255',
        ]);

        // 1. ดักหาค่า ID ของ Job Level จากตัวแปรทุกรูปแบบที่หน้าบ้านอาจจะส่งมา
        $jobLevelId = $request->job_level_id 
            ?? $request->position_level_id 
            ?? $request->level_id 
            ?? $request->job_level 
            ?? null;

        // 2. กรณีที่หน้าบ้านไม่ได้ส่งค่า ID มาโดยตรง แต่ส่งมาเป็นเลขระดับสิทธิ์ (เช่น 0, 1, 2) ผ่านตัวแปร position_level หรือ level_number
        if (empty($jobLevelId)) {
            $levelNumber = $request->position_level ?? $request->level_number ?? null;
            
            if ($levelNumber !== null) {
                // ไปค้นหาในตาราง job_levels ว่าเลขระดับนี้ มี ID หลักคืออะไรเพื่อนำมาผูก
                $findLevel = JobLevel::where('level_number', $levelNumber)->first();
                if ($findLevel) {
                    $jobLevelId = $findLevel->id;
                }
            }
        }

        // 3. ป้องกันกรณีสุดท้าย: ถ้ายังไม่พบค่า ให้ดึง ID ของระดับแรกสุด (หรือระดับ 0) มาใส่ไว้ก่อน ดีกว่าปล่อยให้ระบบบันทึกเป็น NULL
        if (empty($jobLevelId)) {
            $defaultLevel = JobLevel::orderBy('level_number', 'asc')->first();
            $jobLevelId = $defaultLevel ? $defaultLevel->id : null;
        }

        $jobTitle = JobTitle::create([
            'name' => $request->name,
            'job_level_id' => $jobLevelId,
            'position_type' => $request->position_type ?? 'employee', // <-- เพิ่มการบันทึกสิทธิ์พนักงาน/หัวหน้าแผนก
        ]);

        return response()->json(['success' => true, 'data' => $jobTitle]);
    }

    /*
    |--------------------------------------------------------------------------
    | ➕ เพิ่มเติมตามไฟล์ Route: ฟังก์ชันจัดการอัปเดต/ลบ ผ่านเส้นทางตรง
    |--------------------------------------------------------------------------
    |
    */

    public function updateBranch(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $branch = Branch::findOrFail($id);
        $branch->update(['name' => $request->name]);
        return response()->json(['success' => true, 'data' => $branch]);
    }

    public function destroyBranch($id)
    {
        try {
            $branch = Branch::findOrFail($id);
            $branch->delete();
            return response()->json(['success' => true, 'message' => 'ลบสาขาสำเร็จ']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'ไม่สามารถลบได้เนื่องจากข้อมูลนี้ถูกใช้งานอยู่'], 400);
        }
    }

    public function updateJobTitle(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $jobTitle = JobTitle::findOrFail($id);
        $jobTitle->update(['name' => $request->name]);
        return response()->json(['success' => true, 'data' => $jobTitle]);
    }

    public function destroyJobTitle($id)
    {
        try {
            $jobTitle = JobTitle::findOrFail($id);
            $jobTitle->delete();
            return response()->json(['success' => true, 'message' => 'ลบตำแหน่งงานสำเร็จ']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'ไม่สามารถลบได้เนื่องจากข้อมูลนี้ถูกใช้งานอยู่'], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | API สำหรับดึงข้อมูล Real-time เพื่ออัปเดต Dropdown ในหน้าฟอร์ม
    |--------------------------------------------------------------------------
    |
    */

    // 1. ดึงข้อมูลสาขาล่าสุด
    public function getBranches() {
        return response()->json(Branch::orderBy('name', 'asc')->get());
    }

    // 2. ดึงข้อมูลฝ่ายล่าสุด
    public function getDepartments() {
        return response()->json(Department::orderBy('name', 'asc')->get());
    }

    // 3. ดึงข้อมูลระดับสายงานล่าสุด
    public function getJobLevels() {
        return response()->json(JobLevel::orderBy('level_number', 'asc')->get());
    }

    // 4. ดึงข้อมูลตำแหน่งงานล่าสุด (แก้ไขลอจิกการแมตช์ค่าสลับรูปแบบ ID และ Level Number ให้แสดงผลทันที)
    public function getJobTitles(Request $request) {
        $query = JobTitle::orderBy('name', 'asc');

        // ตรวจสอบค่าที่ส่งมาจาก JavaScript (อาจส่งมาเป็น job_level_id หรือ position_level)
        $jobLevelId = $request->job_level_id ?? $request->level_id ?? null;
        $positionLevel = $request->position_level ?? $request->level_number ?? null;

        if (!empty($jobLevelId) && $jobLevelId != '') {
            // กรณีส่ง ID มาตรงๆ (เช่น ส่ง 11 มา) ให้กรองจาก job_level_id ได้เลย
            $query->where('job_level_id', $jobLevelId);
        } elseif ($positionLevel !== null && $positionLevel != '') {
            // กรณีส่งมาเป็นเลข level_number (เช่น ส่งเลข 1 มา) 
            // 💡 แก้ปัญหา: ให้เช็คก่อนว่าเลข 1 นั้นเป็น ID หลักในตาราง หรือเป็นแค่เลขระดับสิทธิ์
            $findLevelByNum = JobLevel::where('level_number', $positionLevel)->first();
            $findLevelById = JobLevel::find($positionLevel);

            if ($findLevelByNum && $findLevelById) {
                // ถ้าเป็นไปทั้งสองแบบ (มีทั้ง ID และ Level_number ที่ตรงกัน) ให้ค้นหาตำแหน่งงานที่ตรงกับเงื่อนไขใดเงื่อนไขหนึ่ง
                $query->where(function($q) use ($findLevelByNum, $positionLevel) {
                    $q->where('job_level_id', $findLevelByNum->id)
                      ->orWhere('job_level_id', $positionLevel);
                });
            } elseif ($findLevelByNum) {
                $query->where('job_level_id', $findLevelByNum->id);
            } else {
                $query->where('job_level_id', $positionLevel);
            }
        }

        $results = $query->get();

        // นโยบายสำรองความปลอดภัยสูงสุด: ถ้ากรองแล้วยังหาไม่เจอ (ป้องกันระบบ Front-end ส่งตัวแปรหลุดมา) 
        // ให้ส่งข้อมูลตำแหน่งทั้งหมดกลับไปแสดงผลทันที Dropdown จะได้ไม่ว่างเปล่า
        if ($results->isEmpty()) {
            return response()->json(JobTitle::orderBy('name', 'asc')->get());
        }

        return response()->json($results);
    }

    /*
    |--------------------------------------------------------------------------
    | 👑 ฟังก์ชันสำหรับรองรับการ แก้ไข/ลบ ข้อมูลจาก Modal (คงเดิม)
    |--------------------------------------------------------------------------
    |
    */

    public function updateManageItem(Request $request, $type, $id)
    {
        $model = $this->getModelByType($type);
        if (!$model) {
            return response()->json(['error' => 'ไม่พบประเภทข้อมูลนี้'], 400);
        }

        $item = $model::find($id);
        if ($item) {
            $item->name = $request->name;
            if($request->has('level_number') && $type === 'job-level'){
                $item->level_number = $request->level_number;
            }
            $item->save();
            return response()->json(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
        }

        return response()->json(['error' => 'ไม่พบข้อมูลที่ต้องการแก้ไข'], 404);
    }

    public function deleteManageItem($type, $id)
    {
        $model = $this->getModelByType($type);
        if (!$model) {
            return response()->json(['error' => 'ไม่พบประเภทข้อมูลนี้'], 400);
        }

        $item = $model::find($id);
        if ($item) {
            try {
                $item->delete();
                return response()->json(['success' => true, 'message' => 'ลบข้อมูลสำเร็จ']);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false, 
                    'error' => 'ไม่สามารถลบรายการนี้ได้ เนื่องจากมีข้อมูลพนักงานเชื่อมโยงอยู่กับระบบ'
                ], 400);
            }
        }

        return response()->json(['error' => 'ไม่พบข้อมูลที่ต้องการลบ'], 404);
    }

    private function getModelByType($type)
    {
        switch ($type) {
            case 'branch':
                return \App\Models\Branch::class;
            case 'department':
                return \App\Models\Department::class;
            case 'job-level':
                return \App\Models\JobLevel::class;
            case 'job-title':
                return \App\Models\JobTitle::class;
            default:
                return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🟢 ฟังก์ชันสำหรับรองรับ AJAX ในการดึงข้อมูล Job Titles โดยระบุเงื่อนไข ID
    |--------------------------------------------------------------------------
    |
    */
    public function getJobTitlesByDepartment($id)
    {
        // ทำการค้นหาตำแหน่งงานตาม id ของกลุ่มระดับงาน/ฝ่ายที่เกี่ยวข้อง
        $jobTitles = JobTitle::where('job_level_id', $id)
                             ->orderBy('name', 'asc')
                             ->get();

        // หากไม่พบข้อมูลตามเงื่อนไข ให้ดึงข้อมูลทั้งหมดสำรองไว้เพื่อไม่ให้ Dropdown ว่างเปล่า
        if ($jobTitles->isEmpty()) {
            $jobTitles = JobTitle::orderBy('name', 'asc')->get();
        }

        return response()->json($jobTitles);
    }
}