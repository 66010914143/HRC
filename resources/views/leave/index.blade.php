@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            
            {{-- ส่วนหัวข้อหน้าเว็บ --}}
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h1 class="h3 fw-bold text-dark m-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i>{{ __('messages.leave_system_title') }}
                    </h1>
                    <p class="text-muted m-0 mt-1">
                        @if(auth()->user()->role == 'admin')
                            {{ __('messages.leave_admin_subtitle') }}
                        @else
                            {{ __('messages.leave_user_subtitle') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- ส่วนที่ 1: รายการรออนุมัติ (แสดงเมื่อมีรายการ pending ส่งมาถึงเราจริงๆ) --}}
            @if($pendingApprovals->count() > 0)
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5 border-start border-4 border-warning">
                <div class="card-header bg-white py-3 px-4">
                    <h6 class="fw-bold text-warning text-uppercase m-0">
                        <i class="fas fa-bell me-2"></i>{{ __('messages.pending_your_approval', ['count' => $pendingApprovals->count()]) }}
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small fw-bold">
                                <th class="ps-4">{{ __('messages.th_requester') }}</th>
                                <th>{{ __('messages.th_leave_type_date') }}</th>
                                <th>{{ __('messages.th_reason_evidence') }}</th>
                                <th class="text-center pe-4">{{ __('messages.th_manage') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovals as $leave)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $leave->user->name }} {{ $leave->user->last_name }}</div>
                                    <div class="text-muted extra-small">{{ $leave->user->position }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $leave->leave_type }}</div>
                                    <div class="text-muted small">{{ date('d/m/Y', strtotime($leave->start_date)) }} - {{ date('d/m/Y', strtotime($leave->end_date)) }}</div>
                                </td>
                                <td>
                                    <div class="text-muted small lh-sm">{{ Str::limit($leave->reason, 50) }}</div>
                                    @if($leave->evidence_image)
                                        <button type="button" onclick="viewImage('{{ asset('storage/leave_evidence/'.$leave->evidence_image) }}')" class="btn btn-link btn-sm p-0 text-decoration-none small"><i class="fas fa-image me-1"></i>{{ __('messages.view_evidence') }}</button>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <form action="{{ route('leave.approve', $leave->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm px-3 rounded-pill fw-bold">{{ __('messages.btn_approve') }}</button>
                                        </form>
                                        <form action="{{ route('leave.reject', $leave->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" onclick="return confirm('{{ __('messages.confirm_reject') }}')" class="btn btn-danger btn-sm px-3 rounded-pill fw-bold">{{ __('messages.btn_reject') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ส่วนที่ 2: ฟอร์มการลา (สำหรับ User) --}}
            @if(auth()->user()->role !== 'admin')
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <h6 class="fw-bold m-0"><i class="fas fa-edit me-2"></i>{{ __('messages.write_leave_title') }}</h6>
                </div>
                <div class="card-body p-4 bg-white">
                    <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">{{ __('messages.th_leave_type_date') }}</label>
                                <select name="leave_type" class="form-select rounded-3 shadow-sm border-gray" required>
                                    <option value="">{{ __('messages.select_leave_type') }}</option>
                                    <option value="ลาป่วย">{{ __('messages.leave_sick') }}</option>
                                    <option value="ลากิจ">{{ __('messages.leave_personal') }}</option>
                                    <option value="ลาพักร้อน">{{ __('messages.leave_vacation') }}</option>
                                    <option value="ลาอื่นๆ">{{ __('messages.leave_other') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">{{ __('messages.attach_evidence') }}</label>
                                <input type="file" name="evidence" class="form-control rounded-3 shadow-sm" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">{{ __('messages.start_date') }}</label>
                                <input type="date" name="start_date" class="form-control rounded-3 shadow-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">{{ __('messages.end_date') }}</label>
                                <input type="date" name="end_date" class="form-control rounded-3 shadow-sm" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-primary"><i class="fas fa-user-shield me-1"></i>ส่งคำขอการลาถึง (หัวหน้าแผนก)</label>
                                <select name="approver_id" class="form-select rounded-3 shadow-sm border-primary text-dark" required>
                                    <option value="">-- เลือกหัวหน้าแผนกผู้อนุมัติ --</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->name }} {{ $manager->last_name }} ({{ $manager->position ?? 'หัวหน้าแผนก' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">{{ __('messages.leave_reason_label') }}</label>
                                <textarea name="reason" rows="2" class="form-control rounded-3 shadow-sm" placeholder="{{ __('messages.leave_reason_placeholder') }}" required></textarea>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm">{{ __('messages.btn_submit_leave') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- ส่วนที่ 3: ศูนย์จัดการประวัติ (Admin Search & Tabs) --}}
            @if(auth()->user()->role == 'admin')
                <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 bg-white">
                    <div class="row g-3 align-items-center">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary"><i class="fas fa-search me-1"></i>{{ __('messages.search_staff_name') }}</label>
                            <div class="input-group border rounded-3 overflow-hidden shadow-sm">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-user"></i></span>
                                <input type="text" id="liveSearchInput" name="search" class="form-control border-0 shadow-none ps-2 py-2" placeholder="{{ __('messages.search_placeholder') }}" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-dark text-white border-0 pt-3 px-4">
                        <h6 class="fw-bold m-0"><i class="fas fa-folder-open me-2"></i>{{ __('messages.admin_leave_center') }}</h6>
                    </div>
                    
                    <ul class="nav nav-tabs border-bottom px-4 pt-2 bg-light bg-opacity-50" id="leaveAdminTabs">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold py-3 text-secondary transition-all" id="high-tab" data-bs-toggle="tab" data-bs-target="#high-pane">
                                <i class="fas fa-user-tie me-2 text-primary"></i>{{ __('messages.tab_high_level', ['count' => $highLevelLeaves->count()]) }}
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold py-3 text-secondary transition-all" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-pane">
                                <i class="fas fa-users me-2 text-success"></i>{{ __('messages.tab_general_level', ['count' => $generalLeaves->count()]) }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="leaveAdminTabsContent">
                        {{-- แท็บพนักงานระดับสูง --}}
                        <div class="tab-pane fade show active" id="high-pane">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="highLevelTable">
                                    <thead class="bg-light">
                                        <tr class="text-muted fs-7 fw-bold">
                                            <th class="ps-4 py-3">{{ __('messages.th_requester') }}</th>
                                            <th>{{ __('messages.th_leave_type_date') }}</th>
                                            <th>{{ __('messages.leave_reason_label') }}</th>
                                            <th class="text-center">{{ __('messages.th_approver') }}</th>
                                            <th class="text-center pe-4">{{ __('messages.th_manage') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($highLevelLeaves as $leave)
                                            <tr class="searchable-row">
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark employee-name">{{ $leave->user->name }} {{ $leave->user->last_name }}</div>
                                                    <div class="text-muted extra-small">{{ $leave->user->position }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-primary">{{ $leave->leave_type }}</div>
                                                    <div class="text-muted extra-small">{{ date('d/m/Y', strtotime($leave->start_date)) }} - {{ date('d/m/Y', strtotime($leave->end_date)) }}</div>
                                                </td>
                                                <td class="text-muted small">{{ Str::limit($leave->reason, 40) }}</td>
                                                <td class="text-center">
                                                    @if($leave->status == 'pending')
                                                        <span class="status-badge status-pending">{{ __('messages.status_pending') }}</span>
                                                    @elseif($leave->status == 'approved' || $leave->status == 'auto_approved')
                                                        <span class="status-badge status-approved">{{ $leave->status == 'auto_approved' ? __('messages.status_auto_approved') : __('messages.status_approved') }}</span>
                                                    @else
                                                        <span class="status-badge status-rejected">{{ __('messages.status_rejected') }}</span>
                                                    @endif
                                                    <div class="text-muted extra-small mt-1 italic">{{ $leave->approver ? $leave->approver->name : 'System' }}</div>
                                                </td>
                                                <td class="text-center pe-4">
                                                    <button type="button" onclick="viewMyLeaveDetail({{ json_encode($leave) }}, {{ json_encode($leave->approver) }}, {{ json_encode($leave->user) }})" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm"><i class="fas fa-eye me-1"></i>{{ __('messages.btn_view_leave') }}</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="no-data-row"><td colspan="5" class="text-center py-5 text-muted">{{ __('messages.no_data_high_level') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- แท็บพนักงานทั่วไป --}}
                        <div class="tab-pane fade" id="general-pane">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="generalLevelTable">
                                    <thead class="bg-light">
                                        <tr class="text-muted fs-7 fw-bold">
                                            <th class="ps-4 py-3">{{ __('messages.th_requester') }}</th>
                                            <th>{{ __('messages.th_leave_type_date') }}</th>
                                            <th>{{ __('messages.leave_reason_label') }}</th>
                                            <th class="text-center">{{ __('messages.th_approver') }}</th>
                                            <th class="text-center pe-4">{{ __('messages.th_manage') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($generalLeaves as $leave)
                                            <tr class="searchable-row">
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark employee-name">{{ $leave->user->name }} {{ $leave->user->last_name }}</div>
                                                    <div class="text-muted extra-small">{{ $leave->user->position }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-primary">{{ $leave->leave_type }}</div>
                                                    <div class="text-muted extra-small">{{ date('d/m/Y', strtotime($leave->start_date)) }} - {{ date('d/m/Y', strtotime($leave->end_date)) }}</div>
                                                </td>
                                                <td class="text-muted small">{{ Str::limit($leave->reason, 40) }}</td>
                                                <td class="text-center">
                                                    @if($leave->status == 'pending')
                                                        <span class="status-badge status-pending">{{ __('messages.status_pending') }}</span>
                                                    @elseif($leave->status == 'approved' || $leave->status == 'auto_approved')
                                                        <span class="status-badge status-approved">{{ __('messages.status_approved') }}</span>
                                                    @else
                                                        <span class="status-badge status-rejected">{{ __('messages.status_rejected') }}</span>
                                                    @endif
                                                    <div class="text-muted extra-small mt-1 italic">{{ $leave->approver ? $leave->approver->name : 'System' }}</div>
                                                </td>
                                                <td class="text-center pe-4">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="button" onclick="viewMyLeaveDetail({{ json_encode($leave) }}, {{ json_encode($leave->approver) }}, {{ json_encode($leave->user) }})" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm"><i class="fas fa-eye me-1"></i>{{ __('messages.btn_view_leave') }}</button>
                                                        <button type="button" onclick="printMyLeave({{ json_encode($leave) }})" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"><i class="fas fa-print me-1"></i>{{ __('messages.btn_print_pdf') }}</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="no-data-row"><td colspan="5" class="text-center py-5 text-muted">{{ __('messages.no_data_general_level') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
            
                {{-- ประวัติการลาของฉัน (สำหรับ User) --}}
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-uppercase text-primary m-0"><i class="fas fa-history me-2"></i>{{ __('messages.my_leave_history') }}</h6>
                        <span class="badge bg-primary rounded-pill fw-normal px-3 py-2">{{ __('messages.total_items_count', ['count' => $myLeaves->count()]) }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small fw-bold">
                                    <th class="ps-4">{{ __('messages.start_date') }}</th>
                                    <th>{{ __('messages.th_leave_type_date') }}</th>
                                    <th>{{ __('messages.th_status') }}</th>
                                    <th>{{ __('messages.th_approver') }}</th>
                                    <th class="text-center pe-4">{{ __('messages.th_manage') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myLeaves as $leave)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ date('d/m/Y', strtotime($leave->start_date)) }} - {{ date('d/m/Y', strtotime($leave->end_date)) }}</div>
                                    </td>
                                    <td class="fw-bold text-primary">{{ $leave->leave_type }}</td>
                                    <td>
                                        @if($leave->status == 'pending')
                                            <span class="status-badge status-pending">{{ __('messages.status_pending') }}</span>
                                        @elseif($leave->status == 'approved' || $leave->status == 'auto_approved')
                                            <span class="status-badge status-approved">{{ __('messages.status_approved') }}</span>
                                        @else
                                            <span class="status-badge status-rejected">{{ __('messages.status_rejected') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted extra-small italic">{{ $leave->approver ? $leave->approver->name : ($leave->status == 'auto_approved' ? __('messages.status_auto_approved') : '-') }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" onclick="viewMyLeaveDetail({{ json_encode($leave) }}, {{ json_encode($leave->approver) }}, {{ json_encode(auth()->user()) }})" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm"><i class="fas fa-eye me-1"></i>{{ __('messages.btn_detail') }}</button>
                                            <button type="button" onclick="printMyLeave({{ json_encode($leave) }})" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"><i class="fas fa-print me-1"></i>{{ __('messages.btn_print_pdf') }}</button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">{{ __('messages.no_my_leave_data') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Modal ส่องรายละเอียดใบลา --}}
<div id="myLeaveModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <i class="fas fa-file-alt text-primary me-2"></i>{{ __('messages.modal_leave_info') }}
                </h5>
                <button type="button" class="btn-close shadow-none" onclick="closeMyLeaveModal()"></button>
            </div>
            <div class="modal-body p-4" id="popupWebView">
                <div class="space-y-3">
                    <div class="bg-light p-3 rounded-4 mb-3">
                        <label class="text-muted extra-small d-block mb-1">{{ __('messages.modal_requester') }}</label>
                        <span id="popUser" class="fw-bold text-dark">-</span>
                    </div>
                    <div class="bg-light p-3 rounded-4 mb-3">
                        <label class="text-muted extra-small d-block mb-1">{{ __('messages.modal_type_period') }}</label>
                        <span id="popLeavePeriod" class="fw-bold text-primary d-block">-</span>
                        <span id="popLeaveDays" class="badge bg-primary mt-1">- วัน</span>
                    </div>
                    <div class="bg-light p-3 rounded-4 mb-3">
                        <label class="text-muted extra-small d-block mb-1">{{ __('messages.modal_approver') }}</label>
                        <span id="popApprover" class="fw-bold text-dark">-</span>
                    </div>
                    <div class="bg-light p-3 rounded-4 mb-3">
                        <label class="text-muted extra-small d-block mb-1">{{ __('messages.modal_status') }}</label>
                        <div id="popStatusBadge"></div>
                    </div>
                    <div id="popRejectReasonBlock" class="hidden bg-danger bg-opacity-10 p-3 rounded-4 mb-3 border border-danger border-opacity-10">
                        <label class="text-danger extra-small fw-bold d-block mb-1 italic">{{ __('messages.modal_reject_comment') }}</label>
                        <span id="popRejectReason" class="text-danger small">-</span>
                    </div>
                    <div class="bg-light p-3 rounded-4">
                        <label class="text-muted extra-small d-block mb-2">{{ __('messages.modal_evidence_img') }}</label>
                        <div id="popEvidenceBlock" class="text-center"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" onclick="closeMyLeaveModal()" class="btn btn-secondary w-100 rounded-pill fw-bold shadow-sm">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f0f2f5; font-family: 'Sarabun', sans-serif; }
    .status-badge { display: inline-flex; padding: 0.35rem 0.8rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; }
    .status-pending { background-color: #fff8e1; color: #b78103; border: 1px solid #ffe082; }
    .status-approved { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    .status-rejected { background-color: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
    .nav-tabs .nav-link { border: none; background: transparent; padding: 1rem 1.25rem; border-bottom: 3px solid transparent; color: #6c757d; }
    .nav-tabs .nav-link.active { border: none; background: transparent; border-bottom: 3px solid #0d6efd; color: #0d6efd !important; }
    .extra-small { font-size: 0.75rem; }
    .hidden { display: none !important; }
</style>

<script>
    // ระบบ Live Search ตรวจจับตอนพิมพ์เพื่อกรองข้อมูลทันที
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('liveSearchInput');
        if (searchInput) {
            // ฟังก์ชันหลักที่ทำงานเมื่อมีการพิมพ์ค้นหา
            function runSearchFilter() {
                const filterText = searchInput.value.trim().toLowerCase();
                filterTableRows('highLevelTable', filterText);
                filterTableRows('generalLevelTable', filterText);
            }

            // เรียกทำงานทันทีตอนโหลดหน้าเสร็จ เพื่อป้องกันปัญหารายการหายจากค่าว่างที่ติดมาจาก URL
            runSearchFilter();

            // ตรวจจับเมื่อผู้ใช้กำลังพิมพ์ในช่องค้นหา
            searchInput.addEventListener('input', runSearchFilter);
        }
    });

    function filterTableRows(tableId, filterText) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.querySelectorAll('.searchable-row');
        let hasVisibleRow = false;

        rows.forEach(row => {
            const nameEl = row.querySelector('.employee-name');
            if (nameEl) {
                const textValue = nameEl.textContent || nameEl.innerText;
                // หากคำค้นหาเป็นค่าว่าง หรือมีคำที่ตรงกับชื่อพนักงาน ให้แสดงผลแถวนั้นๆ
                if (filterText === "" || textValue.toLowerCase().indexOf(filterText) > -1) {
                    row.style.display = "";
                    hasVisibleRow = true;
                } else {
                    row.style.display = "none";
                }
            }
        });

        // จัดการกรณีค้นหาแล้วไม่เจอข้อมูลเลยในตารางนั้นๆ
        let noDataRow = table.querySelector('.no-data-row');
        if (!hasVisibleRow && rows.length > 0) {
            if (!noDataRow) {
                const tbody = table.querySelector('tbody');
                noDataRow = document.createElement('tr');
                noDataRow.className = 'no-data-row';
                noDataRow.innerHTML = `<td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูลพนักงานที่ค้นหา</td>`;
                tbody.appendChild(noDataRow);
            } else {
                noDataRow.style.display = "";
            }
        } else if (noDataRow) {
            noDataRow.style.display = "none";
        }
    }

    function viewMyLeaveDetail(leave, approver, user) {
        const fullName = `${user.name} ${user.last_name || ''}`;
        document.getElementById('popUser').innerText = fullName;
        document.getElementById('popApprover').innerText = approver ? approver.name : (leave.status === 'auto_approved' ? "{{ __('messages.status_auto_approved') }}" : '-');
        
        const badgeEl = document.getElementById('popStatusBadge');
        const popRejectBlock = document.getElementById('popRejectReasonBlock');
        popRejectBlock.classList.add('hidden');

        if (leave.status === 'pending') {
            badgeEl.className = "status-badge status-pending"; badgeEl.innerText = "⏳ {{ __('messages.status_pending') }}";
        } else if (leave.status === 'approved' || leave.status === 'auto_approved') {
            badgeEl.className = "status-badge status-approved"; badgeEl.innerText = "✅ {{ __('messages.status_approved') }}";
        } else if (leave.status === 'rejected') {
            badgeEl.className = "status-badge status-rejected"; badgeEl.innerText = "❌ {{ __('messages.status_rejected') }}";
            document.getElementById('popRejectReason').innerText = leave.comment || 'ไม่ได้ระบุสาเหตุ';
            popRejectBlock.classList.remove('hidden');
        }

        const start = new Date(leave.start_date);
        const end = new Date(leave.end_date);
        const startStr = start.toLocaleDateString('th-TH');
        const endStr = end.toLocaleDateString('th-TH');
        const diffDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1;

        document.getElementById('popLeavePeriod').innerText = `${startStr} - ${endStr}`;
        let daysTemplate = "{{ __('messages.modal_days_count', ['count' => ':dayCount']) }}";
        document.getElementById('popLeaveDays').innerText = daysTemplate.replace(':dayCount', diffDays);

        const evidenceBlock = document.getElementById('popEvidenceBlock');
        if (leave.evidence_image) {
            evidenceBlock.innerHTML = `<img src="{{ asset('storage/leave_evidence/') }}/${leave.evidence_image}" class="img-fluid rounded shadow-sm" style="max-height:200px">`;
        } else {
            evidenceBlock.innerHTML = `<span class="text-muted extra-small italic">{{ __('messages.no_evidence_img') }}</span>`;
        }

        var myModal = new bootstrap.Modal(document.getElementById('myLeaveModal'));
        myModal.show();
    }

    function printMyLeave(leave) {
        let printUrl = "{{ url('leave/print') }}/" + leave.id;
        window.open(printUrl, '_blank');
    }

    function closeMyLeaveModal() {
        var modalEl = document.getElementById('myLeaveModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    function viewImage(url) {
        window.open(url, '_blank');
    }
</script>
@endsection