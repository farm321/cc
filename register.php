<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก - Student Hero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #FFF0F5; font-family: 'Prompt', sans-serif; } /* พื้นชมพูอ่อน */
        .card-register {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 105, 180, 0.2);
            background: #fff;
        }
        .header-title { color: #FF69B4; font-weight: bold; }
        .form-control { border-radius: 12px; border: 1px solid #FFC0CB; }
        .btn-pastel {
            background-color: #87CEFA; color: white; border-radius: 30px; padding: 10px 30px; border: none; width: 100%; font-size: 1.2rem;
        }
        .btn-pastel:hover { background-color: #00BFFF; }
        .hidden-section { display: none; } /* ซ่อนไว้ก่อน */
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-register p-4">
                <h2 class="text-center header-title mb-4">🌟 สมัครสมาชิกใหม่</h2>
                
                <form action="save_register.php" method="POST" enctype="multipart/form-data">
                    <h5 class="text-muted">ข้อมูลทั่วไป</h5>
                    
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">รูปโปรไฟล์</label>
                        <div class="mb-2">
                            <img id="profile_preview" src="https://api.dicebear.com/7.x/avataaars/svg?seed=default" class="rounded-circle border border-3 border-primary mx-auto d-block" width="120" height="120" style="object-fit: cover;">
                        </div>
                        <input type="file" name="profile_image" class="form-control" accept="image/*" onchange="previewProfileImage(event)">
                        <small class="text-muted">เว้นว่างถ้าต้องการใช้รูปสุ่ม</small>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>ชื่อ-นามสกุล</label>
                            <input type="text" name="fullname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>เลือกสถานะของคุณ</label>
                        <select name="role" id="roleSelector" class="form-select form-control" onchange="toggleFields()">
                            <option value="">-- กรุณาเลือก --</option>
                            <option value="teacher">👨‍🏫 คุณครู</option>
                            <option value="student">👦 นักเรียน</option>
                            <option value="parent">👪 ผู้ปกครอง</option>
                        </select>
                    </div>

                    <div id="teacher-fields" class="hidden-section bg-light p-3 rounded mb-3">
                        <h6 class="text-primary">ข้อมูลสำหรับคุณครู</h6>
                        <input type="text" name="teacher_code" class="form-control mb-2" placeholder="รหัสประจำตัวครู">
                        <input type="text" name="subject_dept" class="form-control" placeholder="กลุ่มสาระวิชาที่สอน">
                    </div>

                    <div id="student-fields" class="hidden-section bg-light p-3 rounded mb-3">
                        <h6 class="text-success">ข้อมูลสำหรับนักเรียน</h6>
                        <input type="text" name="student_code" class="form-control mb-2" placeholder="รหัสนักเรียน (สำคัญ)">
                        <div class="row">
                            <div class="col-6"><input type="text" name="classroom" class="form-control" placeholder="ชั้นเรียน (เช่น ม.1/1)"></div>
                            <div class="col-6"><input type="number" name="year_level" class="form-control" placeholder="ชั้นปี"></div>
                        </div>
                    </div>

                    <div id="parent-fields" class="hidden-section bg-light p-3 rounded mb-3">
                        <h6 class="text-warning">ข้อมูลสำหรับผู้ปกครอง</h6>
                        <input type="text" name="child_student_code" class="form-control mb-2" placeholder="ระบุรหัสนักเรียนของบุตรหลาน">
                        <select name="relation" class="form-select form-control">
                            <option value="father">บิดา</option>
                            <option value="mother">มารดา</option>
                            <option value="guardian">ผู้ปกครอง</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>ตั้ง Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label>ตั้งรหัสผ่าน</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-pastel">ลงทะเบียน ✨</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFields() {
        // ซ่อนทั้งหมดก่อน
        document.getElementById('teacher-fields').style.display = 'none';
        document.getElementById('student-fields').style.display = 'none';
        document.getElementById('parent-fields').style.display = 'none';

        // ดูว่าเลือกอะไร
        var role = document.getElementById('roleSelector').value;

        // แสดงตามที่เลือก
        if(role === 'teacher') document.getElementById('teacher-fields').style.display = 'block';
        if(role === 'student') document.getElementById('student-fields').style.display = 'block';
        if(role === 'parent') document.getElementById('parent-fields').style.display = 'block';
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
</script>

</body>
</html>