@extends('layouts.app')

@section('title', 'รายละเอียดใบบันทึกภายใน ' . $memo->memo_number . ' - HRC SYSTEM')

@section('content')
<div class="container-fluid px-4 py-6 print:p-0">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 print:hidden">
        <div>
            <nav class="text-sm font-medium text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">หน้าแรก</a>
                <span class="mx-2">/</span>
                <a href="{{ route('internal_memo.index') }}" class="hover:text-blue-600 transition-colors">บันทึกภายใน</a>
                <span class="mx-2">/</span>
                <span class="text-slate-800">รายละเอียดเอกสาร</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-file-alt text-blue-600"></i>
                รายละเอียดใบคำขอบันทึกภายใน
            </h1>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('internal_memo.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-all gap-2 border-0 shadow-sm">
                <i class="fas fa-arrow-left"></i>
                กลับไปหน้าประวัติ
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all gap-2 border-0">
                <i class="fas fa-print"></i>
                พิมพ์เอกสาร (Print)
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:border-0 print:shadow-none print:rounded-none">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex flex-col items-center justify-center relative print:bg-white print:pt-0 print:pb-2">
            <div class="text-center space-y-1">
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-wide print:text-3xl">บันทึกข้อความ</h2>
                <p class="text-sm text-slate-500 font-bold font-mono print:text-slate-800">เลขที่เอกสาร: {{ $memo->memo_number }}</p>
            </div>
            
            <div class="absolute top-6 right-6 print:hidden">
                @if($memo->status === 'approved')
                    <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-full border border-emerald-200 flex items-center gap-1">
                        <i class="fas fa-check-circle text-emerald-500"></i> อนุมัติแล้ว
                    </span>
                @elseif($memo->status === 'rejected')
                    <span class="bg-rose-50 text-rose-700 text-xs font-bold px-3 py-1.5 rounded-full border border-rose-200 flex items-center gap-1">
                        <i class="fas fa-times-circle text-rose-500"></i> ปฏิเสธการอนุมัติ
                    </span>
                @else
                    <span class="bg-amber-50 text-amber-700 text-xs font-bold px-3 py-1.5 rounded-full border border-amber-200 flex items-center gap-1">
                        <i class="fas fa-clock text-amber-500 animate-pulse"></i> อยู่ระหว่างพิจารณา
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-6 print:space-y-4 print:p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100 print:bg-white print:border-black print:border print:rounded-none print:p-3 print:gap-2">
                <div class="space-y-2 print:space-y-1">
                    <p class="text-sm text-slate-700"><strong>ส่วนงาน/ฝ่าย:</strong> {{ $memo->user?->department ?? 'ทั่วไป' }} (สาขา: {{ $memo->user?->branch ?? 'สำนักงานใหญ่' }})</p>
                    <p class="text-sm text-slate-700"><strong>ผู้ยื่นคำขอ:</strong> {{ $memo->user?->name ?? 'ไม่พบข้อมูลพนักงาน' }}</p>
                    <p class="text-sm text-slate-700"><strong>วันที่ยื่นเอกสาร:</strong> {{ $memo->created_at->format('d/m/Y H:i น.') }}</p>
                </div>
                <div class="space-y-2 print:space-y-1">
                    <p class="text-sm text-slate-700"><strong>เรื่อง / วัตถุประสงค์:</strong> <span class="text-slate-900 font-bold">{{ $memo->subject }}</span></p>
                    <p class="text-sm text-slate-700"><strong>วงเงินงบประมาณ:</strong> <span class="text-slate-900 font-bold text-base">{{ $memo->amount ? number_format($memo->amount, 2) . ' บาท' : 'ไม่ได้ระบุวงเงิน / ไม่มีค่าใช้จ่าย' }}</span></p>
                    <p class="text-sm text-slate-700"><strong>รูปแบบการอนุมัติ:</strong> {{ $memo->approval_type == 2 ? 'อนุมัติ 2 ขั้นตอน (หัวหน้าฝ่าย -> CEO)' : 'อนุมัติ 1 ขั้นตอน (ส่งตรงหา CEO)' }}</p>
                </div>
            </div>

            @if($memo->status === 'rejected' && $memo->reject_reason)
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-xl shadow-sm print:border-black print:bg-white print:text-black print:rounded-none print:border print:p-2">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <i class="fas fa-comment-slash print:hidden"></i>
                        <span>เหตุผลที่ปฏิเสธการอนุมัติ:</span>
                    </div>
                    <p class="text-sm pl-0 md:pl-6 font-medium">{{ $memo->reject_reason }}</p>
                </div>
            @endif

            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2 border-b border-slate-100 pb-2 print:border-black print:mb-2 print:pb-1">
                    <i class="fas fa-signature text-slate-500 print:hidden"></i>
                    เส้นทางการลงนามและสถานะการพิจารณา (Approval Timeline)
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 print:gap-4 print:pt-0">
                    @if($memo->approver1)
                        <div class="p-4 border rounded-xl bg-white space-y-2 relative print:border-black print:rounded-none print:p-2 print:space-y-0.5">
                            <span class="absolute top-3 right-3 text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border print:hidden
                                @if($memo->approver_1_status === 'approved') bg-emerald-50 text-emerald-700 border-emerald-200
                                @elseif($memo->approver_1_status === 'rejected') bg-rose-50 text-rose-700 border-rose-200
                                @else bg-amber-50 text-amber-700 border-amber-200 @endif">
                                ขั้นที่ 1
                            </span>
                            <p class="text-xs text-slate-400 font-bold uppercase">ผู้อนุมัติขั้นที่ 1 (หัวหน้าแผนก/ฝ่าย)</p>
                            <p class="text-sm font-bold text-slate-800">{{ $memo->approver1->name }}</p>
                            <p class="text-xs text-slate-500">ตำแหน่ง: {{ $memo->approver1->position ?? 'หัวหน้างาน' }}</p>
                            <div class="pt-2 text-xs font-semibold flex items-center gap-1.5 print:pt-1">
                                <span>สถานะ:</span>
                                @if($memo->approver_1_status === 'approved')
                                    <span class="text-emerald-600"><i class="fas fa-check-circle"></i> อนุมัติแล้ว</span>
                                @elseif($memo->approver_1_status === 'rejected')
                                    <span class="text-rose-600"><i class="fas fa-times-circle"></i> ปฏิเสธการอนุมัติ</span>
                                @else
                                    <span class="text-amber-600 animate-pulse"><i class="fas fa-spinner"></i> รอการพิจารณา</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-4 border border-dashed rounded-xl bg-slate-50/50 flex flex-col justify-center items-center text-center p-4 print:hidden">
                            <p class="text-xs text-slate-400 font-bold"><i class="fas fa-forward"></i> ข้ามขั้นที่ 1</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">เอกสารระดับหัวหน้างาน วิ่งตรงหา CEO อัตโนมัติ</p>
                        </div>
                    @endif

                    <div class="p-4 border rounded-xl bg-white space-y-2 relative print:border-black print:rounded-none print:p-2 print:space-y-0.5">
                        <span class="absolute top-3 right-3 text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border print:hidden
                            @if($memo->status === 'approved') bg-emerald-50 text-emerald-700 border-emerald-200
                            @elseif($memo->status === 'rejected' && ($memo->approver_1_status === 'approved' || !$memo->approver1)) bg-rose-50 text-rose-700 border-rose-200
                            @else bg-slate-50 text-slate-500 border-slate-200 @endif">
                            ขั้นสุดท้าย
                        </span>
                        <p class="text-xs text-slate-400 font-bold uppercase">ผู้อนุมัติขั้นสุดท้าย (CEO / ผู้บริหารสูงสุด)</p>
                        <p class="text-sm font-bold text-slate-800">{{ $memo->approver2->name ?? 'ประธานเจ้าหน้าที่บริหาร' }}</p>
                        <p class="text-xs text-slate-500">ตำแหน่ง: {{ $memo->approver2->position ?? 'CEO' }}</p>
                        <div class="pt-2 text-xs font-semibold flex items-center gap-1.5 print:pt-1">
                            <span>สถานะ:</span>
                            @if($memo->status === 'approved')
                                <span class="text-emerald-600"><i class="fas fa-check-circle"></i> อนุมัติเสร็จสิ้น</span>
                            @elseif($memo->status === 'rejected' && ($memo->approver_1_status === 'approved' || !$memo->approver1))
                                <span class="text-rose-600"><i class="fas fa-times-circle"></i> ปฏิเสธการอนุมัติ</span>
                            @else
                                <span class="text-slate-400"><i class="fas fa-hourglass-start"></i> รอคิวตรวจสอบ</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="print:hidden">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                    <i class="fas fa-paperclip text-slate-500"></i>
                    เอกสารแนบเพิ่มเติม (Attachment Files)
                </h3>
                
                {{-- สมมติว่ามีฟังก์ชันความสัมพันธ์ของโมเดลหรืออาร์เรย์เก็บไฟล์ดั้งเดิมของคุณ --}}
                @if(isset($memo->attachments) && count($memo->attachments) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($memo->attachments as $file)
                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 transition-all text-slate-700 text-xs font-semibold group">
                                <i class="far fa-file-pdf text-rose-500 text-lg group-hover:scale-110 transition-transform"></i>
                                <div class="truncate">
                                    <p class="truncate text-slate-800 font-bold">{{ $file->file_name }}</p>
                                    <p class="text-[10px] text-slate-400 font-normal">คลิกเพื่อเปิดดูไฟล์ในแท็บใหม่</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 bg-slate-50 p-4 rounded-xl text-center font-medium border border-slate-100">
                        <i class="far fa-comment-dots mr-1"></i> ไม่มีไฟล์เอกสารแนบประกอบรายการนี้
                    </p>
                @endif
            </div>

            <div class="hidden print:block pt-4">
                <div class="grid grid-cols-2 gap-6 text-center text-sm">
                    <div class="flex flex-col items-center justify-end space-y-1">
                        <span class="text-left w-full max-w-[220px]">ลงชื่อ......................................................ผู้ขออนุมัติ</span>
                        <div class="h-[45px] flex items-center justify-center my-0.5">
                            @if($memo->user && $memo->user->signature)
                                <img src="{{ asset('storage/' . $memo->user->signature) }}" alt="Signature" class="max-h-[45px] max-w-[150px] object-contain mix-blend-multiply">
                            @else
                                <div class="h-[20px]"></div>
                            @endif
                        </div>
                        <p class="text-xs">( {{ $memo->user?->name ?? 'ไม่พบข้อมูลพนักงาน' }} )</p>
                        <p class="text-xs">วันที่ ...... / ...... / ......</p>
                    </div>

                    <div class="flex flex-col items-center justify-end space-y-1">
                        <span class="text-left w-full max-w-[220px]">ลงชื่อ......................................................ผู้อนุมัติ</span>
                        <div class="h-[45px] flex items-center justify-center my-0.5">
                            @if($memo->status === 'approved' && $memo->approver2 && $memo->approver2->signature)
                                <img src="{{ asset('storage/' . $memo->approver2->signature) }}" alt="CEO Signature" class="max-h-[45px] max-w-[150px] object-contain mix-blend-multiply">
                            @else
                                <div class="h-[20px]"></div>
                            @endif
                        </div>
                        <p class="text-xs">( @if($memo->status === 'approved') {{ $memo->approver2->name ?? 'ประธานเจ้าหน้าที่บริหาร' }} @else ...................................................... @endif )</p>
                        <p class="text-xs">วันที่ ...... / ...... / ......</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* ซ่อนแชสซี แถบเมนูข้าง ปุ่มกด และส่วนหัวเว็บที่ไม่เกี่ยวข้องออกทั้งหมด */
    nav, aside, header, footer, button, .print\:hidden {
        display: none !important;
    }
    /* ปรับแต่งหน้ากระดาษให้เป็นพื้นขาวและมีขอบตามมาตรฐานการพิมพ์ */
    body {
        background-color: #ffffff !important;
        color: #000000 !important;
        font-family: 'Sarabun', sans-serif !important;
    }
    .print\:p-0 {
        padding: 0 !important;
    }
    /* บังคับให้เบราว์เซอร์พิมพ์สีพื้นหลังและรูปภาพลายเซ็น */
    img {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endsection