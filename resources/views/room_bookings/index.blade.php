@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: "{{ trans('messages.room_booking_success') }}", text: "{{ session('success') }}", timer: 3000 });
            });
        </script>
    @endif

    @php
        $user = Auth::user();
        $userBranch = $user->branch ?? ''; 
        $isAdmin = ($user->role === 'admin');

        // 🛠️ ปรับปรุงเงื่อนไขให้ใช้ position_level ตามที่แก้ไขใน Controller
        $level = strtoupper(trim((string)($user->position_level ?? '')));
        $isAllowed = true; 

        // ถ้าสายงานมีเลข 4 หรือ 5 จะถูกปรับเป็น false ทันที (ซ่อนฟอร์มซ้าย)
        if (str_contains($level, '4') || str_contains($level, '5')) {
            $isAllowed = false;
        }
    @endphp

    <div class="row">
        {{-- กล่องฟอร์มจองห้องประชุมฝั่งซ้าย (แสดงเมื่อ $isAllowed เป็น true) --}}
        @if($isAllowed)
            <div class="col-md-4" id="booking_side_panel">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white font-bold">{{ trans('messages.room_booking_header') }}</div>
                    <div class="card-body">
                        @if($isAdmin)
                            <div class="alert alert-warning border-0 shadow-sm text-center">{{ trans('messages.room_booking_admin_cannot_book') }}</div>
                        @else
                        
                        {{-- 🚨 เพิ่มกล่องแจ้งเตือนเมื่อเกิดข้อผิดพลาดจากหลังบ้าน (เช่น จองเวลาซ้ำ) --}}
                        @if (session('error'))
                            <div class="alert alert-danger border-0 shadow-sm mb-3">
                                <strong>⚠️ {{ trans('messages.room_booking_error_prefix') }}:</strong> {{ session('error') }}
                            </div>
                        @endif

                        {{-- 🛠️ กล่องแจ้งเตือนสาเหตุ หากฟอร์แมตผิด หรือติดเรื่องสิทธิ์ --}}
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('room_bookings.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label font-medium">{{ trans('messages.room_booking_branch') }}</label>
                                <input type="text" class="form-control border-2 bg-light text-muted font-bold" value="{{ $userBranch }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-medium">{{ trans('messages.room_booking_select_room_label') }}</label>
                                <select name="room_id" id="room_select" class="form-select border-2" required>
                                    <option value="">-- {{ trans('messages.room_booking_select_room_placeholder') }} --</option>
                                    @foreach($rooms as $room)
                                        @if($room->branch === $userBranch)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-medium">{{ trans('messages.room_booking_purpose') }}</label>
                                <input type="text" name="title" class="form-control border-2" required placeholder="{{ trans('messages.room_booking_purpose_placeholder') }}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-medium">{{ trans('messages.room_booking_start_time') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" name="start_time" id="start_time_picker" class="form-control border-2 bg-white" placeholder="{{ trans('messages.room_booking_datetime_placeholder') }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-medium">{{ trans('messages.room_booking_end_time') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-clock"></i></span>
                                    <input type="text" name="end_time" id="end_time_picker" class="form-control border-2 bg-white" placeholder="{{ trans('messages.room_booking_datetime_placeholder') }}" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 shadow-sm font-bold py-2 mt-2">{{ trans('messages.room_booking_btn_confirm') }}</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ตารางปฏิทินฝั่งขวา (ถ้าฟอร์มโดนซ่อน จะขยายเป็น 12 ส่วนเต็มจอ) --}}
        <div class="{{ $isAllowed ? 'col-md-8' : 'col-md-12' }}" id="calendar_side_panel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
{{-- โหลดไฟล์ Locale เพิ่มเติมให้ครอบคลุมแต่ละภาษา --}}
<script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/my.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAllowed = {{ $isAllowed ? 'true' : 'false' }};
    const isAdmin = '{{ $isAdmin ? "true" : "false" }}' === "true";
    
    // ดึงค่า Locale ปัจจุบันจากระบบ Laravel
    const currentLocale = '{{ App::getLocale() }}';

    // เปิดการตั้งค่าเวลาเฉพาะคนที่มีสิทธิ์เห็นฟอร์มและไม่ใช่แอดมิน
    if (isAllowed && !isAdmin) {
        const config = {
            enableTime: true,
            dateFormat: "Y-m-d H:i:s", // 🛠️ ปรับเป็นฟอร์แมตมาตรฐานที่มีวินาทีรองรับการ Save ลง DB และ Validation ของ Laravel
            time_24hr: true,
            locale: currentLocale === 'lo' ? 'th' : (currentLocale === 'my' ? 'my' : (currentLocale === 'th' ? 'th' : 'default')),
            minDate: "today",
            allowInput: true,
            altInput: true,
            altFormat: "Y-m-d - H:i",
        };
        if(document.getElementById("start_time_picker")) {
            flatpickr("#start_time_picker", config);
            flatpickr("#end_time_picker", config);
        }
    }

    // ส่วนของปฏิทิน FullCalendar
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: currentLocale === 'lo' ? 'th' : currentLocale, // ปรับให้เข้ากับ Locale ของ FullCalendar
        timeZone: 'local',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        events: [
            @foreach($bookings as $booking)
                @php
                    $showEvent = (Auth::user()->role === 'admin') || ($booking->room && $booking->room->branch === Auth::user()->branch);
                @endphp
                
                @if($showEvent)
                {
                    id: '{{ $booking->id }}',
                    title: '{{ addslashes($booking->room->name ?? trans("messages.room_booking_default_room")) }}',
                    description: '{{ addslashes($booking->title) }}',
                    user_name: '{{ addslashes($booking->user->name ?? trans("messages.room_booking_unknown_user")) }}',
                    start: '{{ $booking->start_time->format('Y-m-d\TH:i:s') }}',
                    end: '{{ $booking->end_time->format('Y-m-d\TH:i:s') }}',
                    color: '{{ Auth::id() == $booking->user_id ? "#22c55e" : "#3b82f6" }}',
                    extendedProps: { user_id: '{{ $booking->user_id }}' }
                },
                @endif
            @endforeach
        ],
        eventClick: function(info) {
            const event = info.event;
            const start = event.start.toLocaleString('{{ App::getLocale() }}');
            const end = event.end.toLocaleString('{{ App::getLocale() }}');
            const userRole = '{{ Auth::user()->role }}';
            const isAdminClick = userRole === "admin";
            const isOwner = event.extendedProps.user_id == '{{ Auth::id() }}';

            let contentHtml = `
                <div class="text-start p-2">
                    <div class="mb-2"><strong><i class="fas fa-door-open me-2 text-primary"></i>{{ trans('messages.room_booking_swal_room') }}:</strong> ${event.title}</div>
                    <div class="mb-2"><strong><i class="fas fa-info-circle me-2 text-primary"></i>{{ trans('messages.room_booking_swal_purpose') }}:</strong> ${event.extendedProps.description}</div>
                    <div class="mb-2"><strong><i class="fas fa-user me-2 text-primary"></i>{{ trans('messages.room_booking_swal_booker') }}:</strong> ${event.extendedProps.user_name}</div>
                    <div class="mb-0"><strong><i class="fas fa-clock me-2 text-primary"></i>{{ trans('messages.room_booking_swal_time') }}:</strong><br><small class="ms-4 text-muted">${start} - ${end}</small></div>
                </div>
            `;

            if (isOwner || isAdminClick) {
                Swal.fire({
                    title: '<span class="text-primary">{{ trans("messages.room_booking_swal_detail_title") }}</span>',
                    html: contentHtml,
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> {{ trans("messages.room_booking_swal_btn_delete") }}',
                    cancelButtonText: "{{ trans('messages.room_booking_swal_btn_close') }}",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        let form = document.createElement('form');
                        form.action = '/room-bookings/' + event.id;
                        form.method = 'POST';
                        form.innerHTML = `@csrf @method('DELETE')`;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            } else {
                Swal.fire({
                    title: "{{ trans('messages.room_booking_swal_detail_title') }}",
                    html: contentHtml,
                    confirmButtonText: "{{ trans('messages.room_booking_swal_btn_acknowledge') }}",
                    confirmButtonColor: '#3b82f6'
                });
            }
        }
    });

    calendar.render();
});
</script>

<style>
    .fc-event { cursor: pointer; padding: 4px 6px; border-radius: 6px; border: none; font-size: 0.8rem; margin-bottom: 2px; }
    .swal2-popup { border-radius: 15px !important; }
    .font-bold { font-weight: 700; }
    .font-medium { font-weight: 500; }
    .form-control.border-2:focus, .form-select.border-2:focus { border-color: #3b82f6; box-shadow: none; }
    .flatpickr-calendar { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
</style>
@endsection