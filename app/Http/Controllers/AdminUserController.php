<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    // แสดงรายชื่อพนักงานทั้งหมด
    public function index() {
        $users = User::where('id', '!=', auth()->id())->get(); 
        return view('admin.users.index', compact('users'));
    }

    // หน้าแก้ไขข้อมูล
    public function edit(User $user) {
        return view('admin.users.edit', compact('user'));
    }

    // บันทึกการแก้ไข
    public function update(Request $request, User $user) {
        // 1. ตรวจสอบข้อมูล (เพิ่มให้ครบตามฟอร์ม edit)
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'branch' => 'required|string',
            'department' => 'required|string',
            'position_level' => 'required',
            'position' => 'required|string',
            'role' => 'required|in:admin,user',
            'password' => 'nullable|min:6', // password เป็นค่าว่างได้ถ้าไม่ต้องการเปลี่ยน
        ]);

        // 2. เตรียมข้อมูลสำหรับอัปเดต
        $data = $request->only([
            'name', 
            'last_name', 
            'position', 
            'position_level', 
            'department', 
            'branch', 
            'role'
        ]);

        // 3. จัดการเรื่องรหัสผ่าน (ถ้ามีการกรอกมาใหม่ ให้ Hash ก่อนบันทึก)
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // 4. บันทึกข้อมูล
        $user->update($data);
        
        return redirect()->route('admin.users.index')->with('success', 'อัปเดตข้อมูลพนักงานเรียบร้อยแล้ว');
    }

    // ลบ User
    public function destroy(User $user) {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'ลบพนักงานออกจากระบบแล้ว');
    }
}