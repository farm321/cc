<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - Student Hero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body { background-color: #FFF0F5; font-family: 'Prompt', sans-serif; }
        
        .card-register {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 105, 180, 0.2);
            background: #fff;
        }
        .header-title { color: #FF69B4; font-weight: bold; }
        .form-control { border-radius: 12px; border: 1px solid #FFC0CB; }
        .btn-pastel {
            background-color: #87CEFA; 
            color: white; 
            border-radius: 30px; 
            padding: 10px 30px; 
            border: none; 
            width: 100%; 
            font-size: 1.2rem;
        }
        .btn-pastel:hover { background-color: #00BFFF; }
        .hidden-section { display: none; }

        /* เพิ่ม style สำหรับ input ที่ invalid */
        .form-control:invalid {
            border-color: #dc3545;
        }
        .form-control:valid {
            border-color: #28a745;
        }

        @media (max-width: 768px) {
            body {
                background-color: #fff;
            }
            .container {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .row {
                margin: 0 !important;
            }
            .col-md-8 {
                padding: 0 !important;
            }
            .card-register {
                border-radius: 0 !important;
                box-shadow: none !important;
                min-height: 100vh;
                padding: 20px !important;
            }
            .mt-5, .mb-5 {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
        }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-register p-4">
                <h2 class="text-center header-title mb-4">🌟 สมัครสมาชิกใหม่</h2>
                
                <form id="registerForm" action="save_register.php" method="POST" enctype="multipart/form-data">
                    <h5 class="text-muted">ข้อมูลทั่วไป</h5>
                    
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">รูปโปรไฟล์</label>
                        <div class="mb-2">
                            <img id="profile_preview" 
                                 src="https://api.dicebear.com/7.x/avataaars/svg?seed=default" 
                                 class="rounded-circle border border-3 border-primary mx-auto d-block" 
                                 width="120" height="120" 
                                 style="object-fit: cover;">
                        </div>
                        <input type="file" name="profile_image" class="form-control" accept="image/*" onchange="previewProfileImage(event)">
                        <small class="text-muted">เว้นว่างถ้าต้องการใช้รูปสุ่ม</small>
                    </div>
                    
                    <!-- แยกชื่อ-นามสกุล -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <label>ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="firstname" 
                                   id="firstname"
                                   class="form-control" 
                                   placeholder="ชื่อ" 
                                   required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label>นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="lastname" 
                                   id="lastname"
                                   class="form-control" 
                                   placeholder="นามสกุล" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="phone" 
                               id="phone"
                               class="form-control" 
                               placeholder="0812345678"
                               pattern="[0-9]{10}"
                               maxlength="10"
                               required>
                        <small class="text-muted">กรุณากรอกเบอร์โทร 10 หลัก</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>เลือกสถานะของคุณ <span class="text-danger">*</span></label>
                        <select name="role" id="roleSelector" class="form-select form-control" onchange="toggleFields()" required>
                            <option value="">-- กรุณาเลือก --</option>
                            <option value="teacher">👨‍🏫 คุณครู</option>
                            <option value="student">👦 นักเรียน</option>
                            <option value="parent">👪 ผู้ปกครอง</option>
                        </select>
                    </div>

                    <!-- ฟิลด์สำหรับคุณครู -->
                    <div id="teacher-fields" class="hidden-section bg-light p-3 rounded mb-3">
                        <h6 class="text-primary">ข้อมูลสำหรับคุณครู</h6>
                        <input type="text" 
                               name="teacher_code" 
                               id="teacher_code"
                               class="form-control mb-2" 
                               placeholder="รหัสประจำตัวครู">
                        <input type="text" 
                               name="subject_dept" 
                               id="subject_dept"
                               class="form-control" 
                               placeholder="กลุ่มสาระวิชาที่สอน">
                    </div>

                    <!-- ฟิลด์สำหรับนักเรียน -->
                    <div id="student-fields" class="hidden-section bg-light p-3 rounded mb-3">
                        <h6 class="text-success">ข้อมูลสำหรับนักเรียน</h6>
                        
                        <!-- รหัสนักเรียน: ตัวเลขเท่านั้น -->
                        <div class="mb-2">
                            <label>รหัสนักเรียน <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="student_code" 
                                   id="student_code"
                                   class="form-control" 
                                   placeholder="65414401021"
                                   pattern="[0-9]+"
                                   title="กรุณากรอกเฉพาะตัวเลข">
                            <small class="text-muted">กรอกเฉพาะตัวเลขเท่านั้น</small>
                        </div>
                        
                        <div class="row">
                            <!-- ห้องเรียน: ตัวเลขเท่านั้น -->
                            <div class="col-6">
                                <label>ห้องเรียน <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="classroom" 
                                       id="classroom"
                                       class="form-control" 
                                       placeholder="1"
                                       min="1"
                                       max="99"
                                       title="กรุณากรอกเฉพาะตัวเลข">
                                <small class="text-muted">เช่น: 1, 2, 3</small>
                            </div>
                            
                            <!-- ชั้นปี: 1-3 เท่านั้น -->
                            <div class="col-6">
                                <label>ชั้นปี <span class="text-danger">*</span></label>
                                <select name="year_level" 
                                        id="year_level"
                                        class="form-select form-control">
                                    <option value="">เลือก</option>
                                    <option value="1">ปี 1</option>
                                    <option value="2">ปี 2</option>
                                    <option value="3">ปี 3</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ฟิลด์สำหรับผู้ปกครอง -->
                    <div id="parent-fields" class="hidden-section bg-light p-3 rounded mb-3">
                        <h6 class="text-warning">ข้อมูลสำหรับผู้ปกครอง</h6>
                        
                        <!-- รหัสนักเรียนของบุตร: ตัวเลขเท่านั้น -->
                        <div class="mb-2">
                            <label>รหัสนักเรียนของบุตรหลาน <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="child_student_code" 
                                   id="child_student_code"
                                   class="form-control" 
                                   placeholder="65414401021"
                                   pattern="[0-9]+"
                                   title="กรุณากรอกเฉพาะตัวเลข">
                            <small class="text-muted">กรอกเฉพาะตัวเลขเท่านั้น</small>
                        </div>
                        
                        <label>ความสัมพันธ์</label>
                        <select name="relation" id="relation" class="form-select form-control">
                            <option value="father">บิดา</option>
                            <option value="mother">มารดา</option>
                            <option value="guardian">ผู้ปกครอง</option>
                        </select>
                    </div>

                    <!-- Username และ Password -->
                    <div class="mb-3">
                        <label>ตั้ง Username <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="username" 
                               id="username"
                               class="form-control" 
                               placeholder="username"
                               minlength="4"
                               required>
                        <small class="text-muted">อย่างน้อย 4 ตัวอักษร</small>
                    </div>
                    
                    <div class="mb-4">
                        <label>ตั้งรหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="form-control" 
                               placeholder="รหัสผ่าน"
                               minlength="4"
                               required>
                        <small class="text-muted">อย่างน้อย 4 ตัวอักษร</small>
                    </div>

                    <button type="submit" class="btn btn-pastel">ลงทะเบียน ✨</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toggleFields() {
        // ซ่อนทั้งหมดก่อน
        document.getElementById('teacher-fields').style.display = 'none';
        document.getElementById('student-fields').style.display = 'none';
        document.getElementById('parent-fields').style.display = 'none';

        // ดูว่าเลือกอะไร
        var role = document.getElementById('roleSelector').value;

        // แสดงตามที่เลือก และตั้งค่า required
        if(role === 'teacher') {
            document.getElementById('teacher-fields').style.display = 'block';
            document.getElementById('teacher_code').setAttribute('required', 'required');
            document.getElementById('subject_dept').setAttribute('required', 'required');
            // ยกเลิก required ของอื่น
            removeRequired(['student_code', 'classroom', 'year_level', 'child_student_code', 'relation']);
        }
        else if(role === 'student') {
            document.getElementById('student-fields').style.display = 'block';
            document.getElementById('student_code').setAttribute('required', 'required');
            document.getElementById('classroom').setAttribute('required', 'required');
            document.getElementById('year_level').setAttribute('required', 'required');
            // ยกเลิก required ของอื่น
            removeRequired(['teacher_code', 'subject_dept', 'child_student_code', 'relation']);
        }
        else if(role === 'parent') {
            document.getElementById('parent-fields').style.display = 'block';
            document.getElementById('child_student_code').setAttribute('required', 'required');
            document.getElementById('relation').setAttribute('required', 'required');
            // ยกเลิก required ของอื่น
            removeRequired(['teacher_code', 'subject_dept', 'student_code', 'classroom', 'year_level']);
        }
        else {
            // ถ้าไม่เลือกอะไร ยกเลิก required ทั้งหมด
            removeRequired(['teacher_code', 'subject_dept', 'student_code', 'classroom', 'year_level', 'child_student_code', 'relation']);
        }
    }

    function removeRequired(fieldIds) {
        fieldIds.forEach(function(id) {
            var element = document.getElementById(id);
            if (element) {
                element.removeAttribute('required');
            }
        });
    }

    function previewProfileImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile_preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }

    // ตรวจสอบข้อมูลก่อนส่งฟอร์ม
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // ตรวจสอบว่าเลือก role แล้วหรือยัง
        var role = document.getElementById('roleSelector').value;
        if (!role) {
            Swal.fire({
                title: 'กรุณาเลือกสถานะ',
                text: 'กรุณาเลือกว่าคุณเป็น คุณครู, นักเรียน หรือ ผู้ปกครอง',
                icon: 'warning',
                confirmButtonColor: '#87CEFA'
            });
            return;
        }

        // ตรวจสอบเฉพาะนักเรียน
        if (role === 'student') {
            var studentCode = document.getElementById('student_code').value;
            var classroom = document.getElementById('classroom').value;
            var yearLevel = document.getElementById('year_level').value;

            // ตรวจสอบรหัสนักเรียน
            if (!/^[0-9]+$/.test(studentCode)) {
                Swal.fire({
                    title: 'รหัสนักเรียนไม่ถูกต้อง',
                    text: 'รหัสนักเรียนต้องเป็นตัวเลขเท่านั้น',
                    icon: 'error',
                    confirmButtonColor: '#87CEFA'
                });
                return;
            }

            // ตรวจสอบห้องเรียน
            if (!classroom || classroom < 1) {
                Swal.fire({
                    title: 'กรุณากรอกห้องเรียน',
                    text: 'ห้องเรียนต้องเป็นตัวเลข',
                    icon: 'error',
                    confirmButtonColor: '#87CEFA'
                });
                return;
            }

            // ตรวจสอบชั้นปี
            if (!yearLevel || (yearLevel < 1 || yearLevel > 3)) {
                Swal.fire({
                    title: 'กรุณาเลือกชั้นปี',
                    text: 'ชั้นปีต้องเป็น 1, 2 หรือ 3',
                    icon: 'error',
                    confirmButtonColor: '#87CEFA'
                });
                return;
            }
        }

        // ตรวจสอบเฉพาะผู้ปกครอง
        if (role === 'parent') {
            var childCode = document.getElementById('child_student_code').value;
            
            if (!/^[0-9]+$/.test(childCode)) {
                Swal.fire({
                    title: 'รหัสนักเรียนไม่ถูกต้อง',
                    text: 'รหัสนักเรียนของบุตรหลานต้องเป็นตัวเลขเท่านั้น',
                    icon: 'error',
                    confirmButtonColor: '#87CEFA'
                });
                return;
            }
        }

        // แสดง SweetAlert ยืนยัน
        Swal.fire({
            title: 'ยืนยันการลงทะเบียน?',
            html: `
                <div class="text-start">
                    <p><strong>ชื่อ:</strong> ${document.getElementById('firstname').value} ${document.getElementById('lastname').value}</p>
                    <p><strong>เบอร์โทร:</strong> ${document.getElementById('phone').value}</p>
                    <p><strong>สถานะ:</strong> ${role === 'teacher' ? 'คุณครู' : role === 'student' ? 'นักเรียน' : 'ผู้ปกครอง'}</p>
                </div>
                <br>
                <small>ตรวจสอบข้อมูลให้ถูกต้องก่อนกดยืนยันนะครับ</small>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#87CEFA',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลงทะเบียนเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // แสดง Loading
                Swal.fire({
                    title: 'กำลังบันทึกข้อมูล...',
                    html: 'กรุณารอสักครู่',
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // ส่งฟอร์ม
                setTimeout(() => {
                    e.target.submit(); 
                }, 800);
            }
        });
    });

    // แสดง SweetAlert เมื่อลงทะเบียนสำเร็จ
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') {
        Swal.fire({
            title: 'สมัครสมาชิกสำเร็จ!',
            text: 'ยินดีต้อนรับสู่ครอบครัว Student Hero',
            icon: 'success',
            confirmButtonColor: '#87CEFA'
        }).then(() => {
            window.location.href = 'index.php'; // กลับไปหน้า Login
        });
    }
    else if (urlParams.get('status') === 'error') {
        const msg = urlParams.get('msg') || 'เกิดข้อผิดพลาดในการลงทะเบียน';
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: decodeURIComponent(msg),
            icon: 'error',
            confirmButtonColor: '#87CEFA'
        });
    }

    // ป้องกันการกรอกตัวอักษรในช่องที่เป็นตัวเลขเท่านั้น
    document.getElementById('student_code')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('child_student_code')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('phone')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>

</body>
</html>