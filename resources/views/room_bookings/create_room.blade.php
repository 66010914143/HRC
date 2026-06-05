@extends('layouts.app')

@section('content')
@php
    // 🟢 ดึงข้อมูลสาขาทั้งหมดจากฐานข้อมูลโดยตรง เพื่อนำมาแสดงเป็น Dropdown
    $dbBranches = \App\Models\Branch::orderBy('name', 'asc')->get();
@endphp

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-primary text-white font-bold">
                    <i class="fas fa-plus-circle me-2"></i>{{ trans('messages.add_new_room') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('rooms.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label font-medium">{{ trans('messages.room_name') }}</label>
                            <input type="text" name="name" class="form-control border-2" placeholder="{{ trans('messages.room_name_placeholder') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-medium">{{ trans('messages.branch_floor') }}</label>
                            <select name="branch" class="form-select border-2" required>
                                <option value="">-- {{ trans('messages.select_branch') }} --</option>
                                @foreach($dbBranches as $branch)
                                    <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100 shadow-sm font-bold">{{ trans('messages.btn_save_room') }}</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white font-bold">
                    <i class="fas fa-tasks me-2"></i>{{ trans('messages.manage_rooms_branches') }}
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ trans('messages.th_room_branch') }}</th>
                                    <th class="text-end">{{ trans('messages.th_manage') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rooms as $room)
                                <tr>
                                    <td>
                                        <span class="font-bold text-primary">{{ $room->name }}</span><br>
                                        <small class="text-muted">{{ $room->branch }}</small>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-warning me-1" onclick="editRoomPopup({{ $room->id }}, '{{ $room->name }}', '{{ $room->branch }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom({{ $room->id }}, '{{ $room->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-2 d-flex justify-content-center">
                        {{ $rooms->appends(['upcoming_page' => $upcomingBookings->currentPage(), 'history_page' => $historyBookings->currentPage()])->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-info text-white font-bold">
                    <i class="fas fa-calendar-check me-2"></i>{{ trans('messages.upcoming_bookings') }}
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ trans('messages.th_room') }}</th>
                                    <th>{{ trans('messages.th_user_purpose') }}</th>
                                    <th>{{ trans('messages.th_start_end') }}</th>
                                    <th>{{ trans('messages.th_manage') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingBookings as $booking)
                                <tr>
                                    <td class="font-bold">{{ $booking->room->name }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $booking->user->name }}</span><br>
                                        <small>{{ $booking->title }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $booking->start_time->format('d/m/Y H:i') }}</small><br>
                                        <small>{{ $booking->end_time->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger shadow-sm" onclick="deleteBooking({{ $booking->id }})">{{ trans('messages.btn_delete') }}</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">{{ trans('messages.no_upcoming_bookings') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-2 d-flex justify-content-center">
                        {{ $upcomingBookings->appends(['rooms_page' => $rooms->currentPage(), 'history_page' => $historyBookings->currentPage()])->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white font-bold">
                    <i class="fas fa-history me-2"></i>{{ trans('messages.past_history') }}
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 opacity-75">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ trans('messages.th_room') }}</th>
                                    <th>{{ trans('messages.th_booker') }}</th>
                                    <th>{{ trans('messages.th_used_at') }}</th>
                                    <th class="text-end">{{ trans('messages.th_status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historyBookings as $booking)
                                <tr>
                                    <td>{{ $booking->room->name }}</td>
                                    <td>{{ $booking->user->name }}</td>
                                    <td>
                                        <small>{{ $booking->start_time->format('d/m/Y H:i') }} - {{ $booking->end_time->format('H:i') }}</small>
                                    </td>
                                    <td class="text-end text-success"><i class="fas fa-check-circle"></i> {{ trans('messages.status_completed') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">{{ trans('messages.no_history_yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-2 d-flex justify-content-center">
                        {{ $historyBookings->appends(['rooms_page' => $rooms->currentPage(), 'upcoming_page' => $upcomingBookings->currentPage()])->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 🟢 เก็บตัวแปรสาขาจากฐานข้อมูลเพื่อนำไปใช้ใน Popup
    const dbBranches = @json($dbBranches->pluck('name'));

    // ฟังก์ชันแก้ไขห้องแบบ Popup (วงเหลือง)
    async function editRoomPopup(id, name, branch) {
        
        // 🟢 สร้าง Option ให้ Dropdown ใน Popup และตั้งให้เลือกสาขาเดิมที่บันทึกไว้
        let branchOptions = '<option value="">-- {{ trans("messages.select_branch") }} --</option>';
        dbBranches.forEach(b => {
            let isSelected = (b === branch) ? 'selected' : '';
            branchOptions += `<option value="${b}" ${isSelected}>${b}</option>`;
        });

        const { value: formValues } = await Swal.fire({
            title: '{{ trans("messages.swal_edit_title") }}',
            html:
                `<div class="text-start mb-1"><small>{{ trans("messages.room_name") }}</small></div>` +
                `<input id="swal-name" class="swal2-input mt-0" placeholder="{{ trans("messages.room_name") }}" value="${name}">` +
                `<div class="text-start mb-1 mt-3"><small>{{ trans("messages.branch_floor") }}</small></div>` +
                `<select id="swal-branch" class="swal2-select mt-0" style="display:flex; width: 73%; margin: 0 auto; font-size: 1rem;">${branchOptions}</select>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: '{{ trans("messages.swal_btn_save") }}',
            cancelButtonText: '{{ trans("messages.swal_btn_cancel") }}',
            preConfirm: () => {
                return {
                    name: document.getElementById('swal-name').value,
                    branch: document.getElementById('swal-branch').value
                }
            }
        });

        if (formValues) {
            let form = document.createElement('form');
            form.action = `{{ url('room-bookings/rooms') }}/${id}`;
            form.method = 'POST';
            form.innerHTML = `
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="${formValues.name}">
                <input type="hidden" name="branch" value="${formValues.branch}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // ฟังก์ชันลบห้อง
    function deleteRoom(id, name) {
        Swal.fire({
            title: '{{ trans("messages.swal_delete_room_title") }}',
            text: `{{ trans("messages.swal_delete_room_text") }} "${name}" {{ trans("messages.swal_delete_room_confirm") }}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: '{{ trans("messages.swal_btn_confirm_delete") }}',
            cancelButtonText: '{{ trans("messages.swal_btn_cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.action = `{{ url('room-bookings/rooms') }}/${id}`;
                form.method = 'POST';
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // ฟังก์ชันลบการจอง
    function deleteBooking(id) {
        Swal.fire({
            title: '{{ trans("messages.swal_cancel_booking_title") }}',
            text: '{{ trans("messages.swal_cancel_booking_text") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: '{{ trans("messages.swal_btn_confirm_cancel") }}',
            cancelButtonText: '{{ trans("messages.swal_btn_close") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.action = `{{ url('room-bookings') }}/${id}`;
                form.method = 'POST';
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<style>
    .font-bold { font-weight: 700; }
    .font-medium { font-weight: 500; }
    .table td { vertical-align: middle; }
    .card { overflow: hidden; border-radius: 12px; }
    .badge { font-weight: 500; }
    .pagination { margin-bottom: 0; }
    .swal2-input { font-size: 1rem !important; height: 2.5rem !important; }
</style>
@endsection