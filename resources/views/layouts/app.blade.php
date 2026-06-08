<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HRC SYSTEM')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f8fafc;
        }
        .sidebar-scroll::-webkit-scrollbar { width: 0px; }
        
        .collapse:not(.show) { 
            display: none !important; 
        }
        
        a { text-decoration: none !important; }
        
        .collapsing {
            transition: height 0.35s ease !important;
        }

        /* Custom Sidebar Styles */
        .nav-item {
            transition: all 0.2s ease-in-out;
            border-left: 4px solid transparent;
        }
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
            padding-left: 1.25rem !important;
        }
        .nav-item.active {
            background-color: #1e293b;
            border-left-color: #3b82f6;
            color: #3b82f6 !important;
        }
        .nav-icon {
            width: 25px;
            text-align: center;
            font-size: 1.1rem;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex min-h-screen">

    <div class="w-72 bg-slate-900 text-slate-300 flex flex-col sticky top-0 h-screen shadow-2xl z-20 sidebar-scroll overflow-y-auto">
        
        <div class="p-6">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-600 text-white p-2 rounded-xl shadow-lg shadow-blue-500/30">
                    <i class="fas fa-layer-group fa-lg"></i>
                </div>
                <h1 class="text-xl font-bold tracking-wider text-white">
                    HRC <span class="text-blue-500">SYSTEM</span>
                </h1>
            </div>
        </div>

        <nav class="flex-1 px-4 space-y-1">
            {{-- ส่วนปุ่มเปลี่ยนภาษา (Language Switcher) --}}
            <div class="px-2 mb-4">
                <div class="flex p-1 bg-slate-800 rounded-lg justify-between items-center text-xs gap-1">
                    <a href="{{ route('lang.switch', 'th') }}" class="flex-1 text-center py-1.5 rounded transition-all {{ App::getLocale() == 'th' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                        🇹🇭 TH
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}" class="flex-1 text-center py-1.5 rounded transition-all {{ App::getLocale() == 'en' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                        🇬🇧 EN
                    </a>
                    <a href="{{ route('lang.switch', 'my') }}" class="flex-1 text-center py-1.5 rounded transition-all {{ App::getLocale() == 'my' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                        🇲🇲 MY
                    </a>
                    <a href="{{ route('lang.switch', 'lo') }}" class="flex-1 text-center py-1.5 rounded transition-all {{ App::getLocale() == 'lo' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                        🇱🇦 LO
                    </a>
                </div>
            </div>

            <div class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2 ml-2">{{ __('messages.main_menu') }}</div>
            
            <a href="{{ route('dashboard') }}" class="nav-item flex items-center py-3 px-4 rounded-lg text-slate-300 {{ Request::is('dashboard') ? 'active text-white' : '' }}">
                <i class="fas fa-chart-pie nav-icon mr-3"></i>
                <span class="font-medium">{{ __('messages.dashboard') }}</span>
            </a>

            {{-- ตรวจสอบสิทธิ์สร้างประกาศ (ต้องไม่ใช่ admin และระดับ 0-3) --}}
            @if(Auth::check() && Auth::user()->role !== 'admin' && in_array((int)Auth::user()->position_level, [0, 1, 2, 3], true))
                <a href="{{ route('posts.create') }}" class="nav-item flex items-center py-3 px-4 rounded-lg text-slate-300 {{ Request::is('posts/create') ? 'active text-white' : '' }}">
                    <i class="fas fa-plus-circle nav-icon mr-3 text-blue-400"></i>
                    <span class="font-medium">{{ __('messages.create_new_post') }}</span>
                </a>
            @endif

            {{-- เมนูการลาออนไลน์ --}}
            <div x-data="{ open: {{ Request::is('leave*') ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" 
                    class="nav-item w-full flex items-center justify-between py-3 px-4 rounded-lg text-slate-300 border-0 bg-transparent outline-none {{ Request::is('leave*') ? 'bg-slate-800/50' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-check nav-icon mr-3"></i>
                        <span class="font-medium">{{ __('messages.leave_system') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(isset($leaveNotificationCount) && $leaveNotificationCount > 0)
                            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ring-4 ring-slate-900">
                                {{ $leaveNotificationCount }}
                            </span>
                        @endif
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                    </div>
                </button>
                
                <div x-show="open" x-cloak x-transition class="ml-9 space-y-1 border-l-2 border-slate-800 pl-4 py-1">
                    <a href="{{ route('leave.index') }}" class="block py-2 text-sm {{ Request::routeIs('leave.index') ? 'text-blue-400 font-bold' : 'hover:text-white' }} transition-colors">
                        {{ Auth::check() && Auth::user()->role === 'admin' ? __('messages.leave_history_title') : __('messages.submit_leave_and_history') }}
                    </a>
                    
                    @php
                        $leaveApproverPositions = [
                            'ประธานเจ้าหน้าที่บริหาร',
                            'ประธานผู้บริหารสายงาน',
                            'ประธานสายงาน',
                            'ผู้อำนวยการอาวุโสกลุ่มงาน',
                            'ผู้จัดการกลุ่มงาน',
                            'ผู้จัดการฝ่าย',
                            'ผู้ชำนาญการ'
                        ];
                    @endphp
                    @if(Auth::check() && in_array(Auth::user()->position, $leaveApproverPositions) && Auth::user()->role !== 'admin')
                        <a href="{{ route('leave.approvals') }}" class="block py-2 text-sm {{ Request::routeIs('leave.approvals') ? 'text-orange-400 font-bold' : 'hover:text-white text-orange-500/70' }} transition-colors">
                            {{ __('messages.approve_leave_online') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- เมนูบันทึกภายใน (Internal Memo) ปรับปรุงเป็น Dropdown --}}
            @php
                $memoNotificationCount = 0;
                if(Auth::check()) {
                    $memoNotificationCount = \App\Models\InternalMemo::where(function($query) {
                        $query->where('approver_1_id', Auth::id())->where('approver_1_status', 'pending');
                    })->orWhere(function($query) {
                        $query->where('approver_2_id', Auth::id())->where('approver_2_status', 'pending')
                              ->where(function($sub) {
                                  $sub->where('approver_1_status', 'approved')->orWhereNull('approver_1_id');
                              });
                    })->count();
                }
            @endphp
            <div x-data="{ open: {{ Request::is('internal-memo*') ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" 
                    class="nav-item w-full flex items-center justify-between py-3 px-4 rounded-lg text-slate-300 border-0 bg-transparent outline-none {{ Request::is('internal-memo*') ? 'bg-slate-800/50' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-file-alt nav-icon mr-3"></i>
                        <span class="font-medium">บันทึกภายใน (Memo)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($memoNotificationCount > 0)
                            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ring-4 ring-slate-900">
                                {{ $memoNotificationCount }}
                            </span>
                        @endif
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                    </div>
                </button>

                <div x-show="open" x-cloak x-transition class="ml-9 space-y-1 border-l-2 border-slate-800 pl-4 py-1">
                    <a href="{{ route('internal_memo.index') }}" class="block py-2 text-sm {{ Request::routeIs('internal_memo.index') ? 'text-blue-400 font-bold' : 'hover:text-white' }} transition-colors">
                        ยื่นขอและประวัติบันทึกภายใน
                    </a>

                    @php
                        $memoApproverPositions = [
                            'ประธานเจ้าหน้าที่บริหาร',
                            'ประธานผู้บริหารสายงาน',
                            'ประธานสายงาน',
                            'ผู้อำนวยการอาวุโสกลุ่มงาน',
                            'ผู้จัดการกลุ่มงาน',
                            'ผู้จัดการฝ่าย',
                            'ผู้ชำนาญการ'
                        ];
                    @endphp
                    @if(Auth::check() && in_array(Auth::user()->position, $memoApproverPositions) && Auth::user()->role !== 'admin')
                        <a href="{{ route('internal_memo.approvals') }}" class="block py-2 text-sm {{ Request::routeIs('internal_memo.approvals') ? 'text-orange-400 font-bold' : 'hover:text-white text-orange-500/70' }} transition-colors">
                            อนุมัติใบบันทึกภายใน
                        </a>
                    @endif
                </div>
            </div>

            {{-- คำนวณแจ้งเตือนเบิกสวัสดิการ --}}
            @php
                $userId = Auth::check() ? Auth::user()->id : 0;

                if (Request::routeIs('welfare.history') && $userId) {
                    \Illuminate\Support\Facades\Cookie::queue('welfare_history_read_at_' . $userId, now()->toIso8601String(), 1440);
                }
                if (Request::routeIs('welfare.approvals') && $userId) {
                    \Illuminate\Support\Facades\Cookie::queue('welfare_approvals_read_at_' . $userId, now()->toIso8601String(), 1440);
                }

                $pendingWelfare = 0;
                $updatedWelfare = 0;
                
                if(Auth::check()) {
                    $historyReadAt = Request::routeIs('welfare.history') ? now()->toIso8601String() : \Illuminate\Support\Facades\Cookie::get('welfare_history_read_at_' . $userId);
                    $approvalsReadAt = Request::routeIs('welfare.approvals') ? now()->toIso8601String() : \Illuminate\Support\Facades\Cookie::get('welfare_approvals_read_at_' . $userId);

                    $pendingQuery = \App\Models\WelfareRequest::where('approver_id', Auth::user()->id)
                                                                ->where('status', 'pending');
                    
                    if ($approvalsReadAt) {
                        $pendingQuery->where('created_at', '>', \Carbon\Carbon::parse($approvalsReadAt));
                    }
                    $pendingWelfare = $pendingQuery->count();
                    
                    $updatedQuery = \App\Models\WelfareRequest::where('user_id', Auth::user()->id)
                                                                ->whereIn('status', ['approved', 'rejected'])
                                                                ->whereDate('updated_at', \Carbon\Carbon::today());
                    
                    if ($historyReadAt) {
                        $updatedQuery->where('updated_at', '>', \Carbon\Carbon::parse($historyReadAt));
                    }
                    $updatedWelfare = $updatedQuery->count();
                }
                
                $totalWelfareNoti = $pendingWelfare + $updatedWelfare;
            @endphp

            {{-- เมนูเบิกสวัสดิการ --}}
            <div x-data="{ open: {{ Request::is('welfare*') ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" 
                    class="nav-item w-full flex items-center justify-between py-3 px-4 rounded-lg text-slate-300 border-0 bg-transparent outline-none {{ Request::is('welfare*') ? 'bg-slate-800/50' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-hand-holding-usd nav-icon mr-3"></i>
                        <span class="font-medium">{{ __('messages.welfare_system') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($totalWelfareNoti > 0)
                            <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ring-4 ring-slate-900">
                                {{ $totalWelfareNoti }}
                            </span>
                        @endif
                        <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                    </div>
                </button>
                
                <div x-show="open" x-cloak x-transition class="ml-9 space-y-1 border-l-2 border-slate-800 pl-4 py-1">
                    @if(Auth::check() && Auth::user()->role !== 'admin')
                        <a href="{{ route('welfare.create') }}" class="block py-2 text-sm {{ Request::routeIs('welfare.create') ? 'text-blue-400 font-bold' : 'hover:text-white' }} transition-colors">
                            {{ __('messages.create_welfare_request') }}
                        </a>
                    @endif

                    <a href="{{ route('welfare.history') }}" class="flex items-center justify-between py-2 pr-4 text-sm {{ Request::routeIs('welfare.history') ? 'text-blue-400 font-bold' : 'hover:text-white' }} transition-colors">
                        <span>{{ __('messages.welfare_history') }}</span>
                        @if(isset($updatedWelfare) && $updatedWelfare > 0)
                            <span class="bg-blue-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ $updatedWelfare }}</span>
                        @endif
                    </a>
                    
                    @php
                        $approverPositions = ['ประธานเจ้าหน้าที่บริหาร', 'ประธานสายงาน', 'ผู้อำนวยการอาวุโสกลุ่มงาน', 'ผู้จัดการกลุ่มงาน', 'ผู้จัดการอาวุโสกลุ่มงาน', 'ผู้จัดการฝ่าย', 'ผู้ชำนาญการ'];
                    @endphp
                    @if(Auth::check() && in_array(Auth::user()->position, $approverPositions))
                        <a href="{{ route('welfare.approvals') }}" class="flex items-center justify-between py-2 pr-4 text-sm {{ Request::routeIs('welfare.approvals') ? 'text-emerald-400 font-bold' : 'hover:text-white text-emerald-500/70' }} transition-colors">
                            <span>{{ __('messages.welfare_pending_approval') }}</span>
                            @if(isset($pendingWelfare) && $pendingWelfare > 0)
                                <span class="bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ $pendingWelfare }}</span>
                            @endif
                        </a>
                    @endif
                </div>
            </div>

            {{-- เมนู ปฏิทินองค์กร --}}
            <a href="{{ route('company_calendar.index') }}" class="nav-item flex items-center justify-between py-3 px-4 rounded-lg text-slate-300 {{ Request::is('company-calendar*') ? 'active text-white' : '' }}">
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt nav-icon mr-3"></i>
                    <span class="font-medium">{{ __('messages.company_calendar') }}</span>
                </div>
                @if(isset($calendarNotiCount) && $calendarNotiCount > 0)
                    <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ring-4 ring-slate-900">
                        {{ $calendarNotiCount }}
                    </span>
                @endif
            </a>

            {{-- ระบบจองห้อง --}}
            <div x-data="{ open: {{ Request::is('room-bookings*') ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" 
                    class="nav-item w-full flex items-center justify-between py-3 px-4 rounded-lg text-slate-300 border-0 bg-transparent outline-none {{ Request::is('room-bookings*') ? 'bg-slate-800/50' : '' }}">
                    <div class="flex items-center">
                        <i class="fas fa-door-open nav-icon mr-3"></i>
                        <span class="font-medium">{{ __('messages.room_booking') }}</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>
                
                <div x-show="open" x-cloak x-transition class="ml-9 space-y-1 border-l-2 border-slate-800 pl-4 py-1">
                    <a href="{{ route('room_bookings.index') }}" class="block py-2 text-sm {{ Request::routeIs('room_bookings.index') ? 'text-blue-400 font-bold' : 'hover:text-white' }} transition-colors">
                        {{ __('messages.booking_calendar') }}
                    </a>
                    
                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <a href="{{ route('room_bookings.create_room') }}" class="block py-2 text-sm {{ Request::routeIs('room_bookings.create_room') ? 'text-orange-400 font-bold' : 'hover:text-white text-orange-500/70' }} transition-colors">
                            {{ __('messages.manage_rooms_and_history') }}
                        </a>
                    @endif
                </div>
            </div>

            <a href="{{ route('profile.edit') }}" class="nav-item flex items-center py-3 px-4 rounded-lg text-slate-300 {{ Request::is('profile*') ? 'active text-white' : '' }}">
                <i class="fas fa-user-cog nav-icon mr-3"></i>
                <span class="font-medium">{{ __('messages.manage_profile') }}</span>
            </a>
            
            @if(Auth::check() && Auth::user()->role == 'admin')
                <div class="pt-6 pb-2 px-2">
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2 ml-2">{{ __('messages.admin_panel') }}</div>
                    
                    <a href="{{ route('admin.users.create') }}" class="flex items-center py-2.5 px-4 rounded-lg transition-all mb-1 {{ Request::is('admin/add-user*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-blue-400 hover:bg-blue-500/10' }}">
                        <i class="fas fa-user-plus nav-icon mr-3"></i>
                        <span class="text-sm font-bold">{{ __('messages.add_member') }}</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center py-2.5 px-4 rounded-lg transition-all {{ Request::is('admin/users*') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'text-emerald-400 hover:bg-emerald-500/10' }}">
                        <i class="fas fa-users-cog nav-icon mr-3"></i>
                        <span class="text-sm font-bold">{{ __('messages.manage_staff') }}</span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="p-4 mt-auto border-t border-slate-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center py-3 px-4 rounded-lg text-red-400 hover:bg-red-500/10 transition-all border-0 bg-transparent outline-none group">
                    <i class="fas fa-sign-out-alt nav-icon mr-3 group-hover:translate-x-1 transition-transform"></i>
                    <span class="font-bold text-sm text-red-400">{{ __('messages.logout') }}</span>
                </button>
            </form>
        </div>
    </div>

    <div class="flex-1 p-10 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>