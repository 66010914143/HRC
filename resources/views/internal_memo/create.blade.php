@extends('layouts.app')

@section('title', 'สร้างบันทึกภายใน (Internal Memo) - HRC SYSTEM')

@section('content')
<div class="container-fluid" x-data="{ approvalType: '{{ old('approval_type', $isStaff ? '2' : '1') }}' }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <nav class="text-sm font-medium text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">หน้าแรก</a>
                <span class="mx-2">/</span>
                <a href="{{ route('internal_memo.index') }}" class="hover:text-blue-600 transition-colors">บันทึกภายใน</a>
                <span class="mx-2">/</span>
                <span class="text-slate-800">สร้างใบคำขอ</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-file-signature text-blue-600"></i>
                แบบฟอร์มคำขอบันทึกภายใน (Internal Memo)
            </h1>
        </div>
        <div>
            <a href="{{ route('internal_memo.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-all gap-2 border-0 shadow-sm">
                <i class="fas fa-arrow-left"></i>
                กลับไปหน้าประวัติ
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-xl shadow-sm flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-xl shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="font-bold">เกิดข้อผิดพลาดในการกรอกข้อมูล:</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div class="text-sm text-slate-600">
                <strong>ผู้ยื่นขอ:</strong> {{ $user->name }} | 
                <strong>แผนก/ฝ่าย:</strong> {{ $user->department ?? 'ทั่วไป' }} | 
                <strong>สาขา:</strong> {{ $user->branch ?? 'สำนักงานใหญ่' }}
            </div>
            @if(!$isStaff)
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1">
                    <i class="fas fa-user-shield"></i> ระดับหัวหน้างาน (ส่งตรงหา CEO อัตโนมัติ)
                </span>
            @endif
        </div>

        <form action="{{ route('internal_memo.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf

            <div>
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <span class="bg-blue-500 text-white w-5 h-5 rounded-full inline-flex items-center justify-center text-xs">1</span>
                    รายละเอียดบันทึกข้อความ
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            เรื่อง / วัตถุประสงค์ <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                <i class="fas fa-pen-nib text-sm"></i>
                            </span>
                            <select name="subject" id="subject" required
                                class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm text-slate-900 appearance-none">
                                <option value="" disabled {{ old('subject') ? '' : 'selected' }}>-- กรุณาเลือกเรื่อง / วัตถุประสงค์ --</option>
                                
                                <optgroup label="📄 เอกสารจัดซื้อ / จัดจ้าง / สัญญา">
                                    <option value="ขอใบเสนอราคา (SO)" {{ old('subject') === 'ขอใบเสนอราคา (SO)' ? 'selected' : '' }}>- ขอใบเสนอราคา (SO)</option>
                                    <option value="ขอ Quotation" {{ old('subject') === 'ขอ Quotation' ? 'selected' : '' }}>- ขอ Quotation</option>
                                    <option value="ขอใบอนุมัติจัดซื้อ/จ้าง (PR)" {{ old('subject') === 'ขอใบอนุมัติจัดซื้อ/จ้าง (PR)' ? 'selected' : '' }}>- ขอใบอนุมัติจัดซื้อ/จ้าง (PR)</option>
                                    <option value="ขอใบสั่งซื้อ/จ้าง (PO)" {{ old('subject') === 'ขอใบสั่งซื้อ/จ้าง (PO)' ? 'selected' : '' }}>- ขอใบสั่งซื้อ/จ้าง (PO)</option>
                                    <option value="ขอทำสัญญาจ้าง/พัฒนา/ซื้อขาย" {{ old('subject') === 'ขอทำสัญญาจ้าง/พัฒนา/ซื้อขาย' ? 'selected' : '' }}>- ขอทำสัญญาจ้าง/พัฒนา/ซื้อขาย</option>
                                    <option value="ขอส่งมอบงานตามสัญญา" {{ old('subject') === 'ขอส่งมอบงานตามสัญญา' ? 'selected' : '' }}>- ขอส่งมอบงานตามสัญญา</option>
                                    <option value="ขอคืนเงินประกันตามสัญญา" {{ old('subject') === 'ขอคืนเงินประกันตามสัญญา' ? 'selected' : '' }}>- ขอคืนเงินประกันตามสัญญา</option>
                                </optgroup>

                                <optgroup label="💰 การเงิน / เบิกจ่าย">
                                    <option value="ขอเบิกจ่าย" {{ old('subject') === 'ขอเบิกจ่าย' ? 'selected' : '' }}>- ขอเบิกจ่าย</option>
                                    <option value="ขอเบิกเงินทดรองจ่าย" {{ old('subject') === 'ขอเบิกเงินทดรองจ่าย' ? 'selected' : '' }}>- ขอเบิกเงินทดรองจ่าย</option>
                                    <option value="ขอเบิกค่า Commission, Incentive" {{ old('subject') === 'ขอเบิกค่า Commission, Incentive' ? 'selected' : '' }}>- ขอเบิกค่า Commission, Incentive</option>
                                </optgroup>

                                <optgroup label="👥 ทรัพยากรบุคคล (HR)">
                                    <option value="ขอบุคลากรร่วมงาน" {{ old('subject') === 'ขอบุคลากรร่วมงาน' ? 'selected' : '' }}>- ขอบุคลากรร่วมงาน</option>
                                    <option value="ขออัตรากำลังคน" {{ old('subject') === 'ขออัตรากำลังคน' ? 'selected' : '' }}>- ขออัตรากำลังคน</option>
                                    <option value="ขอฝึกอบรมพัฒนาบุคลากร" {{ old('subject') === 'ขอฝึกอบรมพัฒนาบุคลากร' ? 'selected' : '' }}>- ขอฝึกอบรมพัฒนาบุคลากร</option>
                                    <option value="ขอศึกษา/ดูงาน" {{ old('subject') === 'ขอศึกษา/ดูงาน' ? 'selected' : '' }}>- ขอศึกษา/ดูงาน</option>
                                </optgroup>

                                <optgroup label="📜 หนังสือและเอกสารทางราชการ">
                                    <option value="ขอหนังสือมอบอำนาจทั่วไป" {{ old('subject') === 'ขอหนังสือมอบอำนาจทั่วไป' ? 'selected' : '' }}>- ขอหนังสือมอบอำนาจทั่วไป</option>
                                    <option value="ขอหนังสือมอบอำนาจที่มีภาระผูกพันบริษัท" {{ old('subject') === 'ขอหนังสือมอบอำนาจที่มีภาระผูกพันบริษัท' ? 'selected' : '' }}>- ขอหนังสือมอบอำนาจที่มีภาระผูกพันบริษัท</option>
                                    <option value="ขอจดหมาย" {{ old('subject') === 'ขอจดหมาย' ? 'selected' : '' }}>- ขอจดหมาย</option>
                                </optgroup>

                                <optgroup label="💻 เทคโนโลยีสารสนเทศ (IT)">
                                    <option value="ขอ Project Code Name" {{ old('subject') === 'ขอ Project Code Name' ? 'selected' : '' }}>- ขอ Project Code Name</option>
                                    <option value="ขอพัฒนาระบบโปรแกรม" {{ old('subject') === 'ขอพัฒนาระบบโปรแกรม' ? 'selected' : '' }}>- ขอพัฒนาระบบโปรแกรม</option>
                                    <option value="ขอเปิดระบบทดลองใช้งาน" {{ old('subject') === 'ขอเปิดระบบทดลองใช้งาน' ? 'selected' : '' }}>- ขอเปิดระบบทดลองใช้งาน</option>
                                    <option value="ขอทำโครงการ ITI" {{ old('subject') === 'ขอทำโครงการ ITI' ? 'selected' : '' }}>- ขอทำโครงการ ITI</option>
                                </optgroup>

                                <optgroup label="📊 โครงการตามหน่วยงาน">
                                    <option value="ขอทำโครงการ (การตลาด)" {{ old('subject') === 'ขอทำโครงการ (การตลาด)' ? 'selected' : '' }}>- ขอทำโครงการ (การตลาด)</option>
                                    <option value="ขอทำโครงการ (การเงิน)" {{ old('subject') === 'ขอทำโครงการ (การเงิน)' ? 'selected' : '' }}>- ขอทำโครงการ (การเงิน)</option>
                                    <option value="ขอทำโครงการ (บัญชี)" {{ old('subject') === 'ขอทำโครงการ (บัญชี)' ? 'selected' : '' }}>- ขอทำโครงการ (บัญชี)</option>
                                    <option value="ขอทำโครงการ (กฎหมาย)" {{ old('subject') === 'ขอทำโครงการ (กฎหมาย)' ? 'selected' : '' }}>- ขอทำโครงการ (กฎหมาย)</option>
                                    <option value="ขอทำโครงการ (จัดซื้อ)" {{ old('subject') === 'ขอทำโครงการ (จัดซื้อ)' ? 'selected' : '' }}>- ขอทำโครงการ (จัดซื้อ)</option>
                                    <option value="ขอทำโครงการ (ธุรการ)" {{ old('subject') === 'ขอทำโครงการ (ธุรการ)' ? 'selected' : '' }}>- ขอทำโครงการ (ธุรการ)</option>
                                    <option value="ขอทำโครงการ (ตรอ.)" {{ old('subject') === 'ขอทำโครงการ (ตรอ.)' ? 'selected' : '' }}>- ขอทำโครงการ (ตรอ.)</option>
                                    <option value="ขอทำโครงการ (โรงเรียน)" {{ old('subject') === 'ขอทำโครงการ (โรงเรียน)' ? 'selected' : '' }}>- ขอทำโครงการ (โรงเรียน)</option>
                                </optgroup>

                                <optgroup label="🏢 อาคาร สถานที่ และซ่อมบำรุง">
                                    <option value="ขอซ่อมบำรุง/อาคาร/สถานที่" {{ old('subject') === 'ขอซ่อมบำรุง/อาคาร/สถานที่' ? 'selected' : '' }}>- ขอซ่อมบำรุง/อาคาร/สถานที่</option>
                                </optgroup>

                                <optgroup label="🔍 ตรวจสอบภายใน (Internal Audit)">
                                    <option value="Internal Audit ฝ่ายมาตราฐาน" {{ old('subject') === 'Internal Audit ฝ่ายมาตราฐาน' ? 'selected' : '' }}>- Internal Audit ฝ่ายมาตราฐาน</option>
                                    <option value="Internal Audit ฝ่าย IDC (ITI และการตลาด)" {{ old('subject') === 'Internal Audit ฝ่าย IDC (ITI และการตลาด)' ? 'selected' : '' }}>- Internal Audit ฝ่าย IDC (ITI และการตลาด)</option>
                                    <option value="Internal Audit ฝ่าย AC (บัญชี)" {{ old('subject') === 'Internal Audit ฝ่าย AC (บัญชี)' ? 'selected' : '' }}>- Internal Audit ฝ่าย AC (บัญชี)</option>
                                    <option value="Internal Audit ฝ่าย CD (ตรอ.)" {{ old('subject') === 'Internal Audit ฝ่าย CD (ตรอ.)' ? 'selected' : '' }}>- Internal Audit ฝ่าย CD (ตรอ.)</option>
                                    <option value="Internal Audit ฝ่าย IDD (โรงเรียน)" {{ old('subject') === 'Internal Audit ฝ่าย IDD (โรงเรียน)' ? 'selected' : '' }}>- Internal Audit ฝ่าย IDD (โรงเรียน)</option>
                                </optgroup>
                            </select>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 pointer-events-none">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-semibold text-slate-700 mb-2">
                            จำนวนเงินงบประมาณ (บาท) <span class="text-slate-400 font-normal">(ถ้ามี)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i class="fas fa-baht-sign text-sm"></i>
                            </span>
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" min="0" step="0.01"
                                class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm text-slate-900"
                                placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <span class="bg-blue-500 text-white w-5 h-5 rounded-full inline-flex items-center justify-center text-xs">2</span>
                    กำหนดเส้นทางการอนุมัติ (Approval Route)
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            รูปแบบการอนุมัติ <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="border rounded-xl p-3 flex items-center gap-2 cursor-pointer transition-all hover:bg-slate-50"
                                :class="approvalType === '1' ? 'border-blue-500 bg-blue-50/40 text-blue-700 font-bold' : 'border-slate-200'">
                                <input type="radio" name="approval_type" value="1" x-model="approvalType" class="text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">อนุมัติ 1 ขั้นตอน</span>
                            </label>
                            <label class="border rounded-xl p-3 flex items-center gap-2 cursor-pointer transition-all hover:bg-slate-50"
                                :class="approvalType === '2' ? 'border-blue-500 bg-blue-50/40 text-blue-700 font-bold' : 'border-slate-200'">
                                <input type="radio" name="approval_type" value="2" x-model="approvalType" class="text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">อนุมัติ 2 ขั้นตอน</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <div x-show="approvalType === '2'" x-transition>
                            <label for="approver_1_id" class="block text-sm font-semibold text-slate-700 mb-2">
                                ผู้อนุมัติขั้นที่ 1 (หัวหน้าแผนก/ฝ่าย) <span class="text-rose-500">*</span>
                            </label>
                            <select name="approver_1_id" id="approver_1_id" :required="approvalType === '2'"
                                class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm text-slate-900">
                                <option value="">-- เลือกหัวหน้าแผนก --</option>
                                @foreach($departmentHeads as $head)
                                    <option value="{{ $head->id }}" {{ old('approver_1_id') == $head->id ? 'selected' : '' }}>
                                        {{ $head->name }} ({{ $head->position }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="approvalType === '1'" x-transition>
                            <label class="block text-sm font-semibold text-slate-400 mb-2">ผู้อนุมัติขั้นที่ 1</label>
                            <div class="px-4 bg-slate-100 text-slate-400 text-sm rounded-xl border border-slate-200 h-[46px] flex items-center justify-center font-medium select-none w-full">
                                <i class="fas fa-forward mr-2 text-xs"></i> ข้ามขั้นตอน (ระบบจะส่งตรงหา CEO)
                            </div>
                        </div>
                    </div>

                    <div>
                        <div x-show="approvalType === '2'" x-transition>
                            <label for="approver_2_id" class="block text-sm font-semibold text-slate-700 mb-2">
                                ผู้อนุมัติขั้นสุดท้าย (CEO / ผู้บริหารสูงสุด) <span class="text-rose-500">*</span>
                            </label>
                            <select name="approver_2_id" id="approver_2_id" :required="approvalType === '2'"
                                class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm text-slate-900">
                                <option value="">-- เลือกประธานเจ้าหน้าที่บริหาร --</option>
                                @foreach($ceos as $ceo)
                                    <option value="{{ $ceo->id }}" {{ old('approver_2_id') == $ceo->id ? 'selected' : '' }}>
                                        {{ $ceo->name }} ({{ $ceo->position }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="approvalType === '1'" x-transition>
                            <label for="approver_2_id_direct" class="block text-sm font-semibold text-slate-700 mb-2">
                                ผู้อนุมัติส่งตรง (CEO / ผู้บริหารสูงสุด) <span class="text-rose-500">*</span>
                            </label>
                            <select name="approver_2_id" id="approver_2_id_direct" :required="approvalType === '1'"
                                class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm text-slate-900">
                                <option value="">-- เลือกประธานเจ้าหน้าที่บริหาร --</option>
                                @foreach($ceos as $ceo)
                                    <option value="{{ $ceo->id }}" {{ old('approver_2_id') == $ceo->id ? 'selected' : '' }}>
                                        {{ $ceo->name }} ({{ $ceo->position }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <span class="bg-blue-500 text-white w-5 h-5 rounded-full inline-flex items-center justify-center text-xs">3</span>
                    เอกสารแนบเพิ่มเติม (Attachment Files)
                </h2>
                
                <div class="max-w-xl">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        เลือกไฟล์เอกสารประกอบ <span class="text-slate-400 font-normal">(อัปโหลดได้หลายไฟล์พร้อมกัน)</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-cloud-upload-alt text-slate-400 text-2xl mb-2"></i>
                                <p class="mb-1 text-sm text-slate-500 font-medium">คลิกเพื่อเลือกไฟล์ หรือ ลากมาวางที่นี่</p>
                                <p class="text-xs text-slate-400">PDF, Word, Excel, รูปภาพ (ขนาดรวมสูงสุดไม่เกิน 10MB ต่อไฟล์)</p>
                            </div>
                            <input type="file" name="files[]" id="files" multiple class="hidden" />
                        </label>
                    </div>
                    <div id="file-list" class="mt-3 space-y-1.5 text-xs text-slate-600 font-medium"></div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end items-center gap-3">
                <button type="reset" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-all border-0">
                    ล้างข้อมูลฟอร์ม
                </button>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2 border-0">
                    <i class="fas fa-paper-plane text-xs"></i>
                    ส่งใบคำขออนุมัติ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // สคริปต์ตรวจจับและแสดงชื่อไฟล์แนบที่เลือกแบบเรียลไทม์
    document.getElementById('files').addEventListener('change', function(e) {
        const fileList = document.getElementById('file-list');
        fileList.innerHTML = '';
        
        if (this.files.length > 0) {
            const heading = document.createElement('div');
            heading.className = 'font-bold text-slate-700 mb-1 flex items-center gap-1 text-sm';
            heading.innerHTML = `<i class="fas fa-paperclip text-slate-400 text-xs"></i> ไฟล์ที่เลือกแนบ (${this.files.length} ไฟล์):`;
            fileList.appendChild(heading);

            for (let i = 0; i < this.files.length; i++) {
                const item = document.createElement('div');
                item.className = 'flex items-center gap-2 py-1 px-3 bg-slate-100 rounded-lg text-slate-600 text-xs w-fit';
                
                // คำนวณขนาดไฟล์ให้อ่านง่าย
                let fileSize = (this.files[i].size / 1024).toFixed(1) + ' KB';
                if (this.files[i].size > 1024 * 1024) {
                    fileSize = (this.files[i].size / (1024 * 1024)).toFixed(1) + ' MB';
                }

                item.innerHTML = `<i class="far fa-file text-slate-400"></i> <span>${this.files[i].name}</span> <span class="text-slate-400">(${fileSize})</span>`;
                fileList.appendChild(item);
            }
        }
    });
</script>
@endpush