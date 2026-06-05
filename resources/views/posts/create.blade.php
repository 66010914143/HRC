<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สร้างประกาศใหม่ - HRC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        /* ปรับแต่งสไตล์ของ Select2 ให้เข้ากับ Tailwind CSS */
        .select2-container--default .select2-selection--multiple {
            border-color: #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.25rem !important;
            min-height: 48px !important;
            outline: none !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5) !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            color: #1e40af !important;
            border-radius: 0.375rem !important;
            padding: 4px 8px !important;
            margin-top: 5px !important;
            font-size: 0.875rem !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #1e40af !important;
            margin-right: 6px !important;
            border-right: none !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: transparent !important;
            color: #1e3a8a !important;
        }
    </style>
</head>
<body class="bg-gray-100 flex">

    <div class="w-64 bg-slate-800 h-screen text-white p-5 fixed">
        <h1 class="text-xl font-bold mb-8">HRC SYSTEM</h1>
        <nav class="space-y-4">
            <a href="{{ route('dashboard') }}" class="block py-2 px-4 hover:bg-slate-700 rounded transition">หน้าหลัก</a>
            
            @if(Auth::check() && Auth::user()->role !== 'admin' && in_array((int)Auth::user()->level, [0, 1, 2, 3], true))
                <a href="{{ route('posts.create') }}" class="block py-2 px-4 bg-slate-700 rounded text-yellow-400 font-bold border border-yellow-400/30 transition">📢 สร้างประกาศ</a>
            @endif
        </nav>
    </div>

    <div class="ml-64 p-10 w-full">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-slate-800">สร้างประกาศข่าวสาร</h1>
                <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-blue-600 flex items-center">ย้อนกลับ</a>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-blue-800 p-4 text-white text-center font-medium">กรอกรายละเอียดประกาศ</div>
                
                <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">หัวเรื่องประกาศ <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                            placeholder="หัวข้อที่ต้องการแจ้งให้ทราบ...">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">รายละเอียด <span class="text-red-500">*</span></label>
                        <textarea name="content" rows="4" required
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                            placeholder="ระบุรายละเอียดเนื้อหา..."></textarea>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <label class="block text-sm font-semibold text-blue-800 mb-2">
                            ฝ่ายที่ต้องการให้เห็นประกาศ 
                            <span class="text-xs text-gray-500 font-normal italic ml-1">* คลิกเพื่อพิมพ์ค้นหา หรือเลือกได้มากกว่า 1 (หากเว้นว่างจะเห็นทุกคน)</span>
                        </label>
                        @php
                            $dbDepartments = \App\Models\Department::orderBy('name', 'asc')->get();
                        @endphp
                        <select name="target_departments[]" class="select2-multiple w-full" multiple="multiple" data-placeholder="-- คลิกเพื่อเลือกฝ่ายที่ต้องการ --">
                            @foreach($dbDepartments as $dept)
                                <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-blue-800 mb-2">
                            สาขาที่ต้องการให้เห็นประกาศ 
                            <span class="text-xs text-gray-500 font-normal italic ml-1">* คลิกเพื่อพิมพ์ค้นหา หรือเลือกได้มากกว่า 1 (หากเว้นว่างจะเห็นทุกคน)</span>
                        </label>
                        @php
                            $dbBranches = \App\Models\Branch::orderBy('name', 'asc')->get();
                        @endphp
                        <select name="target_branches[]" class="select2-multiple w-full" multiple="multiple" data-placeholder="-- คลิกเพื่อเลือกสาขาที่ต้องการ --">
                            @foreach($dbBranches as $branch)
                                <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">แนบรูปภาพ (ถ้ามี)</label>
                        <input type="file" name="image" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    
                    <button type="submit" class="w-full mt-6 bg-blue-800 text-white py-3 rounded-lg font-bold hover:bg-blue-900 transition shadow-md">
                        ลงประกาศทันที
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2-multiple').select2({
                width: '100%',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "ไม่พบข้อมูลที่ค้นหา";
                    }
                }
            });
        });
    </script>
</body>
</html>