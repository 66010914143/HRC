@extends('layouts.app')

@section('title', 'ปฏิทินประกาศองค์กร')

@section('content')
<div class="container-fluid">
    
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-3 text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm flex items-center">
            <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        
        {{-- 🔒 ซ่อนฟอร์มไม่ให้ Admin (ระดับ 0, 1, 2, 3) และพนักงานระดับ 4, 5 เห็นตามเงื่อนไขที่กำหนด --}}
        @if(Auth::check() && !in_array((int)Auth::user()->position_level, [0, 1, 2, 3, 4, 5], true))
            <div class="xl:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
                <div class="flex items-center space-x-2 mb-4 border-b border-slate-100 pb-3">
                    <i class="fas fa-edit text-blue-500 text-lg"></i>
                    <h2 class="text-lg font-bold text-slate-800">สร้างประกาศลงปฏิทิน</h2>
                </div>

                <form action="{{ route('company_calendar.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">หัวข้อประกาศ <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required placeholder="เช่น วันตรวจร่างกายประจำปี"
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">รายละเอียดเพิ่มเติม</label>
                        <textarea name="description" rows="3" placeholder="อธิบายเงื่อนไข กำหนดการ หรือสถานที่..."
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm transition-all"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">วันที่จัดกิจกรรม <span class="text-red-500">*</span></label>
                        <input type="date" name="event_date" required
                            class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm transition-all">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-sm font-semibold text-slate-700">สาขาที่ต้องการให้เห็นประกาศ <span class="text-red-500">*</span></label>
                            <div class="space-x-2">
                                <button type="button" onclick="toggleSelectAll('branch', true)" class="text-xs text-blue-600 hover:underline bg-none border-0 p-0 cursor-pointer">เลือกทั้งหมด</button>
                                <button type="button" onclick="toggleSelectAll('branch', false)" class="text-xs text-slate-400 hover:underline bg-none border-0 p-0 cursor-pointer">ล้าง</button>
                            </div>
                        </div>
                        <div class="w-full p-3 border border-slate-200 rounded-xl max-h-40 overflow-y-auto space-y-2 bg-slate-50/50">
                            @if(isset($branches) && count($branches) > 0)
                                @foreach($branches as $branch)
                                    <label class="flex items-start space-x-2 text-sm text-slate-700 cursor-pointer select-none">
                                        <input type="checkbox" name="target_branch[]" value="{{ $branch }}" class="branch-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 mt-0.5">
                                        <span>{{ $branch }}</span>
                                    </label>
                                @endforeach
                            @else
                                <span class="text-xs text-slate-400 block">ไม่พบข้อมูลสาขาในระบบ</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-sm font-semibold text-slate-700">ฝ่ายที่ต้องการให้เห็นประกาศ <span class="text-red-500">*</span></label>
                            <div class="space-x-2">
                                <button type="button" onclick="toggleSelectAll('dept', true)" class="text-xs text-blue-600 hover:underline bg-none border-0 p-0 cursor-pointer">เลือกทั้งหมด</button>
                                <button type="button" onclick="toggleSelectAll('dept', false)" class="text-xs text-slate-400 hover:underline bg-none border-0 p-0 cursor-pointer">ล้าง</button>
                            </div>
                        </div>
                        <div class="w-full p-3 border border-slate-200 rounded-xl max-h-40 overflow-y-auto space-y-2 bg-slate-50/50">
                            @if(isset($departments) && count($departments) > 0)
                                @foreach($departments as $dept)
                                    <label class="flex items-start space-x-2 text-sm text-slate-700 cursor-pointer select-none">
                                        <input type="checkbox" name="target_department[]" value="{{ $dept }}" class="dept-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 mt-0.5">
                                        <span>{{ $dept }}</span>
                                    </label>
                                @endforeach
                            @else
                                <span class="text-xs text-slate-400 block">ไม่พบข้อมูลฝ่ายในระบบ</span>
                            @endif
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="is_urgent" value="1" class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500 cursor-pointer">
                            <span class="ml-2 text-sm font-bold text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1 animate-pulse"></i> เป็นประกาศด่วน
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center space-x-2 border-0 mt-2">
                        <i class="fas fa-paper-plane text-xs"></i>
                        <span class="text-sm">ลงบันทึกประกาศปฏิทิน</span>
                    </button>
                </form>
            </div>
        @endif

        {{-- 📐 ปรับเงื่อนไขความกว้างของแผงปฏิทิน: หากไม่มีสิทธิ์โพสต์ (รวม Admin) ให้ขยายกว้างเต็มหน้าจอ (xl:col-span-4) --}}
        <div class="{{ Auth::check() && !in_array((int)Auth::user()->position_level, [0, 1, 2, 3, 4, 5], true) ? 'xl:col-span-3' : 'xl:col-span-4' }} bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 pb-3 border-b border-slate-100 gap-2">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                    <h2 class="text-lg font-bold text-slate-800">ปฏิทินกิจกรรมและประกาศองค์กร</h2>
                </div>
                <div class="text-xs bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg flex items-center shadow-sm">
                    <i class="fas fa-user-shield mr-2 text-blue-500"></i>
                    แสดงเฉพาะข้อมูลของ: <span class="font-bold ml-1 text-blue-600">{{ Auth::user()->branch }}</span> / <span class="font-bold text-blue-600">{{ Auth::user()->department }}</span>
                </div>
            </div>

            <div id="calendar" class="p-1 min-h-[600px]"></div>
        </div>

    </div>
</div>

<div class="modal fade" id="eventDetailModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header bg-slate-900 border-0 py-4 px-6">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-bullhorn text-amber-400 text-lg"></i>
                    <h5 class="modal-title font-bold text-white text-base" id="modalEventTitle">รายละเอียดประกาศ</h5>
                </div>
                <button type="button" class="btn-close btn-close-white focus:outline-none shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6 space-y-4 bg-slate-50/50">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">📅 วันที่ดำเนินกิจกรรม</span>
                    <p id="modalEventDate" class="text-slate-800 font-semibold bg-white p-2.5 rounded-xl border border-slate-100 shadow-sm text-sm"></p>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">📄 เนื้อหา / รายละเอียดประกาศ</span>
                    <div id="modalEventDescription" class="text-slate-700 bg-white p-3 rounded-xl border border-slate-100 shadow-sm text-sm whitespace-pre-line leading-relaxed min-h-[80px]"></div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <div class="flex items-center text-xs text-slate-500">
                        <i class="fas fa-user-circle mr-1.5 text-slate-400 text-sm"></i>
                        ผู้ประกาศ: <span id="modalEventPostedBy" class="font-bold ml-1 text-slate-700"></span>
                    </div>
                    <button type="button" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold py-2 px-4 rounded-xl transition-all border-0 shadow-sm" data-bs-dismiss="modal">
                        ปิดหน้าต่าง
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
// ฟังก์ชันสคริปต์ควบคุมการ กดเลือกทั้งหมด / ล้างทั้งหมด (แก้ไขข้อผิดพลาดตัวแปรในลูปดั้งเดิมให้ถูกต้อง)
function toggleSelectAll(type, isSelectAll) {
    const checkboxes = document.querySelectorAll('.' + type + '-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = isSelectAll;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    // ดึงข้อมูลอีเวนต์ที่ถูกสกัดกรองเรียบร้อยแล้วจาก Controller มาแปลงเป็น JSON Array
    const calendarEvents = @json($events);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'th', // รองรับภาษาไทย 100%
        timeZone: 'Asia/Bangkok',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'วันนี้',
            month: 'เดือน',
            week: 'สัปดาห์',
            list: 'รายการประกาศ'
        },
        events: calendarEvents,
        editable: false,
        selectable: false,
        height: 'auto',
        
        // 🎯 เมื่อพนักงานทำการคลิกเลือกที่ตัวกิจกรรมบนช่องปฏิทิน
        eventClick: function(info) {
            // ดึง Metadata ออกมาจากข้อมูลกิจกรรมที่กด
            const title = info.event.title;
            const startStr = info.event.startStr;
            const desc = info.event.extendedProps.description || 'ไม่มีคำอธิบายเพิ่มเติมสำหรับประกาศนี้';
            const postedBy = info.event.extendedProps.posted_by;

            // แปลงฟอร์แมตวันที่ให้อ่านง่ายสไตล์ไทย (วว/ดด/ปปปป)
            const dateObj = new Date(startStr);
            const formattedDate = dateObj.toLocaleDateString('th-TH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // ฝังข้อมูลลงในกล่องข้อความบน Modal Popup
            document.getElementById('modalEventTitle').innerText = title;
            document.getElementById('modalEventDate').innerText = formattedDate;
            document.getElementById('modalEventDescription').innerText = desc;
            document.getElementById('modalEventPostedBy').innerText = postedBy;

            // สั่งเปิดตัว Bootstrap Modal แสดงผลทันที
            const detailModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
            detailModal.show();
        }
    });

    calendar.render();
});
</script>
@endpush