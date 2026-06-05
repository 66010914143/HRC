@extends('layouts.app')

@section('title', __('messages.online_leave_approval') . ' - HRC System')

@section('content')
    <div class="min-h-screen p-6 max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ __('messages.online_leave_approval_center') }}</h1>
                <p class="text-sm text-slate-500">for HR & Management Approvals</p>
            </div>
            <a href="{{ route('dashboard') }}" class="flex items-center text-slate-600 hover:text-blue-600 transition text-sm font-medium">
                ← {{ __('messages.back_to_main_page') }}
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-slate-200">
            <div class="bg-blue-800 px-6 py-4 flex justify-between items-center">
                <h2 class="text-white font-semibold flex items-center gap-2">
                    📥 {{ __('messages.pending_leave_requests_list') }}
                </h2>
                <span class="bg-blue-900 text-white text-xs px-3 py-1 rounded-full font-bold">
                    {{ __('messages.total_all') }} {{ $leaveRequests->count() }} {{ __('messages.items_unit') }}
                </span>
            </div>

            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if($leaveRequests->isEmpty())
                    <div class="text-center py-12">
                        <span class="text-4xl">🎉</span>
                        <p class="text-slate-400 mt-2 text-sm italic">{{ __('messages.no_pending_leave_requests') }}</p>
                    </div>
                @endif
                
                @if(!$leaveRequests->isEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                                    <th class="py-3 px-4">{{ __('messages.employee_fullname') }}</th>
                                    <th class="py-3 px-4">{{ __('messages.department_position') }}</th>
                                    <th class="py-3 px-4">{{ __('messages.leave_type_label') }}</th>
                                    <th class="py-3 px-4">{{ __('messages.leave_date_period') }}</th>
                                    <th class="py-3 px-4">{{ __('messages.leave_reason_label') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('messages.permission_management') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                                @foreach($leaveRequests as $request)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-4 px-4 font-semibold text-slate-800">
                                            {{ $request->user ? $request->user->name : __('messages.employee_data_not_found') }}
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-xs font-medium text-slate-700">{{ $request->user ? $request->user->position : '-' }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $request->user ? $request->user->department : '-' }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                {{ $request->leave_type }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') }} ถึง {{ \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') }}</div>
                                            <div class="text-xs text-slate-400 italic">
                                                {{ __('messages.total_duration') }} ({{ \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1 }} {{ __('messages.days_unit') }})
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-xs max-w-xs truncate" title="{{ $request->reason }}">
                                            {{ $request->reason ?? '-' }}
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <div class="flex justify-center items-center gap-2">
                                                <button type="button" onclick="openLeaveModal({{ json_encode($request) }}, {{ json_encode($request->user) }})" 
                                                        class="px-2.5 py-1.5 bg-slate-600 text-white font-bold rounded hover:bg-slate-700 transition shadow text-xs flex items-center gap-1">
                                                    👁️ {{ __('messages.view_details') }}
                                                </button>

                                                <form action="{{ route('leave.approve', $request->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_approve_leave_alert') }}')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white font-bold rounded hover:bg-emerald-700 transition shadow text-xs">
                                                        ✅ {{ __('messages.approve_btn') }}
                                                    </button>
                                                </form>

                                                <button type="button" onclick="openRejectModal({{ $request->id }})" class="px-3 py-1.5 bg-rose-600 text-white font-bold rounded hover:bg-rose-700 transition shadow text-xs">
                                                    ❌ {{ __('messages.reject_btn') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <p class="mt-6 text-center text-gray-400 text-xs italic">HRC Internal Leave Approvals System © 2026</p>
    </div>

    {{-- Modal ปฏิเสธการลา --}}
    <div id="rejectModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden border border-slate-200 transform scale-95 transition-all">
            <div class="bg-rose-700 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="font-semibold text-base flex items-center gap-2">⚠️ {{ __('messages.specify_reject_reason_title') }}</h3>
                <button onclick="closeRejectModal()" class="text-slate-200 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <form id="rejectForm" action="" method="POST">
                @csrf
                @method('PATCH')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-2">{{ __('messages.reject_comment_label') }}</label>
                        <textarea name="comment" required rows="4" placeholder="{{ __('messages.reject_comment_placeholder') }}" 
                                  class="w-full px-3 py-2 text-sm text-slate-800 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium rounded-lg text-xs transition">
                        {{ __('messages.cancel_btn') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs transition shadow">
                        {{ __('messages.confirm_reject_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal ดูรายละเอียดการลา --}}
    <div id="leaveModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200 transform scale-95 transition-all">
            <div class="bg-slate-900 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="font-semibold text-base flex items-center gap-2">📄 {{ __('messages.electronic_leave_detail_title') }}</h3>
                <button onclick="closeLeaveModal()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <div class="p-6 space-y-4 text-sm text-slate-700">
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">{{ __('messages.leave_requester') }}</span>
                        <strong id="modalName" class="text-slate-900">-</strong>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">{{ __('messages.position_department_label') }}</span>
                        <strong id="modalPosition" class="text-slate-900">-</strong>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">{{ __('messages.leave_type_label') }}</label>
                        <div id="modalType" class="px-3 py-2 bg-slate-100 rounded-lg font-medium text-slate-900"></div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">{{ __('messages.requested_period') }}</label>
                        <div id="modalDate" class="px-3 py-2 bg-slate-100 rounded-lg font-medium text-slate-900"></div>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 block mb-1">{{ __('messages.leave_reason_necessity') }}</label>
                    <div id="modalReason" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 min-h-[60px] whitespace-pre-line"></div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 block mb-1">{{ __('messages.attached_evidence_image') }}</label>
                    <div id="modalEvidenceContainer" class="border border-dashed border-slate-300 rounded-lg p-2 flex justify-center bg-slate-50">
                        <span id="modalNoEvidence" class="text-slate-400 italic text-xs py-4">{{ __('messages.no_evidence_image_attached') }}</span>
                        <img id="modalEvidenceImg" src="" alt="หลักฐานการลา" class="max-h-48 rounded shadow-sm hidden cursor-pointer hover:opacity-90 transition" onclick="window.open(this.src)">
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 flex justify-end gap-2 border-t border-slate-100">
                <button onclick="closeLeaveModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium rounded-lg text-xs transition">
                    {{ __('messages.close_window_btn') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        function openRejectModal(requestId) {
            document.getElementById('rejectForm').action = `/leave/${requestId}/reject`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        function openLeaveModal(leave, user) {
            document.getElementById('modalName').innerText = user ? user.name : '{{ __('messages.employee_data_not_found') }}';
            document.getElementById('modalPosition').innerText = user ? `${user.position} (${user.department})` : '-';
            document.getElementById('modalType').innerText = leave.leave_type;
            document.getElementById('modalReason').innerText = leave.reason ? leave.reason : '{{ __('messages.no_reason_specified') }}';
            
            const start = new Date(leave.start_date).toLocaleDateString('th-TH');
            const end = new Date(leave.end_date).toLocaleDateString('th-TH');
            document.getElementById('modalDate').innerText = `${start} - ${end}`;

            const imgEl = document.getElementById('modalEvidenceImg');
            const noImgEl = document.getElementById('modalNoEvidence');
            
            if (leave.evidence_image) {
                imgEl.src = `/storage/leave_evidence/${leave.evidence_image}`;
                imgEl.classList.remove('hidden');
                noImgEl.classList.add('hidden');
            } else {
                imgEl.src = '';
                imgEl.classList.add('hidden');
                noImgEl.classList.remove('hidden');
            }

            document.getElementById('leaveModal').classList.remove('hidden');
        }

        function closeLeaveModal() {
            document.getElementById('leaveModal').classList.add('hidden');
        }

        window.onclick = function(event) {
            const leaveModal = document.getElementById('leaveModal');
            const rejectModal = document.getElementById('rejectModal');
            if (event.target == leaveModal) closeLeaveModal();
            if (event.target == rejectModal) closeRejectModal();
        }
    </script>
@endsection