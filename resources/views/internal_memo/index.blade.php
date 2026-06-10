@extends('layouts.app')

@section('title', 'ประวัติใบอนุมัติบันทึกภายใน - HRC SYSTEM')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-history text-blue-600"></i>
                ประวัติการขอใบอนุมัติบันทึกภายใน
            </h1>
            <p class="text-sm text-slate-500 mt-1">ตรวจสอบและติดตามสถานะเอกสารบันทึกภายใน (Internal Memo) ทั้งหมดของคุณ</p>
        </div>
        <div>
            <a href="{{ route('internal_memo.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all gap-2 border-0">
                <i class="fas fa-plus text-xs"></i>
                เพิ่มขอใบบันทึกภายใน
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-xl shadow-sm flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-xl shadow-sm flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-700 text-xs uppercase font-bold tracking-wider">
                        <th class="px-6 py-4">เลขที่เอกสาร</th>
                        <th class="px-6 py-4">เรื่องที่ติดต่อ</th>
                        <th class="px-6 py-4 text-right">จำนวนเงิน (บาท)</th>
                        <th class="px-6 py-4 text-center">วันที่ขอ</th>
                        <th class="px-6 py-4 text-center">สถานะ</th>
                        <th class="px-6 py-4 text-center">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600 font-medium">
                    @forelse($myMemos as $memo)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-slate-900 font-bold">
                            <a href="{{ route('internal_memo.show', $memo->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1 group">
                                <i class="far fa-file-alt text-slate-400 group-hover:text-blue-600 transition-colors"></i>
                                {{ $memo->memo_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            <div class="truncate text-slate-800 font-semibold mb-1">{{ $memo->subject }}</div>
                            <div class="text-xs text-slate-400 font-normal flex flex-col gap-0.5">
                                <span>เส้นทาง: {{ $memo->approval_type == 2 ? 'อนุมัติ 2 ขั้นตอน' : 'อนุมัติ 1 ขั้นตอน' }}</span>
                                @if($memo->approver1)
                                    <span class="truncate">ขั้นที่ 1: {{ $memo->approver1->name }} 
                                        (@if($memo->approver_1_status === 'approved') <span class="text-emerald-500">อนุมัติแล้ว</span> 
                                         @elseif($memo->approver_1_status === 'rejected') <span class="text-rose-500">ปฏิเสธ</span>
                                         @else <span class="text-amber-500">รอตรวจ</span> @endif)
                                    </span>
                                @endif
                                @if($memo->approver2)
                                    <span class="truncate">ขั้นสุดท้าย: {{ $memo->approver2->name }}
                                        (@if($memo->status === 'approved') <span class="text-emerald-500">อนุมัติเสร็จสิ้น</span> 
                                         @elseif($memo->status === 'rejected' && ($memo->approver_1_status === 'approved' || !$memo->approver1)) <span class="text-rose-500">ปฏิเสธ</span>
                                         @else <span class="text-slate-400">รอคิว</span> @endif)
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-900">
                            {{ $memo->amount ? number_format($memo->amount, 2) : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center text-slate-500 text-xs">
                            {{ $memo->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($memo->status === 'approved')
                                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> อนุมัติแล้ว
                                </span>
                            @elseif($memo->status === 'rejected')
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> ปฏิเสธการอนุมัติ
                                    </span>
                                    @if($memo->reject_reason)
                                        <span class="text-[11px] text-rose-500 max-w-[150px] truncate block" title="{{ $memo->reject_reason }}">
                                            เหตุผล: {{ $memo->reject_reason }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> รอการอนุมัติ
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $canApprove = false;
                                if ($memo->status === 'pending') {
                                    if ($memo->approval_type == 2 && $memo->approver_1_status === 'pending' && auth()->id() == $memo->approver_1_id) {
                                        $canApprove = true;
                                    } elseif (($memo->approval_type == 1 || $memo->approver_1_status === 'approved') && auth()->id() == $memo->approver_2_id) {
                                        $canApprove = true;
                                    }
                                }
                            @endphp

                            <div class="flex items-center justify-center gap-2">
                                @if($canApprove)
                                    <form action="{{ route('internal_memo.approve', $memo->id) }}" method="POST" onsubmit="return confirm('ยืนยันการอนุมัติเอกสารฉบับนี้?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all gap-1 border-0 shadow-sm shadow-emerald-600/10">
                                            <i class="fas fa-check"></i> อนุมัติ
                                        </button>
                                    </form>
                                    
                                    <button type="button" 
                                        onclick="openRejectModal('{{ route('internal_memo.reject', $memo->id) }}', '{{ $memo->memo_number }}')"
                                        class="inline-flex items-center justify-center px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-all gap-1 border-0 shadow-sm shadow-rose-600/10">
                                        <i class="fas fa-times"></i> ปฏิเสธ
                                    </button>
                                @endif

                                <a href="{{ route('internal_memo.show', $memo->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 rounded-lg transition-all border-0 shadow-sm" 
                                   title="เปิดดูรายละเอียดและพิมพ์เอกสาร">
                                    <i class="far fa-eye text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 bg-slate-50/30">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="far fa-folder-open text-3xl text-slate-300"></i>
                                <span class="text-sm font-medium">ยังไม่มีประวัติการสร้างคำขอใบบันทึกภายใน</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="rejectModal" class="fixed inset-0 z-50 hidden bg-slate-900/65 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden transform scale-95 transition-transform duration-300" id="modalCard">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-rose-500"></i>
                ปฏิเสธใบอนุมัติเอกสาร
            </h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 border-0 bg-transparent text-sm p-1">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>
        
        <form id="rejectForm" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <p class="text-xs text-slate-500 font-semibold mb-2">
                    เลขที่เอกสาร: <span id="modalMemoNumber" class="font-mono text-slate-800 font-bold bg-slate-100 px-1.5 py-0.5 rounded"></span>
                </p>
                <label for="reject_reason" class="block text-sm font-bold text-slate-700 mb-2">
                    ระบุเหตุผลที่ปฏิเสธการอนุมัติ <span class="text-rose-500">*</span>
                </label>
                <textarea name="reject_reason" id="reject_reason" required rows="4"
                    class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all text-sm text-slate-900 placeholder:text-slate-400"
                    placeholder="กรุณากรอกเหตุผลความจำเป็นในการปฏิเสธเอกสารฉบับนี้..."></textarea>
            </div>

            <div class="flex justify-end items-center gap-2 pt-2">
                <button type="button" onclick="closeRejectModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-all border-0">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl shadow-md shadow-rose-600/20 transition-all flex items-center gap-1 border-0">
                    <i class="fas fa-paper-plane text-[10px]"></i>
                    ยืนยันการปฏิเสธ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rejectModal = document.getElementById('rejectModal');
    const modalCard = document.getElementById('modalCard');
    const rejectForm = document.getElementById('rejectForm');
    const modalMemoNumber = document.getElementById('modalMemoNumber');
    const rejectReasonInput = document.getElementById('reject_reason');

    function openRejectModal(actionUrl, memoNumber) {
        rejectForm.setAttribute('action', actionUrl);
        modalMemoNumber.textContent = memoNumber;
        rejectModal.classList.remove('hidden');
        
        // ใส่เอฟเฟกต์ Fade in และ Scale-up เล็กน้อย
        setTimeout(() => {
            modalCard.classList.remove('scale-95');
            modalCard.classList.add('scale-100');
        }, 10);
    }

    function closeRejectModal() {
        card.classList.remove('scale-100');
        card.classList.add('scale-95');
        
        setTimeout(() => {
            rejectModal.classList.add('hidden');
            rejectForm.setAttribute('action', '');
            modalMemoNumber.textContent = '';
            rejectReasonInput.value = '';
        }, 150);
    }

    // ปิดหน้าต่างเมื่อคลิกพื้นที่ด้านนอก Modal
    rejectModal.addEventListener('click', function(e) {
        if (e.target === rejectModal) {
            closeRejectModal();
        }
    });
</script>
@endpush