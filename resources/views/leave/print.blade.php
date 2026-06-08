<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบลาพนักงาน - {{ $leave->user->name ?? '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        /* จำลองหน้ากระดาษ A4 บนหน้าจอเว็บ */
        .welfare-pdf-box {
            width: 210mm;
            min-height: 297mm;
            padding: 25mm 20mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
            position: relative;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .header-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .line-doc {
            border-bottom: 2px solid #000;
            margin-bottom: 25px;
        }

        .content-body {
            font-size: 16px;
            line-height: 2.5;
            margin-top: 30px;
        }

        .dot-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            padding: 0 10px;
            font-weight: 600;
        }

        /* ตารางลายเซ็นท้ายใบลา */
        .signature-section {
            width: 100%;
            margin-top: 80px;
            border-collapse: collapse;
        }

        .signature-section td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            font-size: 16px;
            line-height: 1.8;
        }

        .sig-wrapper {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
        }

        .sig-img {
            max-height: 65px;
            max-width: 160px;
            object-fit: contain;
        }

        .no-print-bar {
            background: #333;
            padding: 10px;
            text-align: center;
        }

        .btn-print {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 20px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
        }

        /* 🚀 คำสั่ง CSS สำหรับตอนสั่งพิมพ์จริงๆ */
        @media print {
            body { background: white; }
            .no-print-bar { display: none !important; }
            .welfare-pdf-box {
                margin: 0;
                box-shadow: none;
                padding: 15mm 15mm;
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">กดพิมพ์เอกสารใบลา</button>
    </div>

    <div class="welfare-pdf-box">
        <div class="text-center header-title">ใบลาพนักงาน</div>
        <div class="text-right" style="font-size: 16px; margin-bottom: 5px;">
            วันที่เขียนคำขอ: <span class="dot-line">{{ \Carbon\Carbon::parse($leave->created_at)->format('d/m/Y') }}</span>
        </div>
        
        <div class="line-doc"></div>

        <div class="content-body">
            <p>เรียน หัวหน้างาน และฝ่ายบุคคล</p>
            <p style="text-indent: 2.5cm;">
                ข้าพเจ้า <span class="dot-line">{{ $leave->user->name ?? '....................' }} {{ $leave->user->last_name ?? '' }}</span> 
                ตำแหน่ง <span class="dot-line">{{ $leave->user->position ?? '....................' }}</span> 
                แผนก/ฝ่าย <span class="dot-line">{{ $leave->user->department ?? '....................' }}</span>
            </p>
            <p>
                มีความประสงค์ขอ <span class="dot-line">{{ $leave->leave_type }}</span> 
                เนื่องจาก <span class="dot-line">{{ $leave->reason }}</span>
            </p>
            <p>
                ตั้งแต่วันที่ <span class="dot-line">{{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}</span> 
                ถึงวันที่ <span class="dot-line">{{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}</span> 
                รวมเป็นเวลา <span class="dot-line">
                    {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}
                </span> วัน
            </p>
        </div>

        <table class="signature-section">
            <tr>
                <td>
                    <div class="sig-wrapper">
                        @if($leave->user && $leave->user->signature)
                            <img src="{{ asset('storage/' . $leave->user->signature) }}" class="sig-img">
                        @endif
                    </div>
                    ลงชื่อ..........................................................<br>
                    ( {{ $leave->user->name ?? '.....................................' }} )<br>
                    <strong>ผู้ยื่นใบลา</strong>
                </td>

                <td>
                    <div class="sig-wrapper">
                        @if($leave->status === 'approved' && $leave->approver && $leave->approver->signature)
                            <img src="{{ asset('storage/' . $leave->approver->signature) }}" class="sig-img">
                        @endif
                    </div>
                    ลงชื่อ..........................................................<br>
                    ( {{ $leave->approver ? $leave->approver->name : '.....................................' }} )<br>
                    <strong>ผู้อนุมัติ</strong>
                </td>
            </tr>
        </table>
    </div>

    <script>
        window.onload = function() {
            // หน่วงเวลาเล็กน้อยเพื่อให้ฟอนต์และรูปภาพโหลดเสร็จก่อนเปิดหน้าพิมพ์
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>