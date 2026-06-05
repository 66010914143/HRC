@extends('layouts.app')

@section('title', trans('messages.profile_title'))

@section('content')
<div class="max-w-5xl mx-auto bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-200">
    
    <div class="bg-slate-700 p-12 text-white text-center relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full -mr-32 -mt-32"></div>
        
        <div class="relative z-10">
            <div class="relative w-44 h-44 mx-auto mb-6">
                <div id="profile_display" class="w-full h-full bg-white rounded-full flex items-center justify-center text-slate-700 text-6xl font-bold border-4 border-white shadow-2xl overflow-hidden">
                    @if(Auth::user()->profile_image)
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" class="w-full h-full object-cover">
                    @else
                        <span class="opacity-30">{{ mb_substr(Auth::user()->name, 0, 1) }}</span>
                    @endif
                </div>
                
                <label for="profile_upload" class="absolute bottom-1 right-1 bg-blue-600 p-3 rounded-full border-4 border-slate-700 cursor-pointer hover:bg-blue-500 transition shadow-lg hover:scale-110 active:scale-95 group">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </label>
                <input type="file" id="profile_upload" class="hidden" accept="image/*">
            </div>
            
            <h2 class="text-4xl font-extrabold tracking-tight">
                {{ Auth::user()->name }} {{ Auth::user()->last_name }}
            </h2>
            <p class="text-blue-300 mt-2 font-medium bg-blue-900/30 inline-block px-4 py-1 rounded-full border border-blue-400/20">
                {{ trans('messages.profile_employee_id') }}{{ Auth::user()->username }}
            </p>
        </div>
    </div>

    <div class="p-10 bg-white">
        <div class="grid grid-cols-1 gap-6">
            @php
                $details = [
                    ['label' => trans('messages.profile_user_id'), 'value' => Auth::user()->username, 'bold' => true],
                    ['label' => trans('messages.profile_fullname'), 'value' => Auth::user()->name . ' ' . Auth::user()->last_name],
                    ['label' => trans('messages.profile_position'), 'value' => Auth::user()->position . ' (LEVEL ' . Auth::user()->position_level . ')', 'isBadge' => true],
                    ['label' => trans('messages.profile_branch'), 'value' => Auth::user()->branch ?? trans('messages.profile_not_specified')],
                    ['label' => trans('messages.profile_department'), 'value' => Auth::user()->department ?? trans('messages.profile_not_specified')]
                ];
            @endphp

            @foreach($details as $item)
            <div class="flex items-center border-b border-gray-50 pb-5 last:border-0 transition hover:bg-gray-50/50 p-2 rounded-lg">
                <div class="w-1/3 text-gray-400 font-medium">{{ $item['label'] }}</div>
                <div class="w-2/3">
                    @if(isset($item['isBadge']))
                        <span class="bg-blue-50 text-blue-700 px-4 py-1.5 rounded-lg text-sm font-bold border border-blue-100 shadow-sm">
                            {{ $item['value'] }}
                        </span>
                    @else
                        <span class="{{ isset($item['bold']) ? 'font-bold text-slate-800' : 'text-gray-700' }} text-lg">
                            {{ $item['value'] }}
                        </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-12 pt-8 border-t border-gray-100">
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                <span class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center mr-3 text-sm">📝</span>
                {{ trans('messages.profile_history_title') }}
            </h3>

            @if(Auth::user()->position_level <= 3)
                @if(isset($myPosts) && $myPosts->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($myPosts as $post)
                        <div class="flex justify-between items-center p-5 bg-white rounded-xl border border-gray-200 hover:border-blue-400 transition shadow-sm group">
                            <div>
                                <p class="font-bold text-slate-800 group-hover:text-blue-600 transition">{{ $post->title }}</p>
                                <p class="text-xs text-gray-400 mt-1 flex items-center">
                                    {{ $post->created_at->format('d/m/Y H:i') }}{{ trans('messages.profile_time_suffix') }}
                                </p>
                            </div>
                            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-2 rounded-lg hover:bg-blue-600 hover:text-white transition">{{ trans('messages.profile_view_post') }}</a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-400">{{ trans('messages.profile_no_posts') }}</p>
                    </div>
                @endif
            @else
                <div class="p-6 bg-amber-50 rounded-xl border border-amber-100 flex items-center text-amber-700">
                    <span class="mr-4 text-2xl">⚠️</span>
                    <p>{{ trans('messages.profile_no_permission') }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-gray-50 p-8 border-t border-gray-100 text-center">
        <p class="text-sm text-gray-400 italic">{{ trans('messages.profile_copyright') }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('profile_upload').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('profile_image', file);
        formData.append('_token', '{{ csrf_token() }}');

        const profileDisplay = document.getElementById('profile_display');
        profileDisplay.innerHTML = '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>';

        fetch('{{ route("profile.image.update") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) window.location.reload();
            else alert('{{ trans("messages.profile_error") }}' + data.message);
        });
    });
</script>
@endpush