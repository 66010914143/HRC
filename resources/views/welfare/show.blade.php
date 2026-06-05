@extends('layouts.app')

@section('content')
{{-- ส่วนที่แสดงบนหน้าจอปกติ --}}
<div class="container py-3 d-print-none">
    <div class="d-flex justify-content-between">
        <a href="{{ route('welfare.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('messages.welfare_back_home') }}</a>
        <button onclick="window.print()" class="btn btn-sm btn-dark">{{ __('messages.welfare_print_pdf') }}</button>
    </div>
</div>

{{-- ส่วนที่จะปริ้นออกมา (Full Page) --}}
<div class="print-page-container">
    <div class="print-header text-center">
        <h4 class="fw-bold mb-1">{{ __('messages.welfare_form_title') }}</h4>
        <p class="small text-muted mb-0">HRC SYSTEM - Welfare Requisition Form</p>
    </div>

    <hr class="my-4">

    <div class="row mb-4">
        <div class="col-6">
            <p class="mb-1"><strong>{{ __('messages.welfare_requester') }}</strong> {{ $welfareRequest->user->name }}</p>
            <p class="mb-0"><strong>{{ __('messages.welfare_department') }}</strong> {{ $welfareRequest->user->department ?? '-' }}</p>
        </div>
        <div class="col-6 text-end">
            <p class="mb-1"><strong>{{ __('messages.welfare_doc_no') }}</strong> #REF-{{ str_pad($welfareRequest->id, 5, '0', STR_PAD_LEFT) }}</p>
            <p class="mb-0"><strong>{{ __('messages.welfare_request_date') }}</strong> {{ $welfareRequest->created_at->format('d/m/Y H:i') }} {{ __('messages.welfare_time_unit') }}</p>
        </div>
    </div>

    <table class="table table-print mb-4">
        <thead>
            <tr>
                <th style="width: 70%">{{ __('messages.welfare_th_item_subject') }}</th>
                <th class="text-end">{{ __('messages.welfare_th_amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="fw-bold fs-5 text-dark">{{ $welfareRequest->title }}</div>
                    <div class="small text-muted mt-1">{{ __('messages.welfare_category') }} {{ $welfareRequest->type }}</div>
                    <div class="mt-3">
                        <small class="fw-bold d-block text-dark border-bottom mb-1" style="width: fit-content;">{{ __('messages.welfare_detail_reason') }}</small>
                        <div class="ps-2 text-dark">{{ $welfareRequest->description ?: '-' }}</div>
                    </div>
                </td>
                <td class="text-end fw-bold fs-5 align-top">
                    {{ number_format($welfareRequest->amount, 2) }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="text-end fw-bold py-3">{{ __('messages.welfare_grand_total') }}</td>
                <td class="text-end fw-bold py-3 fs-4 border-bottom-double">
                    {{ number_format($welfareRequest->amount, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="signature-section mt-5 pt-4">
        <div class="row text-center">
            <div class="col-6 offset-3">
                <div class="signature-box mb-5">
                    <p class="mb-0 fw-bold">({{ $welfareRequest->user->name }})</p>
                    <p class="small">{{ __('messages.welfare_requester_sign') }}</p>
                    <p class="small mt-1">{{ __('messages.welfare_sign_date') }}</p>
                </div>
                <div class="signature-box mt-5">
                    <p class="mb-0">(................................................)</p>
                    <p class="small fw-bold">{{ __('messages.welfare_approver_sign') }}</p>
                    <p class="small mt-1">{{ __('messages.welfare_sign_date') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="print-footer d-none d-print-block">
        <small>{{ __('messages.welfare_printed_by', ['date' => date('d/m/Y H:i')]) }}</small>
    </div>
</div>

<style>
    /* การแสดงผลหน้าจอปกติ */
    .print-page-container {
        background: white;
        padding: 40px;
        margin: 20px auto;
        max-width: 900px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border: 1px solid #eee;
    }
    .table-print thead th { border-bottom: 2px solid #333; color: #000; }
    .border-bottom-double { border-bottom: 4px double #333 !important; }

    /* --- ส่วนสำคัญสำหรับการปริ้น PDF เพื่อซ่อน Sidebar/Navbar --- */
    @media print {
        /* 1. ซ่อนทุกอย่างที่น่าจะเป็น Sidebar/Navbar ด้วย Selector ที่กว้างขึ้น */
        nav, .navbar, aside, [class*="sidebar"], [class*="navigation"], .main-header, .main-footer, .d-print-none {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            width: 0 !important;
        }

        /* 2. จัดการ Body และ Main Wrapper ให้ขยายเต็มหน้า */
        body, html {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        /* แก้ปัญหา Sidebar บังโดยการ Reset พื้นที่หลัก */
        .main-content, .content-wrapper, .wrapper, .main, main, [role="main"] {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            position: relative !important;
            display: block !important;
        }

        .print-page-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 20px !important;
            box-shadow: none !important;
            border: none !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 9999 !important; /* ดันหน้าปริ้นขึ้นมาบนสุดทับทุกอย่าง */
            background-color: white !important;
        }

        .table-print {
            border: 1px solid #000 !important;
        }
        
        .table-print th, .table-print td {
            border: 1px solid #000 !important;
            color: #000 !important;
        }

        .signature-section { margin-top: 100px !important; }

        @page {
            size: A4;
            margin: 10mm;
        }
    }
</style>
@endsection