<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คู่มือการใช้งาน - Student Hero</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f0f2f5;
            min-height: 100vh;
            /* สีพาสเทลเหมือนหน้า Login */
            background: radial-gradient(circle at top left, #e0f2fe, transparent),
                        radial-gradient(circle at bottom right, #fce7f3, transparent),
                        radial-gradient(circle at bottom left, #fef3c7, transparent),
                        radial-gradient(circle at top right, #d1fae5, transparent);
        }

        .manual-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            backdrop-filter: blur(10px);
            border: none;
            overflow: hidden;
        }

        .nav-pills .nav-link {
            border-radius: 50px;
            padding: 10px 25px;
            color: #666;
            font-weight: 500;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .nav-pills .nav-link.active {
            background-color: #87CEFA; /* สีฟ้าพาสเทล */
            color: white;
            box-shadow: 0 4px 15px rgba(135, 206, 250, 0.4);
        }

        .nav-pills .nav-link:hover:not(.active) {
            background-color: #fff;
            color: #87CEFA;
        }

        .role-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .step-box {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 5px solid #ddd;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        .step-box.teacher { border-color: #FF69B4; } /* ชมพู */
        .step-box.student { border-color: #87CEFA; } /* ฟ้า */
        .step-box.parent { border-color: #FFD700; }  /* เหลือง */

        .back-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
            border-radius: 50px;
            padding: 15px 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="py-5">

    <div class="container">
        <div class="text-center mb-5">
            <h1 class="fw-bold text-primary"><i class="fa-solid fa-book-open me-2"></i> คู่มือการใช้งาน</h1>
            <p class="text-muted fs-5">ระบบสะสมความดี Student Hero</p>
        </div>

        <div class="card manual-card p-4">
            <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-general">
                        <i class="fa-solid fa-rocket me-2"></i>เริ่มต้นใช้งาน
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-teacher">
                        <i class="fa-solid fa-chalkboard-user me-2"></i>สำหรับครู
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-student">
                        <i class="fa-solid fa-user-graduate me-2"></i>สำหรับนักเรียน
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-parent">
                        <i class="fa-solid fa-users me-2"></i>สำหรับผู้ปกครอง
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                
                <div class="tab-pane fade show active" id="pills-general">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <h4 class="mb-3 text-secondary">📝 การสมัครสมาชิก (Register)</h4>
                            <div class="step-box">
                                <ol class="mb-0">
                                    <li class="mb-2">เข้าสู่หน้าแรก กดปุ่ม <b>"สมัครสมาชิก"</b></li>
                                    <li class="mb-2">เลือกสถานะของคุณ (ครู / นักเรียน / ผู้ปกครอง)</li>
                                    <li class="mb-2">กรอกข้อมูลให้ครบถ้วน:
                                        <ul>
                                            <li><span class="text-primary">ครู:</span> ต้องใช้รหัสวิชา/แผนก</li>
                                            <li><span class="text-info">นักเรียน:</span> ต้องใช้รหัสนักเรียน & ห้องเรียน</li>
                                            <li><span class="text-warning">ผู้ปกครอง:</span> ต้องมีรหัสนักเรียนของบุตรหลาน</li>
                                        </ul>
                                    </li>
                                    <li>กดปุ่ม <b>"ลงทะเบียน"</b> แล้วเข้าสู่ระบบได้ทันที</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-teacher">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-chalkboard-user role-icon text-danger"></i>
                        <h3>คู่มือสำหรับคุณครู</h3>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="step-box teacher h-100">
                                <h5>✅ การให้คะแนน / ตัดคะแนน</h5>
                                <hr>
                                <p>1. เลือกรายชื่อนักเรียนจากหน้า Dashboard</p>
                                <p>2. กดปุ่ม <b>"ให้คะแนน"</b></p>
                                <p>3. เลือกประเภท <b>"ความดี 👍"</b> หรือ <b>"ทำผิด 👎"</b></p>
                                <p>4. ระบุเหตุผลและคะแนน แล้วกดบันทึก</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="step-box teacher h-100">
                                <h5>🎁 จัดการของรางวัล</h5>
                                <hr>
                                <p>ไปที่เมนู <b>"ร้านค้า"</b> เพื่อเพิ่มของรางวัลใหม่</p>
                                <p>เมื่อเด็กกดแลกของ ครูต้องเข้ามากด <b>"อนุมัติ"</b> เพื่อตัดสต็อกสินค้า</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-student">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-user-graduate role-icon text-primary"></i>
                        <h3>คู่มือสำหรับนักเรียน</h3>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="step-box student h-100">
                                <h5>💰 เช็คแต้ม & ประวัติ</h5>
                                <hr>
                                <p>เข้าสู่ระบบแล้วจะเห็น <b>"แต้มคงเหลือ"</b> ตัวใหญ่ชัดเจน</p>
                                <p>ด้านล่างจะมีตารางบอกว่าเราได้คะแนน หรือโดนหักคะแนนจากเรื่องอะไร วันไหน</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="step-box student h-100">
                                <h5>🎁 วิธีแลกของรางวัล</h5>
                                <hr>
                                <p>1. ดูรายการของรางวัลที่หน้าแรก</p>
                                <p>2. ถ้าแต้มพอ ปุ่มจะเป็นสีสดใส กด <b>"แลกเลย"</b></p>
                                <p>3. นำไปยื่นให้ครูดูเพื่อรับของจริง</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-parent">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-users role-icon text-warning"></i>
                        <h3>คู่มือสำหรับผู้ปกครอง</h3>
                    </div>
                    <div class="step-box parent">
                        <h5>👶 การติดตามพฤติกรรมบุตรหลาน</h5>
                        <p>เมื่อท่านเข้าสู่ระบบ จะเห็นข้อมูลของบุตรหลานทันที (ตามรหัสนักเรียนที่ท่านกรอกตอนสมัคร)</p>
                        <ul>
                            <li>ดูคะแนนพฤติกรรมรวม</li>
                            <li>ดูประวัติว่าวันนี้ลูกทำดี หรือโดนทำโทษเรื่องอะไร</li>
                        </ul>
                    </div>
                    <div class="step-box parent">
                        <h5>💬 การติดต่อครู</h5>
                        <p>สามารถกดเมนู <b>"แชท"</b> เลือกชื่อครูประจำชั้น แล้วพิมพ์ข้อความสอบถามได้โดยตรง</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <a href="index.php" class="btn btn-dark back-btn">
        <i class="fa-solid fa-arrow-left me-2"></i> กลับหน้าหลัก
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>