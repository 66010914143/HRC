@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h1 class="h3 fw-bold text-dark m-0">
                        <i class="fas fa-wallet text-primary me-2"></i>{{ __('messages.welfare_system_title') }}
                    </h1>
                    <p class="text-muted m-0 mt-1">
                        @if($isAdminView)
                            {{ __('messages.welfare_history_admin_subtitle') }}
                        @else
                            {{ __('messages.welfare_history_subtitle') ?? 'ประวัติรายการเบิกสวัสดิการของคุณ' }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('welfare.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 transition-all">
                    <i class="fas fa-plus me-2"></i>{{ __('messages.welfare_create_btn') }}
                </a>
            </div>

            @if($isAdminView)
            <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white">
                <div class="card-body p-4">
                    <form action="{{ route('welfare.history') }}" method="GET" class="row g-3 align-items-center">
                        <div class="col-12 col-md-9">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" 
                                       placeholder="{{ __('messages.welfare_history_search_placeholder') }}" 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                {{ __('messages.welfare_history_search_btn') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white border-bottom pt-4 px-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                            <i class="fas fa-history fa-lg"></i>
                        </div>
                        <h5 class="fw-bold text-dark m-0">
                            @if($isAdminView)
                                {{ __('messages.welfare_history_admin_header') }}
                            @else
                                {{ __('messages.welfare_history_header') ?? 'รายการประวัติการเบิกสวัสดิการ' }}
                            @endif
                        </h5>
                    </div>
                    
                    @if($isAdminView)
                    <ul class="nav nav-pills card-header-pills" id="welfareTabs" role="tablist">
                        <li class="nav-item me-2" role="presentation">
                            <button class="nav-link active fw-bold px-4 py-2-5 rounded-3" id="high-level-tab" data-bs-toggle="tab" data-bs-target="#high-level" type="button" role="tab" aria-controls="high-level" aria-selected="true">
                                <i class="fas fa-user-tie me-2"></i>{{ __('messages.welfare_history_tab_high_level') }} 
                                <span class="badge rounded-pill bg-white text-primary ms-2 px-2 pb-1" style="font-size: 0.75rem; vertical-align: middle;">{{ $highLevelRequests->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-secondary px-4 py-2-5 rounded-3" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="false">
                                <i class="fas fa-users me-2"></i>{{ __('messages.welfare_history_tab_general') }} 
                                <span class="badge rounded-pill bg-light text-secondary border ms-2 px-2 pb-1" style="font-size: 0.75rem; vertical-align: middle;">{{ $generalRequests->count() }}</span>
                            </button>
                        </li>
                    </ul>
                    @endif
                </div>
                
                <div class="card-body p-0 bg-white">
                    <div class="tab-content" id="welfareTabsContent">
                        
                        @if($isAdminView)
                            <div class="tab-pane fade show active" id="high-level" role="tabpanel" aria-labelledby="high-level-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr class="text-muted fs-7 fw-bold">
                                                <th class="ps-4 py-3" style="width: 15%">{{ __('messages.welfare_history_th_date') }}</th>
                                                <th style="width: 20%">{{ __('messages.welfare_history_th_employee') }}</th>
                                                <th style="width: 35%">{{ __('messages.welfare_history_th_subject') }}</th>
                                                <th class="text-end" style="width: 15%">{{ __('messages.welfare_history_th_amount') }}</th>
                                                <th class="text-center" style="width: 15%">{{ __('messages.welfare_history_th_status') }}</th>
                                                <th class="text-center pe-4" style="width: 10%">{{ __('messages.welfare_history_th_manage') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($highLevelRequests as $request)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark">{{ $request->created_at->format('d/m/Y') }}</div>
                                                    <div class="text-muted small">{{ $request->created_at->format('H:i') }} {{ __('messages.welfare_time_unit') }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $request->user->name ?? '-' }}</div>
                                                    <div class="text-muted small">{{ $request->user->employee_id ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark mb-1">{{ $request->title }}</div>
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                        <span class="badge bg-light text-dark border fw-normal py-1 px-2">
                                                            <i class="fas fa-tag me-1 text-primary small"></i>{{ $request->type }}
                                                        </span>
                                                        @if($request->status == 'rejected' && $request->remark)
                                                            <button type="button" class="btn btn-link p-0 text-danger text-decoration-none small bg-danger bg-opacity-10 px-2 py-1 rounded-2 border-0 align-items-center d-inline-flex text-start btn-remark-popup" data-bs-toggle="modal" data-bs-target="#remarkModal{{ $request->id }}">
                                                                <i class="fas fa-comment-dots me-1"></i><strong>{{ __('messages.welfare_reason_label') }}</strong> <span class="text-truncate ms-1" style="max-width: 150px;">{{ $request->remark }}</span>
                                                            </button>

                                                            <div class="modal fade" id="remarkModal{{ $request->id }}" tabindex="-1" aria-labelledby="remarkModalLabel{{ $request->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content border-0 shadow rounded-4">
                                                                        <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                                                                            <h5 class="modal-title fw-bold" id="remarkModalLabel{{ $request->id }}"><i class="fas fa-comment-dots me-2"></i>{{ __('messages.welfare_reason_label') }}</h5>
                                                                            <button type="button" class="btn-close btn-close-white" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body p-4 text-dark fs-6" style="white-space: pre-wrap;">{{ $request->remark }}</div>
                                                                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @elseif($request->description)
                                                            <button type="button" class="btn btn-link p-0 text-muted text-decoration-none small border-0 bg-transparent align-items-center d-inline-flex text-start btn-description-popup" data-bs-toggle="modal" data-bs-target="#descModal{{ $request->id }}">
                                                                <i class="fas fa-info-circle me-1 text-secondary"></i><span class="text-truncate" style="max-width: 250px;">{{ $request->description }}</span>
                                                            </button>

                                                            <div class="modal fade" id="descModal{{ $request->id }}" tabindex="-1" aria-labelledby="descModalLabel{{ $request->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content border-0 shadow rounded-4">
                                                                        <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                                                                            <h5 class="modal-title fw-bold" id="descModalLabel{{ $request->id }}"><i class="fas fa-info-circle me-2"></i>รายละเอียดเพิ่มเติม</h5>
                                                                            <button type="button" class="btn-close btn-close-white" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body p-4 text-dark fs-6" style="white-space: pre-wrap;">{{ $request->description }}</div>
                                                                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold text-dark pe-3">
                                                    {{ number_format($request->amount, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    @if($request->status == 'pending')
                                                        <span class="status-badge status-pending">
                                                            <i class="fas fa-spinner fa-spin me-1 text-warning"></i>{{ __('messages.welfare_history_status_pending') }}
                                                        </span>
                                                    @elseif($request->status == 'approved')
                                                        <span class="status-badge status-approved">
                                                            <i class="fas fa-check-circle me-1 text-success"></i>{{ __('messages.welfare_history_status_approved') }}
                                                        </span>
                                                    @elseif($request->status == 'rejected')
                                                        <span class="status-badge status-rejected">
                                                            <i class="fas fa-times-circle me-1 text-danger"></i>{{ __('messages.welfare_history_status_rejected') }}
                                                        </span>
                                                    @else
                                                        <span class="status-badge status-default">{{ $request->status }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center pe-4">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="{{ route('welfare.show', $request->id) }}" class="btn btn-outline-primary btn-action" title="{{ __('messages.welfare_history_read_more') }}">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="py-4">
                                                        <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                                        <h6 class="text-muted fw-bold">{{ __('messages.welfare_history_empty_high_level') }}</h6>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="general" role="tabpanel" aria-labelledby="general-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr class="text-muted fs-7 fw-bold">
                                                <th class="ps-4 py-3" style="width: 15%">{{ __('messages.welfare_history_th_date') }}</th>
                                                <th style="width: 20%">{{ __('messages.welfare_history_th_employee') }}</th>
                                                <th style="width: 35%">{{ __('messages.welfare_history_th_subject') }}</th>
                                                <th class="text-end" style="width: 15%">{{ __('messages.welfare_history_th_amount') }}</th>
                                                <th class="text-center" style="width: 15%">{{ __('messages.welfare_history_th_status') }}</th>
                                                <th class="text-center pe-4" style="width: 10%">{{ __('messages.welfare_history_th_manage') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($generalRequests as $request)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark">{{ $request->created_at->format('d/m/Y') }}</div>
                                                    <div class="text-muted small">{{ $request->created_at->format('H:i') }} {{ __('messages.welfare_time_unit') }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $request->user->name ?? '-' }}</div>
                                                    <div class="text-muted small">{{ $request->user->employee_id ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark mb-1">{{ $request->title }}</div>
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                        <span class="badge bg-light text-dark border fw-normal py-1 px-2">
                                                            <i class="fas fa-tag me-1 text-primary small"></i>{{ $request->type }}
                                                        </span>
                                                        @if($request->status == 'rejected' && $request->remark)
                                                            <button type="button" class="btn btn-link p-0 text-danger text-decoration-none small bg-danger bg-opacity-10 px-2 py-1 rounded-2 border-0 align-items-center d-inline-flex text-start btn-remark-popup" data-bs-toggle="modal" data-bs-target="#remarkModal{{ $request->id }}">
                                                                <i class="fas fa-comment-dots me-1"></i><strong>{{ __('messages.welfare_reason_label') }}</strong> <span class="text-truncate ms-1" style="max-width: 150px;">{{ $request->remark }}</span>
                                                            </button>

                                                            <div class="modal fade" id="remarkModal{{ $request->id }}" tabindex="-1" aria-labelledby="remarkModalLabel{{ $request->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content border-0 shadow rounded-4">
                                                                        <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                                                                            <h5 class="modal-title fw-bold" id="remarkModalLabel{{ $request->id }}"><i class="fas fa-comment-dots me-2"></i>{{ __('messages.welfare_reason_label') }}</h5>
                                                                            <button type="button" class="btn-close btn-close-white" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body p-4 text-dark fs-6" style="white-space: pre-wrap;">{{ $request->remark }}</div>
                                                                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @elseif($request->description)
                                                            <button type="button" class="btn btn-link p-0 text-muted text-decoration-none small border-0 bg-transparent align-items-center d-inline-flex text-start btn-description-popup" data-bs-toggle="modal" data-bs-target="#descModal{{ $request->id }}">
                                                                <i class="fas fa-info-circle me-1 text-secondary"></i><span class="text-truncate" style="max-width: 250px;">{{ $request->description }}</span>
                                                            </button>

                                                            <div class="modal fade" id="descModal{{ $request->id }}" tabindex="-1" aria-labelledby="descModalLabel{{ $request->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content border-0 shadow rounded-4">
                                                                        <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                                                                            <h5 class="modal-title fw-bold" id="descModalLabel{{ $request->id }}"><i class="fas fa-info-circle me-2"></i>รายละเอียดเพิ่มเติม</h5>
                                                                            <button type="button" class="btn-close btn-close-white" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body p-4 text-dark fs-6" style="white-space: pre-wrap;">{{ $request->description }}</div>
                                                                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold text-dark pe-3">
                                                    {{ number_format($request->amount, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    @if($request->status == 'pending')
                                                        <span class="status-badge status-pending">
                                                            <i class="fas fa-spinner fa-spin me-1 text-warning"></i>{{ __('messages.welfare_history_status_pending') }}
                                                        </span>
                                                    @elseif($request->status == 'approved')
                                                        <span class="status-badge status-approved">
                                                            <i class="fas fa-check-circle me-1 text-success"></i>{{ __('messages.welfare_history_status_approved') }}
                                                        </span>
                                                    @elseif($request->status == 'rejected')
                                                        <span class="status-badge status-rejected">
                                                            <i class="fas fa-times-circle me-1 text-danger"></i>{{ __('messages.welfare_history_status_rejected') }}
                                                        </span>
                                                    @else
                                                        <span class="status-badge status-default">{{ $request->status }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center pe-4">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="{{ route('welfare.show', $request->id) }}" class="btn btn-outline-primary btn-action" title="{{ __('messages.welfare_history_read_more') }}">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="py-4">
                                                        <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                                        <h6 class="text-muted fw-bold">{{ __('messages.welfare_history_empty_high_level') }}</h6>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="tab-pane fade show active" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr class="text-muted fs-7 fw-bold">
                                                <th class="ps-4 py-3" style="width: 15%">{{ __('messages.welfare_history_th_date') }}</th>
                                                <th style="width: 40%">{{ __('messages.welfare_history_th_subject') }}</th>
                                                <th class="text-end" style="width: 15%">{{ __('messages.welfare_history_th_amount') }}</th>
                                                <th class="text-center" style="width: 20%">{{ __('messages.welfare_history_th_status') }}</th>
                                                <th class="text-center pe-4" style="width: 10%">{{ __('messages.welfare_history_th_manage') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($welfareRequests as $request)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark">{{ $request->created_at->format('d/m/Y') }}</div>
                                                    <div class="text-muted small">{{ $request->created_at->format('H:i') }} {{ __('messages.welfare_time_unit') }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark mb-1">{{ $request->title }}</div>
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                        <span class="badge bg-light text-dark border fw-normal py-1 px-2">
                                                            <i class="fas fa-tag me-1 text-primary small"></i>{{ $request->type }}
                                                        </span>
                                                        @if($request->status == 'rejected' && $request->remark)
                                                            <button type="button" class="btn btn-link p-0 text-danger text-decoration-none small bg-danger bg-opacity-10 px-2 py-1 rounded-2 border-0 align-items-center d-inline-flex text-start btn-remark-popup" data-bs-toggle="modal" data-bs-target="#remarkModalUser{{ $request->id }}">
                                                                <i class="fas fa-comment-dots me-1"></i><strong>{{ __('messages.welfare_reason_label') }}</strong> <span class="text-truncate ms-1" style="max-width: 150px;">{{ $request->remark }}</span>
                                                            </button>

                                                            <div class="modal fade" id="remarkModalUser{{ $request->id }}" tabindex="-1" aria-labelledby="remarkModalLabelUser{{ $request->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content border-0 shadow rounded-4">
                                                                        <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                                                                            <h5 class="modal-title fw-bold" id="remarkModalLabelUser{{ $request->id }}"><i class="fas fa-comment-dots me-2"></i>{{ __('messages.welfare_reason_label') }}</h5>
                                                                            <button type="button" class="btn-close btn-close-white" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body p-4 text-dark fs-6" style="white-space: pre-wrap;">{{ $request->remark }}</div>
                                                                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @elseif($request->description)
                                                            <button type="button" class="btn btn-link p-0 text-muted text-decoration-none small border-0 bg-transparent align-items-center d-inline-flex text-start btn-description-popup" data-bs-toggle="modal" data-bs-target="#descModalUser{{ $request->id }}">
                                                                <i class="fas fa-info-circle me-1 text-secondary"></i><span class="text-truncate" style="max-width: 250px;">{{ $request->description }}</span>
                                                            </button>

                                                            <div class="modal fade" id="descModalUser{{ $request->id }}" tabindex="-1" aria-labelledby="descModalUserLabel{{ $request->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content border-0 shadow rounded-4">
                                                                        <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                                                                            <h5 class="modal-title fw-bold" id="descModalUserLabel{{ $request->id }}"><i class="fas fa-info-circle me-2"></i>รายละเอียดเพิ่มเติม</h5>
                                                                            <button type="button" class="btn-close btn-close-white" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body p-4 text-dark fs-6" style="white-space: pre-wrap;">{{ $request->description }}</div>
                                                                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold text-dark pe-3">
                                                    {{ number_format($request->amount, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    @if($request->status == 'pending')
                                                        <span class="status-badge status-pending">
                                                            <i class="fas fa-spinner fa-spin me-1 text-warning"></i>{{ __('messages.welfare_history_status_pending') }}
                                                        </span>
                                                    @elseif($request->status == 'approved')
                                                        <span class="status-badge status-approved">
                                                            <i class="fas fa-check-circle me-1 text-success"></i>{{ __('messages.welfare_history_status_approved') }}
                                                        </span>
                                                    @elseif($request->status == 'rejected')
                                                        <span class="status-badge status-rejected">
                                                            <i class="fas fa-times-circle me-1 text-danger"></i>{{ __('messages.welfare_history_status_rejected') }}
                                                        </span>
                                                    @else
                                                        <span class="status-badge status-default">{{ $request->status }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center pe-4">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="{{ route('welfare.show', $request->id) }}" class="btn btn-outline-primary btn-action" title="{{ __('messages.welfare_history_read_more') }}">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="py-4">
                                                        <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                                        <h6 class="text-muted fw-bold">{{ __('messages.welfare_no_requests') ?? 'ไม่พบประวัติการเบิกสวัสดิการของคุณ' }}</h6>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body { background-color: #f0f2f5; font-family: 'Sarabun', sans-serif; }
    
    /* Table Styling */
    .table thead th { border-top: none; text-transform: none; font-size: 0.85rem; border-bottom: 2px solid #edf2f7; }
    .table td { padding: 1.1rem 0.5rem; border-bottom: 1px solid #f0f0f0; }
    .table tbody tr:hover { background-color: rgba(0, 123, 255, 0.02); }
    
    /* ปรับแต่งดีไซน์ Nav Pills เมนูแท็บสลับฝั่งพนักงานให้สวยสะอาดตาและเห็นชัดขึ้น */
    .nav-pills .nav-link { color: #6c757d; background: #f8f9fa; border: 1px solid #e9ecef; margin-bottom: 5px; transition: all 0.2s ease; }
    .nav-pills .nav-link:hover { background: #f1f3f5; color: #495057; }
    .nav-pills .nav-link.active { color: #fff !important; background-color: #0d6efd !important; border-color: #0d6efd; box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2); }
    .py-2-5 { padding-top: 0.65rem; padding-bottom: 0.65rem; }
    
    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .status-pending { background-color: #fff8e1; color: #b78103; border-color: #ffe082; }
    .status-approved { background-color: #e8f5e9; color: #2e7d32; border-color: #a5d6a7; }
    .status-rejected { background-color: #ffebee; color: #c62828; border-color: #ef9a9a; }
    .status-default { background-color: #f5f5f5; color: #616161; }

    /* Action Buttons */
    .btn-action { 
        width: 36px; 
        height: 36px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    
    .transition-all { transition: all 0.3s ease; }
    .gap-2 { gap: 0.5rem; }
    .fs-7 { font-size: 0.85rem; }

    /* CSS สำหรับปุ่ม Popup */
    .btn-remark-popup:hover { background-color: rgba(220, 53, 69, 0.15) !important; cursor: pointer; }
    .btn-description-popup:hover { color: #0d6efd !important; cursor: pointer; }
</style>
@endsection