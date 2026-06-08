@extends('layouts.app')

@section('title', __('messages.edit_member_title') . ' - HRC System')

@section('content')
<div class="min-h-screen flex flex-col items-center py-10 px-6 bg-slate-100">
    
    <div class="w-full max-w-4xl flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ __('messages.edit_member_header') }}</h1>
        <a href="{{ route('admin.users.index') }}" class="flex items-center text-slate-600 hover:text-blue-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            {{ __('messages.back_to_manage') }}
        </a>
    </div>

    <div class="bg-white w-full max-w-4xl rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="bg-blue-800 p-4">
            <p class="text-white text-center font-medium">{{ __('messages.fill_employee_edit_info') }}</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 m-6 mb-0">
                <p class="font-bold">{{ __('messages.error_alert') }}:</p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            @method('PUT')

            <div class="col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.first_name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.last_name') }}</label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold text-gray-400 mb-2">{{ __('messages.user_id_login') }}</label>
                <input type="text" name="username" value="{{ $user->username }}" 
                       class="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed outline-none" readonly>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    {{ __('messages.new_password_label') }} 
                    <span class="text-xs font-normal text-gray-400">({{ __('messages.leave_blank_if_no_change') }})</span>
                </label>
                <input type="password" name="password" id="password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.branch_label') }}</label>
                <select id="branch" name="branch" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white" required>
                    <option value="">{{ __('messages.select_branch') }}</option>
                    @foreach($branches as $branch)
                        @php 
                            $branch_name = $branch->name ?? $branch->branch_name ?? ''; 
                        @endphp
                        <option value="{{ $branch_name }}" {{ old('branch', $user->branch) == $branch_name ? 'selected' : '' }}>
                            {{ $branch_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.department_label') }}</label>
                <select id="department" name="department" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white" required>
                    <option value="">{{ __('messages.select_department') }}</option>
                    @foreach($departments as $dept)
                        @php 
                            $dept_name = $dept->name ?? $dept->department_name ?? ''; 
                        @endphp
                        <option value="{{ $dept_name }}" {{ old('department', $user->department) == $dept_name ? 'selected' : '' }}>
                            {{ $dept_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.job_level_label') }}</label>
                <select id="position_level" name="position_level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white" required>
                    <option value="">{{ __('messages.select_level') }}</option>
                    @foreach($position_levels as $level)
                        @php
                            $lvl_id = $level->id ?? $level->level ?? $level->level_number ?? '';
                            $lvl_name = $level->name ?? $level->level_name ?? '';
                        @endphp
                        <option value="{{ $lvl_id }}" {{ old('position_level', $user->position_level) == $lvl_id ? 'selected' : '' }}>
                            {{ $lvl_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.job_title_label') }}</label>
                <select id="position" name="position" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white" required>
                    <option value="">{{ __('messages.select_position') }}</option>
                </select>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('messages.system_role') }}</label>
                <select name="role" class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50" required>
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>{{ __('messages.role_user_desc_short') }}</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('messages.role_admin_desc_short') }}</option>
                </select>
            </div>

            <div class="col-span-2 mt-4 space-y-3">
                <button type="submit" class="w-full bg-blue-800 text-white py-3 rounded-lg font-bold hover:bg-blue-900 shadow-md transition duration-200">
                    {{ __('messages.save_changes_btn') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="block text-center w-full py-2 text-gray-500 hover:text-gray-700 transition text-sm">
                    {{ __('messages.cancel_btn') }}
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // ดึงค่าคำแปลมาเก็บไว้ในตัวแปร JavaScript เพื่อใช้สลับภาษาใน Dropdown
    const txtSelectPosition = "{{ __('messages.select_position') }}";
    const txtSelectBranch = "{{ __('messages.select_branch') }}";
    const txtSelectDepartment = "{{ __('messages.select_department') }}";
    const txtSelectLevel = "{{ __('messages.select_level') }}";

    // 1. รายชื่อตำแหน่งทั้งหมดที่มีอยู่ในระบบ
    const allPositions = [
        @foreach($positions as $pos)
            @php $pos_name = $pos->name ?? $pos->position_name ?? ''; @endphp
            @if($pos_name)
                "{{ $pos_name }}",
            @endif
        @endforeach
    ];

    // โครงสร้างจับคู่ชื่อข้อความของแต่ละระดับ
    const levelNamesMapping = {
        @foreach($position_levels as $level)
            @php 
                $lvl_id = $level->id ?? $level->level ?? $level->level_number ?? '';
                $lvl_name = $level->name ?? $level->level_name ?? '';
            @endphp
            "{{ $lvl_id }}": "{{ $lvl_name }}",
        @endforeach
    };

    // 2. โครงสร้างความสัมพันธ์กรณีดึงโดยตรงผ่านฐานข้อมูล
    const positionData = {
        @foreach($position_levels as $level)
            @php $lvl_id = $level->id ?? $level->level ?? $level->level_number ?? ''; @endphp
            "{{ $lvl_id }}": [
                @foreach($positions as $pos)
                    @php
                        $pos_level = $pos->job_level_id ?? $pos->position_level_id ?? $pos->level_id ?? $pos->position_level ?? '';
                        $pos_name = $pos->name ?? $pos->position_name ?? '';
                    @endphp
                    @if((string)$pos_level === (string)$lvl_id && $pos_name)
                        "{{ $pos_name }}",
                    @endif
                @endforeach
            ],
        @endforeach
    };

    const levelSelect = document.getElementById('position_level');
    const positionSelect = document.getElementById('position');
    
    // ดึงค่าตำแหน่งปัจจุบันของ User จากฐานข้อมูลหรือ Old value ของ Form
    let currentPosition = "{{ old('position', $user->position) }}";

    // ฟังก์ชันตรวจสอบว่า Dropdown กำลังถูกคลิกใช้งานอยู่หรือไม่ ป้องกันการรีเฟรชขณะใช้งาน
    const isFocused = (id) => document.activeElement === document.getElementById(id);

    // ฟังก์ชันกรองตำแหน่งงานแยกตามระดับอย่างเด็ดขาดและแม่นยำ
    function updatePositions(level, selectedPos = null) {
        if (isFocused('position')) return; // ป้องกันการเปลี่ยนค่าตัวเลือกขณะ User กำลังคลิกเลือก

        const activeSelect = selectedPos || positionSelect.value;
        let optionsHTML = `<option value="">${txtSelectPosition}</option>`;
        
        let targets = [];
        
        if (level === '') {
            targets = allPositions;
        } else {
            // ตรวจสอบขั้นแรก: ข้อมูลในฐานข้อมูลผูก ID ไว้หรือไม่
            if (positionData[level] && positionData[level].length > 0) {
                targets = positionData[level];
            } else {
                // ขั้นที่สอง: การคัดกรองอัจฉริยะด้วยการเช็ก String และแยกคีย์เวิร์ด
                const levelFullText = levelNamesMapping[level] || '';
                
                if (levelFullText.includes('ประธานเจ้าหน้าที่บริหาร') || levelFullText.includes('ระดับ 0') || (level == '0')) {
                    targets = allPositions.filter(p => p.includes('ประธานเจ้าหน้าที่บริหาร') || p.includes('CEO'));
                } else if (levelFullText.includes('ประธานสายงาน') || levelFullText.includes('ระดับ 1') || (level == '1')) {
                    targets = allPositions.filter(p => p.includes('ประธานสายงาน'));
                } else if (levelFullText.includes('ผู้อำนวยการ') || levelFullText.includes('ระดับ 2') || (level == '2')) {
                    targets = allPositions.filter(p => p.includes('ผู้อำนวยการ'));
                } else if (levelFullText.includes('ผู้จัดการกลุ่มงาน') || levelFullText.includes('ระดับ 3') || (level == '3')) {
                    targets = allPositions.filter(p => p.includes('ผู้จัดการกลุ่มงาน'));
                } else if (levelFullText.includes('ผู้จัดการฝ่าย') || levelFullText.includes('ระดับ 4') || (level == '4')) {
                    targets = allPositions.filter(p => p.includes('ผู้จัดการฝ่าย') || p.includes('ผู้จัดการ'));
                } else if (levelFullText.includes('เจ้าหน้าที่') || levelFullText.includes('ระดับ 5') || (level == '5')) {
                    targets = allPositions.filter(p => (p.includes('เจ้าหน้าที่') || p.includes('พนักงาน')) && !p.includes('ประธาน'));
                } else if (levelFullText.includes('ทดลองงาน') || levelFullText.includes('ระดับ 6') || (level == '6')) {
                    targets = allPositions.filter(p => p.includes('ทดลองงาน') || p.includes('ฝึกงาน'));
                } else {
                    // หากไม่ตรงเงื่อนไขคำใดๆ เลย ให้ดึงข้อมูลทั้งหมดมาแสดงป้องกันหน้าจอว่างเปล่า
                    targets = allPositions;
                }
            }
        }

        // ลบชื่อซ้ำออก
        const uniqueTargets = [...new Set(targets)];

        // ประกอบ String HTML เพื่อลดปัญหาภาพกระตุก
        uniqueTargets.forEach(function(pos) {
            const isSelected = (pos === activeSelect || pos === currentPosition) ? 'selected' : '';
            optionsHTML += `<option value="${pos}" ${isSelected}>${pos}</option>`;
        });

        // อัปเดตเมื่อมีข้อมูลเปลี่ยนแปลงเท่านั้น
        if(positionSelect.innerHTML !== optionsHTML) {
            positionSelect.innerHTML = optionsHTML;
        }
    }

    // ฟังก์ชันดึงข้อมูลแบบ Real-time จาก API หลังบ้าน
    async function fetchDropdownsRealtime() {
        try {
            // ดึงข้อมูลสาขา
            if (!isFocused('branch')) {
                const resBranch = await fetch('/admin/api/branches');
                if (resBranch.ok) {
                    const branches = await resBranch.json();
                    const selectBranch = document.getElementById('branch');
                    const currentVal = selectBranch.value;
                    let branchHTML = `<option value="">${txtSelectBranch}</option>`;
                    branches.forEach(b => {
                        const name = b.name || b.branch_name;
                        if(name) branchHTML += `<option value="${name}" ${currentVal === name ? 'selected' : ''}>${name}</option>`;
                    });
                    if(selectBranch.innerHTML !== branchHTML) selectBranch.innerHTML = branchHTML;
                }
            }

            // ดึงข้อมูลฝ่าย
            if (!isFocused('department')) {
                const resDept = await fetch('/admin/api/departments');
                if (resDept.ok) {
                    const depts = await resDept.json();
                    const selectDept = document.getElementById('department');
                    const currentVal = selectDept.value;
                    let deptHTML = `<option value="">${txtSelectDepartment}</option>`;
                    depts.forEach(d => {
                        const name = d.name || d.department_name;
                        if(name) deptHTML += `<option value="${name}" ${currentVal === name ? 'selected' : ''}>${name}</option>`;
                    });
                    if(selectDept.innerHTML !== deptHTML) selectDept.innerHTML = deptHTML;
                }
            }

            // ดึงข้อมูลระดับสายงาน
            if (!isFocused('position_level')) {
                const resLevel = await fetch('/admin/api/job-levels');
                if (resLevel.ok) {
                    const levels = await resLevel.json();
                    const selectLevel = document.getElementById('position_level');
                    const currentVal = selectLevel.value;
                    let levelHTML = `<option value="">${txtSelectLevel}</option>`;
                    levels.forEach(l => {
                        const id = l.id;
                        const name = l.name || l.level_name;
                        if(id) {
                            levelHTML += `<option value="${id}" ${currentVal == id ? 'selected' : ''}>${name}</option>`;
                            levelNamesMapping[id] = name;
                        }
                    });
                    if(selectLevel.innerHTML !== levelHTML) selectLevel.innerHTML = levelHTML;
                }
            }

            // ดึงข้อมูลตำแหน่งงานเพื่อทำกรองข้อมูลแบบจับคู่
            const resPos = await fetch('/admin/api/job-titles');
            if (resPos.ok) {
                const titles = await resPos.json();
                
                allPositions.length = 0;
                Object.keys(positionData).forEach(key => positionData[key] = []);

                titles.forEach(t => {
                    const name = t.name || t.position_name;
                    const lvlId = t.job_level_id || t.position_level_id || t.level_id || t.position_level;
                    
                    if (name) allPositions.push(name);
                    if (lvlId && name) {
                        if (!positionData[lvlId]) positionData[lvlId] = [];
                        if (!positionData[lvlId].includes(name)) positionData[lvlId].push(name);
                    }
                });

                // ตรวจสอบว่ามีตำแหน่งงานที่เลือกไว้หรือไม่
                const activePos = positionSelect.value || currentPosition;
                updatePositions(levelSelect.value, activePos);
            }
        } catch (error) {
            console.error("ระบบดึงข้อมูล Real-time ขัดข้อง:", error);
        }
    }

    // ทำงานเมื่อผู้ใช้เปลี่ยนระดับสายงาน
    levelSelect.addEventListener('change', function() {
        // เมื่อเปลี่ยนระดับด้วยตนเอง ให้เคลียร์ตรรกะ currentPosition ทิ้งไปเพื่อให้เปลี่ยนตามจริง
        currentPosition = null;
        updatePositions(this.value);
    });

    // โหลดข้อมูลครั้งแรกเมื่อเปิดหน้าเว็บ
    window.addEventListener('DOMContentLoaded', (event) => {
        updatePositions(levelSelect.value, currentPosition);
        fetchDropdownsRealtime();
    });

    // เรียกดึงข้อมูล Real-time อัตโนมัติเมื่อหน้าจอโฟกัส
    window.addEventListener('focus', function() {
        const activePos = positionSelect.value || currentPosition;
        fetchDropdownsRealtime().then(() => {
            if (activePos) {
                updatePositions(levelSelect.value, activePos);
            }
        });
    });

    // ดึงข้อมูลลูปภายในช่วงเวลาที่กำหนด
    setInterval(function() {
        const activePos = positionSelect.value || currentPosition;
        fetchDropdownsRealtime().then(() => {
            if (activePos) {
                updatePositions(levelSelect.value, activePos);
            }
        });
    }, 3000);
</script>
@endsection