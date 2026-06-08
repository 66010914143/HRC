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
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600 font-medium">
                    @forelse($myMemos as $memo)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-slate-900 font-bold">
                            {{ $memo->memo_number }}
                        </td>
                        <td class="px-6 py-4 max-w-xs truncate text-slate-800 font-semibold">
                            {{ $memo->subject }}
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
                                <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> ปฏิเสธการอนุมัติ
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> รอการอนุมัติ
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 bg-slate-50/30">
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
@endsection