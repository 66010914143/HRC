@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 flex items-center">
        <span class="mr-2">💰</span> {{ __('messages.create_welfare_title') }}
    </h2>

    <form action="{{ route('welfare.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- หัวข้อการเบิก --}}
            <div class="md:col-span-2">
                <label class="block text-gray-700 mb-2">{{ __('messages.welfare_subject_label') }}</label>
                <input type="text" name="title" class="w-full border rounded-lg p-2" placeholder="{{ __('messages.welfare_subject_placeholder') }}" required>
            </div>

            {{-- ประเภท --}}
            <div>
                <label class="block text-gray-700 mb-2">{{ __('messages.welfare_type_label') }}</label>
                <select name="type" class="w-full border rounded-lg p-2" required>
                    <option value="ค่าเดินทาง">{{ __('messages.welfare_type_travel') }}</option>
                    <option value="ค่าที่พัก">{{ __('messages.welfare_type_accommodation') }}</option>
                    <option value="สวัสดิการรักษาพยาบาล">{{ __('messages.welfare_type_medical') }}</option>
                    <option value="อื่นๆ">{{ __('messages.welfare_type_other') }}</option>
                </select>
            </div>

            {{-- จำนวนเงิน --}}
            <div>
                <label class="block text-gray-700 mb-2">{{ __('messages.welfare_amount_label') }}</label>
                <input type="number" name="amount" step="0.01" class="w-full border rounded-lg p-2" placeholder="0.00" required>
            </div>

            {{-- รายละเอียด --}}
            <div class="md:col-span-2">
                <label class="block text-gray-700 mb-2">{{ __('messages.welfare_detail_label') }}</label>
                <textarea name="description" class="w-full border rounded-lg p-2" rows="3"></textarea>
            </div>

            {{-- ไฟล์แนบ --}}
            <div class="md:col-span-2">
                <label class="block text-gray-700 mb-2">{{ __('messages.welfare_attachment_label') }}</label>
                <input type="file" name="attachment" class="w-full">
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                {{ __('messages.welfare_submit_btn') }}
            </button>
        </div>
    </form>
</div>
@endsection