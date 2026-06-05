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
                    <p class="text-muted m-0 mt-1">{{ __('messages.welfare_system_subtitle') }}</p>
                </div>
                <a href="{{ route('welfare.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 transition-all">
                    <i class="fas fa-plus me-2"></i>{{ __('messages.welfare_create_btn') }}
                </a>
            </div>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-uppercase text-primary m-0" style="letter-spacing: 0.5px;">
                        <i class="fas fa-history me-2"></i>{{ __('messages.welfare_history_title') }}
                    </h6>
                    <span class="badge bg-primary rounded-pill fw-normal px-3">{{ __('messages.welfare_items_count', ['count' => $welfareRequests->count()]) }}</span>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted fs-7 fw-bold">
                                    <th class="ps-4 py-3" style="width: 15%">{{ __('messages.welfare_history_th_date') }}</th>
                                    <th style="width: 25%">{{ __('messages.welfare_history_th_employee') }}</th>
                                    <th style="width: 25%">{{ __('messages.welfare_history_th_subject') }}</th>
                                    <th class="text-end" style="width: 15%">{{ __('messages.welfare_history_th_amount') }}</th>
                                    <th class="text-center" style="width: 15%">{{ __('messages.welfare_history_th_status') }}</th>
                                    <th class="ps-3" style="width: 20%">{{ __('messages.welfare_history_th_remark') }}</th>
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
                                        <span class="badge bg-light text-dark border fw-normal py-1 px-2">
                                            <i class="fas fa-tag me-1 text-primary small"></i>{{ $request->type }}
                                        </span>
                                    </td>
                                    
                                    <td class="text-end fw-bold text-dark pe-3">
                                        {{ number_format($request->amount, 2) }}
                                    </td>
                                    
                                    <td class="text-center">
                                        @if($request->status == 'pending')
                                            <span class="status-badge status-pending">
                                                <i class="fas fa-spinner fa-spin me-1 text-warning"></i>{{ __('messages.welfare_status_pending') }}
                                            </span>
                                        @elseif($request->status == 'approved')
                                            <span class="status-badge status-approved">
                                                <i class="fas fa-check-circle me-1 text-success"></i>{{ __('messages.welfare_status_approved') }}
                                            </span>
                                        @elseif($request->status == 'rejected')
                                            <span class="status-badge status-rejected">
                                                <i class="fas fa-times-circle me-1 text-danger"></i>{{ __('messages.welfare_status_rejected') }}
                                            </span>
                                        @else
                                            <span class="status-badge status-default">{{ $request->status }}</span>
                                        @endif
                                    </td>
                                    
                                    <td class="ps-3 text-muted small">
                                        @if($request->status == 'rejected' && $request->remark)
                                            <span class="text-danger fw-bold"><i class="fas fa-comment-dots me-1"></i>{{ __('messages.welfare_reason_label') }}</span> {{ $request->remark }}
                                        @else
                                            {{ $request->description ?: '-' }}
                                        @endif
                                    </td>

                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('welfare.show', $request->id) }}" 
                                               class="btn btn-outline-primary btn-action" 
                                               title="ดูรายละเอียด/ปริ้นใบเบิก">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($request->attachment)
                                            <a href="{{ asset('storage/' . $request->attachment) }}" 
                                               target="_blank"
                                               class="btn btn-outline-info btn-action" 
                                               title="ดูหลักฐานการจ่าย">
                                                <i class="fas fa-paperclip"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                            <h6 class="text-muted fw-bold">{{ __('messages.welfare_empty_title') }}</h6>
                                            <p class="text-muted small">{{ __('messages.welfare_empty_subtitle') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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
</style>
@endsection