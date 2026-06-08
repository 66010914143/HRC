@extends('layouts.app')

@block('content')
<div class="container-fluid">
    <h2 class="mb-4 text-primary">📥 รายการคำขออนุมัติใบบันทึกภายใน</h2>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ผู้ขอเอกสาร</th>
                        <th>แผนก/สาขา</th>
                        <th>เรื่อง</th>
                        <th>จำนวนเงิน</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingMemos as $memo)
                    <tr>
                        <td>{{ $memo->user->name }}</td>
                        <td>{{ $memo->department }}</td>
                        <td>{{ $memo->subject }}</td>
                        <td>{{ number_format($memo->amount, 2) }} ฿</td>
                        <td>
                            <form action="{{ route('memo.approve.action', $memo->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-success btn-sm">อนุมัติ</button>
                            </form>

                            <form action="{{ route('memo.approve.action', $memo->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-danger btn-sm">ปฏิเสธ</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">🎉 ยินดีด้วย! ไม่มีคำขออนุมัติค้างอยู่ในระบบ</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endblock