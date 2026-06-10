@extends('layouts.app')

@section('content')
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
                            <button type="button" class="btn btn-info btn-sm text-white btn-view-memo" data-id="{{ $memo->id }}">
                                ดูรายละเอียด
                            </button>

                            <form action="{{ route('memo.approve.action', $memo->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-success btn-sm">อนุมัติ</button>
                            </form>

                            <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal({{ $memo->id }})">
                                ปฏิเสธ
                            </button>
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

<div class="modal fade" id="memoDetailModal" tabindex="-1" aria-labelledby="memoDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="memoDetailModalLabel">📋 รายละเอียดใบบันทึกภายใน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalTemplateBody">
                <div class="text-center py-4 text-muted" id="modalLoadingSpinner">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <p>กำลังโหลดข้อมูลเอกสาร...</p>
                </div>
                <div id="modalRealContent" class="d-none">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><strong>เลขที่เอกสาร:</strong> <span id="lblMemoNumber" class="text-primary fw-bold"></span></div>
                        <div class="col-md-6"><strong>วันที่ขอเอกสาร:</strong> <span id="lblRequestDate"></span></div>
                        <div class="col-md-6"><strong>ผู้ยื่นขอ:</strong> <span id="lblUserName"></span></div>
                        <div class="col-md-6"><strong>แผนก / สาขา:</strong> <span id="lblDeptBranch"></span></div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h5>เรื่อง / วัตถุประสงค์</h5>
                        <div class="p-3 bg-light rounded" id="lblSubject" style="white-space: pre-line;"></div>
                    </div>
                    <div class="mb-3">
                        <h5>จำนวนเงินงบประมาณที่รองรับ</h5>
                        <div class="fs-5 text-success fw-bold"><span id="lblAmount"></span> ฿</div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h5>เส้นทางการอนุมัติ (Approval Status)</h5>
                        <ul class="list-group list-group-flush" id="lblApprovalRoute"></ul>
                    </div>
                    <div class="mb-1" id="boxAttachments">
                        <h5>📎 ไฟล์เอกสารแนบประกอบ</h5>
                        <div class="list-group" id="lblFilesContainer"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectMemoModal" tabindex="-1" aria-labelledby="rejectMemoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectMemoModalLabel">⚠️ ระบุสาเหตุการปฏิเสธเอกสาร</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectMemoForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="status" value="rejected">
                    
                    <div class="mb-3">
                        <label for="reject_comment" class="form-label fw-bold">สาเหตุการปฏิเสธคำขอ <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_comment" name="reject_comment" rows="4" placeholder="กรุณาระบุเหตุผลที่ไม่ใหอนุมัติเอกสารฉบับนี้..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger">ยืนยันปฏิเสธ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const memoModal = new bootstrap.Modal(document.getElementById('memoDetailModal'));
    const spinner = document.getElementById('modalLoadingSpinner');
    const content = document.getElementById('modalRealContent');

    document.querySelectorAll('.btn-view-memo').forEach(button => {
        button.addEventListener('click', function () {
            const memoId = this.getAttribute('data-id');
            
            spinner.classList.remove('d-none');
            content.classList.add('d-none');
            memoModal.show();

            fetch(`/internal-memo/${memoId}/json`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const memo = data.data;
                        
                        document.getElementById('lblMemoNumber').textContent = memo.memo_number || '-';
                        document.getElementById('lblRequestDate').textContent = memo.request_date || '-';
                        document.getElementById('lblUserName').textContent = memo.user ? memo.user.name : '-';
                        document.getElementById('lblDeptBranch').textContent = `${memo.department || '-'} / ${memo.branch || '-'}`;
                        document.getElementById('lblSubject').textContent = memo.subject || '-';
                        document.getElementById('lblAmount').textContent = parseFloat(memo.amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2});

                        // เส้นทางการอนุมัติ
                        let routeHtml = '';
                        if(memo.approver_1_id) {
                            let statusBadge = `<span class="badge bg-warning">รอการพิจารณา</span>`;
                            if(memo.approver_1_status === 'approved') statusBadge = `<span class="badge bg-success">อนุมัติแล้ว</span>`;
                            if(memo.approver_1_status === 'rejected') statusBadge = `<span class="badge bg-danger">ปฏิเสธการอนุมัติ</span>`;
                            routeHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">1. ผู้อนุมัติขั้นต้น (หัวหน้าแผนก): ${memo.approver1 ? memo.approver1.name : 'ไม่ระบุชื่อ'} ${statusBadge}</li>`;
                        }
                        if(memo.approver_2_id) {
                            let statusBadge = `<span class="badge bg-secondary">รอคิวพิจารณา</span>`;
                            if(memo.approver_2_status === 'pending') statusBadge = `<span class="badge bg-warning">รอการพิจารณา</span>`;
                            if(memo.approver_2_status === 'approved') statusBadge = `<span class="badge bg-success">อนุมัติแล้ว</span>`;
                            if(memo.approver_2_status === 'rejected') statusBadge = `<span class="badge bg-danger">ปฏิเสธการอนุมัติ</span>`;
                            routeHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">2. ผู้อนุมัติขั้นสุดท้าย (CEO): ${memo.approver2 ? memo.approver2.name : 'ไม่ระบุชื่อ'} ${statusBadge}</li>`;
                        }
                        document.getElementById('lblApprovalRoute').innerHTML = routeHtml || '<li class="list-group-item text-muted">ไม่มีการกำหนดผู้อนุมัติ</li>';

                        // 🛠️ อัปเดตโครงสร้าง JavaScript ตรวจสอบข้อมูลอาร์เรย์ก้อนไฟล์แนบอย่างรัดกุมสูงสุด
                        const fileContainer = document.getElementById('lblFilesContainer');
                        fileContainer.innerHTML = '';
                        
                        let memoFiles = memo.attachments || memo.files || [];

                        // แสดงผลรายการไฟล์แนบ
                        if (memoFiles && memoFiles.length > 0) {
                            document.getElementById('boxAttachments').classList.remove('d-none');
                            memoFiles.forEach(file => {
                                if (file) {
                                    let filePath = file.file_path || file.path || '#';
                                    let fileName = file.file_name || file.name || 'ดาวน์โหลดไฟล์แนบ';
                                    
                                    fileContainer.innerHTML += `
                                        <a href="${filePath}" target="_blank" class="list-group-item list-group-item-action text-truncate text-primary">
                                            📄 ${fileName}
                                        </a>`;
                                }
                            });
                        } else {
                            document.getElementById('boxAttachments').classList.add('d-none');
                        }

                        spinner.classList.add('d-none');
                        content.classList.remove('d-none');
                    } else {
                        alert('ไม่สามารถดึงข้อมูลเอกสารได้: ' + data.message);
                        memoModal.hide();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการดึงข้อมูลจากระบบ');
                    memoModal.hide();
                });
        });
    });
});

/**
 * ฟังก์ชันสำหรับเปิดกล่องข้อความกรอกสาเหตุการปฏิเสธ และปรับเปลี่ยน Action URL ฟอร์มให้สัมพันธ์กับ ID
 */
function openRejectModal(memoId) {
    const form = document.getElementById('rejectMemoForm');
    
    // ผูกปลายทาง URL เข้ากับสัญกรณ์ระบบเราให้สอดคล้องกับโครงสร้างหลักของ Laravel Route
    form.action = `/internal-memo/approvals/${memoId}/action`; 
    
    // เคลียร์ค่าที่พิมพ์ค้างไว้ในกล่องทิ้งก่อนเปิดทุกครั้ง
    document.getElementById('reject_comment').value = '';
    
    // เรียกเปิดตัว Pop-up บันทึกเหตุผลขึ้นมาใช้งาน
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectMemoModal'));
    rejectModal.show();
}
</script>
@endsection