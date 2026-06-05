@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3">
                    <i class="fas fa-clipboard-check fa-2x text-primary"></i>
                </div>
                <div>
                    <h1 class="h3 fw-bold text-dark m-0">{{ __('messages.welfare_pending_approval_title') }}</h1>
                    <p class="text-muted m-0">{{ __('messages.welfare_pending_approval_subtitle') }}</p>
                </div>
                @if($pendingRequests->count() > 0)
                    <div class="ms-auto">
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold">
                            {{ __('messages.pending_consideration') }} {{ $pendingRequests->count() }} {{ __('messages.items_unit') }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="row g-4">
                @forelse($pendingRequests as $request)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden request-card transition-all">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-auto mb-3 mb-md-0">
                                        <div class="bg-light p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-user text-secondary"></i>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <h5 class="fw-bold text-dark mb-1">{{ $request->title }}</h5>
                                        <div class="d-flex flex-wrap gap-3 small text-muted">
                                            <span><i class="fas fa-user-edit me-1"></i> {{ __('messages.welfare_requester_label') }}: {{ $request->user->name }}</span>
                                            <span><i class="fas fa-calendar-alt me-1"></i> {{ __('messages.submission_date_label') }}: {{ $request->created_at->format('d/m/Y H:i น.') }}</span>
                                            <span class="badge bg-light text-primary border fw-normal"><i class="fas fa-tag me-1"></i> {{ $request->type }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-auto text-md-end mt-3 mt-md-0">
                                        <div class="h4 fw-bold text-primary mb-0">{{ number_format($request->amount, 2) }} ฿</div>
                                    </div>
                                </div>
                                
                                <hr class="my-4 opacity-50">
                                
                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="{{ route('welfare.show', $request->id) }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                                        <i class="fas fa-file-alt me-2"></i>{{ __('messages.request_details_btn') }}
                                    </a>
                                    
                                    @if($request->attachment)
                                        <button type="button" 
                                                class="btn btn-outline-info rounded-pill px-4 fw-bold" 
                                                onclick="viewAttachment('{{ asset('storage/' . $request->attachment) }}')">
                                            <i class="fas fa-image me-2"></i>{{ __('messages.view_welfare_evidence_btn') }}
                                        </button>
                                    @endif

                                    {{-- ฟอร์มอนุมัติ --}}
                                    <form action="{{ route('welfare.approve', $request->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="return confirm('{{ __('messages.confirm_welfare_approve_alert') }}')">
                                            <i class="fas fa-check-circle me-2"></i>{{ __('messages.approve_request_btn') }}
                                        </button>
                                    </form>
                                    
                                    {{-- ปุ่มเปิด Modal ปฏิเสธ --}}
                                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" onclick="openRejectModal({{ $request->id }})">
                                        <i class="fas fa-times-circle me-2"></i>{{ __('messages.reject_welfare_btn') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="bg-white rounded-4 shadow-sm py-5">
                            <i class="fas fa-check-double fa-4x text-light mb-3"></i>
                            <h5 class="text-muted fw-bold">{{ __('messages.no_welfare_pending_requests') }}</h5>
                            <p class="text-muted small">{{ __('messages.all_requests_processed_desc') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- 1. Modal ดูรูปภาพหลักฐาน --}}
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 bg-white pt-4 px-4 pb-2">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-2 rounded-3 me-2">
                        <i class="fas fa-file-invoice text-info"></i>
                    </div>
                    {{ __('messages.welfare_evidence_title') }}
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 d-flex justify-content-center align-items-center bg-light bg-opacity-50" style="min-height: 450px;">
                <div class="attachment-frame shadow-sm rounded-3 overflow-hidden bg-white p-2">
                    <img src="" id="attachmentImage" class="img-fluid rounded-2 transition-all" alt="Attachment Preview">
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-2 bg-white justify-content-between">
                <button type="button" class="btn btn-light px-4 rounded-pill fw-bold text-muted" data-bs-dismiss="modal">{{ __('messages.close_window_btn') }}</button>
                <a href="" id="downloadLink" class="btn btn-info px-4 rounded-pill fw-bold text-white shadow-sm" download>
                    <i class="fas fa-download me-2"></i>{{ __('messages.download_evidence_btn') }}
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 2. Modal กรอกเหตุผลการปฏิเสธ --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ __('messages.specify_welfare_reject_title') }}
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <p class="text-muted small">{{ __('messages.welfare_reject_notice_desc') }}</p>
                    <textarea name="admin_remark" class="form-control rounded-4 border-light-subtle shadow-sm" rows="4" placeholder="{{ __('messages.welfare_reject_placeholder') }}" required></textarea>
                </div>
                <div class="modal-footer border-0 p-4 pt-2">
                    <button type="button" class="btn btn-light px-4 rounded-pill fw-bold text-muted" data-bs-dismiss="modal">{{ __('messages.cancel_btn') }}</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i>{{ __('messages.confirm_reject_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
    function viewAttachment(url) {
        const image = document.getElementById('attachmentImage');
        const download = document.getElementById('downloadLink');
        image.style.opacity = '0';
        image.src = url;
        download.href = url;
        const myModal = new bootstrap.Modal(document.getElementById('attachmentModal'));
        myModal.show();
        image.onload = function() { image.style.opacity = '1'; };
    }

    function openRejectModal(id) {
        const form = document.getElementById('rejectForm');
        form.action = `/welfare/reject/${id}`; 
        
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        rejectModal.show();
    }
</script>

<style>
    body { background-color: #f8f9fa; font-family: 'Sarabun', sans-serif; }
    .request-card { border-left: 5px solid #ffc107 !important; }
    .request-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
    .attachment-frame { display: inline-block; max-width: 100%; text-align: center; }
    #attachmentImage { max-height: 75vh; object-fit: contain; display: block; margin: 0 auto; }
    .transition-all { transition: all 0.3s ease; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    textarea:focus { border-color: #dc3545 !important; box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.1) !important; }
</style>
@endsection