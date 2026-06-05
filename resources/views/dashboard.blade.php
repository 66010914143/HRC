@extends('layouts.app')

@section('title', __('messages.announcement_board'))

@section('content')

<div class="grid grid-cols-12 gap-8">
    
    <div class="col-span-12 lg:col-span-9 space-y-6">
        
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- ส่วนตัวกรองดูโพสต์ย้อนหลัง --}}
        <div class="bg-white rounded-lg shadow border p-4 mb-6">
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">📅</span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-700">{{ __('messages.view_history_posts') }}</h3>
                        <p class="text-[10px] text-gray-500">{{ __('messages.select_date_instruction') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="date" 
                           name="search_date" 
                           value="{{ request('search_date') }}" 
                           class="border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none w-full sm:w-48 text-gray-700">
                    
                    <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-blue-700 transition shadow-sm">
                        {{ __('messages.search') }}
                    </button>

                    @if(request('search_date'))
                        <a href="{{ route('dashboard') }}" class="bg-gray-200 text-gray-600 px-4 py-1.5 rounded-lg text-sm hover:bg-gray-300 transition">
                            {{ __('messages.clear_filter') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-slate-700">
                @if(request('search_date'))
                    {{ __('messages.search_result_title', ['date' => date('d/m/Y', strtotime(request('search_date')))]) }}
                @else
                    {{ __('messages.latest_announcements') }}
                @endif
            </h2>
        </div>

        @forelse($posts as $post)
            <div id="post-{{ $post->id }}" class="bg-white rounded-lg shadow border hover:border-blue-300 transition duration-200 overflow-hidden w-full">
                
                <div class="p-6 pb-0">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold overflow-hidden border border-gray-100 shadow-sm">
                                @if($post->user->profile_image)
                                    <img src="{{ asset('storage/' . $post->user->profile_image) }}" class="w-full h-full object-cover">
                                @else
                                    {{ mb_substr($post->user->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <span class="font-bold text-blue-900 text-lg">{{ $post->user->name }}</span>
                                    <span class="text-[10px] bg-gray-200 px-2 py-0.5 rounded ml-2 text-gray-600 uppercase border">{{ $post->user->position }}</span>
                                </div>
                                <p class="text-[11px] text-gray-400">
                                    {{ $post->created_at->timezone('Asia/Bangkok')->locale(app()->getLocale())->isoFormat('DD MMM YYYY') }} • 
                                    {{ $post->created_at->timezone('Asia/Bangkok')->format('H:i') }}
                                    ({{ $post->created_at->timezone('Asia/Bangkok')->locale(app()->getLocale())->diffForHumans() }})
                                </p>
                            </div>
                        </div>

                        @if(Auth::id() === $post->user_id || Auth::user()->role === 'admin')
                        <div class="relative" x-data="{ openDelete: false }">
                            <button @click="openDelete = !openDelete" class="text-gray-400 hover:text-red-500 transition p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            <div x-show="openDelete" @click.away="openDelete = false" class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-xl z-20 p-2" x-transition x-cloak>
                                <p class="text-[11px] text-gray-600 mb-2 px-2 text-center">{{ __('messages.delete_confirm_title') }}</p>
                                <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-500 text-white text-xs py-1.5 rounded hover:bg-red-600 transition">
                                        {{ __('messages.confirm_delete_btn') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mt-4">{{ $post->title }}</h3>
                </div>

                @if ($post->image)
                    <div class="mt-4 px-6 text-center">
                        <div class="inline-block w-full max-w-2xl">
                            <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-auto rounded-lg shadow-sm border" alt="Post image">
                        </div>
                    </div>
                @endif

                <div class="p-6 pt-4 text-gray-800 leading-relaxed whitespace-pre-line">
                    {{ $post->content }}

                    {{-- START: ส่วนแสดงไฟล์แนบเอกสาร --}}
                    @if (!empty($post->document_file))
                        <div class="mt-6 p-4 border border-green-200 rounded-lg bg-green-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center space-x-3">
                                <div class="text-green-600 text-3xl">📄</div>
                                <div>
                                    <p class="text-sm font-bold text-green-800">{{ __('messages.attached_document') }}</p>
                                    <p class="text-[11px] text-green-600 font-normal">{{ __('messages.attached_document_desc') }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-2 w-full sm:w-auto">
                                {{-- ปุ่มตรวจสอบประเภทไฟล์ หากเป็น Excel ให้แสดงปุ่มดูตารางข้อมูลสด --}}
                                @if(in_array(pathinfo($post->document_file, PATHINFO_EXTENSION), ['xlsx', 'xls', 'csv', 'XLSX', 'XLS', 'CSV']))
                                    <button type="button" onclick="previewExcelData('{{ asset('storage/' . $post->document_file) }}')" class="flex-1 sm:flex-none justify-center px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded shadow hover:bg-blue-700 transition flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        {{ __('messages.preview_excel') }}
                                    </button>
                                @endif

                                {{-- ปุ่มตรวจสอบประเภทไฟล์ หากเป็น Word ให้แสดงปุ่มดูตัวอย่าง Word --}}
                                @if(in_array(pathinfo($post->document_file, PATHINFO_EXTENSION), ['docx', 'doc', 'DOCX', 'DOC']))
                                    <button type="button" onclick="previewWordData('{{ asset('storage/' . $post->document_file) }}')" class="flex-1 sm:flex-none justify-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded shadow hover:bg-indigo-700 transition flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        {{ __('messages.preview_word') }}
                                    </button>
                                @endif

                                <a href="{{ asset('storage/' . $post->document_file) }}" download class="flex-1 sm:flex-none justify-center px-3 py-1.5 bg-white border border-green-600 text-green-700 text-xs font-bold rounded shadow-sm hover:bg-green-50 transition flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    {{ __('messages.download_btn') }}
                                </a>
                            </div>
                        </div>
                    @endif
                    {{-- END: ส่วนแสดงไฟล์แนบเอกสาร --}}

                    {{-- ส่วนการแสดงกลุ่มเป้าหมาย (แก้ไขใหม่ให้เป็นทางการ) --}}
                    <div class="mt-8 pt-4 border-t border-gray-100">
                        <div class="flex flex-wrap gap-3 items-center">
                            <div class="flex items-center text-slate-500">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="text-[11px] font-bold uppercase tracking-wider">{{ __('messages.visibility_permission') }}</span>
                            </div>
                            
                            @if(!empty($post->target_departments) && count($post->target_departments) > 0)
                                @foreach($post->target_departments as $dept)
                                    <span class="inline-flex items-center px-3 py-1 rounded border border-blue-200 bg-blue-50 text-blue-700 text-[11px] font-medium shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span>
                                        {{ $dept }}
                                    </span>
                                @endforeach
                            @endif

                            @if(!empty($post->target_branches) && count($post->target_branches) > 0)
                                @foreach($post->target_branches as $branch)
                                    <span class="inline-flex items-center px-3 py-1 rounded border border-slate-200 bg-slate-50 text-slate-700 text-[11px] font-medium shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-2"></span>
                                        {{ $branch }}
                                    </span>
                                @endforeach
                            @endif

                            @if((empty($post->target_departments) || count($post->target_departments) == 0) && 
                                (empty($post->target_branches) || count($post->target_branches) == 0))
                                <span class="inline-flex items-center px-3 py-1 rounded border border-emerald-200 bg-emerald-50 text-emerald-700 text-[11px] font-bold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                                    {{ __('messages.public_announcement') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 border-t" x-data="{ expanded: false }">
                    <h4 class="text-sm font-bold text-gray-700 mb-4 flex items-center">
                        <span class="mr-2">💬</span> {{ __('messages.comments_title', ['count' => $post->comments->count()]) }}
                    </h4>

                    <div class="space-y-4 mb-4 flex flex-col">
                        @php 
                            $allComments = $post->comments->sortBy('created_at');
                            $commentCount = $allComments->count(); 
                            $showThreshold = 6;
                            $lastVisibleIndex = $commentCount - 2;
                        @endphp

                        @if($commentCount > $showThreshold)
                            <button @click="expanded = !expanded" type="button" 
                                class="text-xs text-blue-600 font-semibold hover:underline mb-2 block px-1 focus:outline-none text-left">
                                <span x-show="!expanded">{{ __('messages.show_previous_comments', ['count' => $commentCount - 2]) }}</span>
                                <span x-show="expanded">{{ __('messages.hide_comments') }}</span>
                            </button>
                        @endif

                        @foreach ($allComments as $index => $comment)
                            <div class="flex space-x-3 items-start" 
                                 @if($commentCount > $showThreshold && $index < $lastVisibleIndex) 
                                    x-show="expanded" 
                                    x-transition 
                                 @endif
                                 x-data="{ isEditing: false }">
                                
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex-shrink-0 flex items-center justify-center text-white text-[10px] font-bold shadow-sm overflow-hidden border border-gray-200">
                                    @if($comment->user->profile_image)
                                        <img src="{{ asset('storage/' . $comment->user->profile_image) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ mb_substr($comment->user->name, 0, 1) }}
                                    @endif
                                </div>

                                <div class="flex-1 bg-white p-3 rounded-2xl border text-sm shadow-sm relative group">
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="flex items-center">
                                            <span class="font-bold text-blue-900 text-xs">{{ $comment->user->name }}</span>
                                            <span class="text-[9px] bg-gray-100 px-1.5 py-0.5 rounded ml-2 text-gray-500 border border-gray-200 uppercase leading-none">
                                                {{ $comment->user->position }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <div class="text-right">
                                                <p class="text-[9px] text-gray-400 italic leading-none">
                                                    {{ $comment->created_at->timezone('Asia/Bangkok')->locale(app()->getLocale())->isoFormat('DD/MM/YYYY HH:mm') }}
                                                </p>
                                                <p class="text-[9px] text-blue-400 font-medium">
                                                    {{ $comment->created_at->timezone('Asia/Bangkok')->locale(app()->getLocale())->diffForHumans() }}
                                                </p>
                                            </div>

                                            @if(Auth::id() == $comment->user_id)
                                            <div class="relative" x-data="{ openMenu: false }">
                                                <button @click="openMenu = !openMenu" @click.away="openMenu = false" class="text-gray-400 hover:text-gray-600 focus:outline-none p-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                                                </button>
                                                <div x-show="openMenu" class="absolute right-0 mt-1 w-24 bg-white border rounded-md shadow-lg z-10 py-1" x-transition x-cloak>
                                                    <button @click="isEditing = true; openMenu = false" class="w-full text-left px-4 py-1 text-[11px] text-gray-700 hover:bg-gray-100">{{ __('messages.edit_comment') }}</button>
                                                    <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete_comment') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left px-4 py-1 text-[11px] text-red-600 hover:bg-gray-100">{{ __('messages.delete_comment') }}</button>
                                                    </form>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div x-show="!isEditing">
                                        <p class="text-gray-700 leading-snug text-xs mt-1">{{ $comment->comment_text }}</p>
                                    </div>

                                    <div x-show="isEditing" x-transition x-cloak>
                                        <form action="{{ route('comments.update', $comment->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <textarea name="comment_text" class="w-full border rounded p-1 text-xs focus:ring-1 focus:ring-blue-500" rows="2">{{ $comment->comment_text }}</textarea>
                                            <div class="flex justify-end space-x-1 mt-1">
                                                <button type="button" @click="isEditing = false" class="text-[10px] text-gray-500">{{ __('messages.cancel_btn') }}</button>
                                                <button type="submit" class="text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded">{{ __('messages.save_btn') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form action="{{ route('comments.store', $post->id) }}" method="POST" class="mt-4">
                        @csrf
                        <div class="flex space-x-2">
                            <input type="text" name="comment_text" placeholder="{{ __('messages.comment_placeholder') }}" required
                                class="flex-1 bg-white border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-bold hover:bg-blue-700 transition shadow-sm">
                                {{ __('messages.send_comment_btn') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white p-20 rounded-lg shadow border text-center text-gray-500 w-full">
                <div class="text-5xl mb-4">📢</div>
                @if(request('search_date'))
                    {{ __('messages.no_posts_found_date') }}
                @else
                    {{ __('messages.no_posts_available') }}
                @endif
            </div>
        @endforelse
    </div>

    <div class="hidden lg:block lg:col-span-3" x-data="{ showAll: false }">
        <div class="sticky top-8 space-y-4">
            
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-700 flex items-center text-sm">
                        <span class="mr-2">🔔</span> {{ __('messages.notifications_title') }}
                    </h3>
                    <div class="flex items-center space-x-2">
                        @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                        @if($unreadCount > 0)
                            <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full animate-pulse font-bold">
                                {{ $unreadCount }}
                            </span>
                        @endif
                        
                        @if(auth()->user()->notifications->count() > 0)
                            <form action="{{ route('notifications.clearAll') }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_clear_notifications') }}')">
                                @csrf
                                <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 hover:underline">{{ __('messages.clear_all_notifications') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
                
                <div class="max-h-[500px] overflow-y-auto">
                    @forelse(auth()->user()->notifications as $index => $notification)
                        <div 
                            x-show="showAll || {{ $index }} < 3"
                            x-transition
                            class="border-b transition duration-200 {{ $notification->read_at ? 'bg-white opacity-70' : 'bg-blue-50/50 border-l-4 border-l-blue-500' }}"
                        >
                            <a href="{{ route('notifications.markAsRead', $notification->id) }}" 
                               class="block p-4 no-underline hover:bg-blue-50">
                                <div class="flex items-start space-x-2">
                                    <div class="flex-1">
                                        <p class="text-[12px] leading-tight {{ $notification->read_at ? 'text-gray-600' : 'text-slate-900 font-semibold' }}">
                                            <span class="font-bold text-blue-700">{{ $notification->data['user_name'] ?? 'ใครบางคน' }}</span> 
                                            {{ $notification->data['message'] ?? 'แจ้งเตือนถึงคุณ' }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-1 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <div class="text-3xl mb-2 opacity-20">📭</div>
                            <p class="text-[11px] text-gray-400 italic">{{ __('messages.no_notifications') }}</p>
                        </div>
                    @endforelse
                </div>
                
                @if(auth()->user()->notifications->count() > 3)
                    <div class="p-2 bg-gray-50 border-t text-center">
                        <button 
                            @click="showAll = !showAll" 
                            class="text-[11px] text-blue-600 hover:text-blue-800 font-medium py-1 focus:outline-none"
                            x-text="showAll ? '{{ __('messages.hide_notifications') }}' : '{{ __('messages.show_all_notifications', ['count' => auth()->user()->notifications->count()]) }}'"
                        >
                        </button>
                    </div>
                @endif
            </div>
            
            <div class="p-4 text-[10px] text-gray-400 text-center">
                HRC Internal Management System © 2026
            </div>
        </div>
    </div>
</div>

{{-- ส่วนแสดงข้อมูล Excel โหมดตารางสด --}}
<div id="excelPreviewModal" style="display: none;" class="fixed inset-0 z-[150] flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-11/12 md:w-5/6 lg:w-4/5 xl:w-3/4 h-[85vh] flex flex-col overflow-hidden">
        <div class="p-4 border-b bg-gray-50 flex justify-between items-center z-30 relative shadow-sm">
            <h3 class="font-bold text-gray-800 flex items-center">
                <span class="text-blue-600 mr-2 text-xl">📊</span> {{ __('messages.excel_preview_title') }}
            </h3>
            <button onclick="closeExcelModal()" type="button" class="text-gray-400 hover:text-red-500 transition focus:outline-none bg-gray-200 hover:bg-red-100 rounded-full p-1.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 bg-gray-100 p-4 overflow-auto">
            <div id="excelTableContainer" class="bg-white rounded-xl border border-gray-200 p-4 max-h-[70vh] overflow-auto shadow-inner">
            </div>
        </div>
    </div>
</div>

{{-- ส่วนแสดงไฟล์ Word ฝั่ง Client-side --}}
<div id="wordPreviewModal" style="display: none;" class="fixed inset-0 z-[150] flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 h-[85vh] flex flex-col overflow-hidden">
        <div class="p-4 border-b bg-gray-50 flex justify-between items-center z-30 relative shadow-sm">
            <h3 class="font-bold text-gray-800 flex items-center">
                <span class="text-indigo-600 mr-2 text-xl">📝</span> {{ __('messages.word_preview_title') }}
            </h3>
            <button onclick="closeWordModal()" type="button" class="text-gray-400 hover:text-red-500 transition focus:outline-none bg-gray-200 hover:bg-red-100 rounded-full p-1.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 bg-slate-100 p-4 overflow-auto flex justify-center">
            <div id="wordRenderContainer" class="bg-white shadow-md border p-8 max-w-4xl w-full min-h-full overflow-y-auto rounded-lg">
            </div>
        </div>
    </div>
</div>

{{-- เรียกใช้ Library สำหรับจัดการดึงข้อมูลมาแปลงในฝั่งเบราเซอร์ตรงๆ --}}
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/docx-preview@0.1.15/dist/docx-preview.min.js"></script>

<script>
// --- จัดการส่วนพรีวิวเอกสาร EXCEL ---
function previewExcelData(fileUrl) {
    const modal = document.getElementById('excelPreviewModal');
    const container = document.getElementById('excelTableContainer');
    
    modal.style.display = 'flex';
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center py-20">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mb-4"></div>
            <p class="text-sm text-gray-600 font-semibold">{{ __('messages.excel_loading_desc') }}</p>
        </div>
    `;

    fetch(fileUrl)
        .then(response => {
            if (!response.ok) throw new Error("{{ __('messages.network_error') }}");
            return response.arrayBuffer();
        })
        .then(data => {
            const workbook = XLSX.read(new Uint8Array(data), { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            let htmlTable = XLSX.utils.sheet_to_html(worksheet, { editable: false });
            
            container.innerHTML = htmlTable;
            
            const table = container.querySelector('table');
            if (table) {
                table.setAttribute('class', 'min-w-full divide-y divide-gray-200 text-sm text-left text-gray-700 border-collapse');
                const ths = table.querySelectorAll('th');
                ths.forEach(th => {
                    th.setAttribute('class', 'bg-slate-100 text-slate-800 font-bold p-3 border border-gray-300 text-xs uppercase tracking-wider sticky top-0');
                });
                const tds = table.querySelectorAll('td');
                tds.forEach(td => {
                    td.setAttribute('class', 'p-2.5 border border-gray-200 whitespace-nowrap bg-white hover:bg-slate-50 transition');
                });
            } else {
                container.innerHTML = '<p class="text-center py-10 text-gray-400">{{ __('messages.excel_empty_error') }}</p>';
            }
        })
        .catch(error => {
            container.innerHTML = `
                <div class="text-center py-12 text-red-500 bg-red-50 rounded-xl border border-red-200 p-6">
                    <p class="font-bold text-lg">{{ __('messages.preview_failed') }}</p>
                    <p class="text-xs mt-2 text-gray-600">{{ __('messages.preview_failed_detail') }} ${error.message}</p>
                </div>
            `;
        });
}

function closeExcelModal() {
    document.getElementById('excelPreviewModal').style.display = 'none';
}

// --- จัดการส่วนพรีวิวเอกสาร WORD ---
function previewWordData(fileUrl) {
    const modal = document.getElementById('wordPreviewModal');
    const container = document.getElementById('wordRenderContainer');
    
    modal.style.display = 'flex';
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center py-20">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-4"></div>
            <p class="text-sm text-gray-600 font-semibold">{{ __('messages.word_loading_desc') }}</p>
        </div>
    `;

    fetch(fileUrl)
        .then(response => {
            if (!response.ok) throw new Error("{{ __('messages.network_error') }}");
            return response.blob();
        })
        .then(blob => {
            container.innerHTML = "";
            docx.renderAsync(blob, container, null, {
                className: "docx", 
                inWrapper: false,
                ignoreWidth: false,
                ignoreHeight: false
            }).catch(err => {
                throw new Error("{{ __('messages.word_structure_error') }} " + err.message);
            });
        })
        .catch(error => {
            container.innerHTML = `
                <div class="text-center py-12 text-red-500 bg-red-50 rounded-xl border border-red-200 p-6 mx-auto max-w-md">
                    <p class="font-bold text-lg">{{ __('messages.word_preview_error_title') }}</p>
                    <p class="text-xs mt-2 text-gray-600">{{ __('messages.preview_failed_detail') }} ${error.message}</p>
                    <p class="text-xs mt-3 text-gray-500 font-normal">{{ __('messages.word_preview_error_desc') }}</p>
                </div>
            `;
        });
}

function closeWordModal() {
    document.getElementById('wordPreviewModal').style.display = 'none';
}
</script>

@endsection