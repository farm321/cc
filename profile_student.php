<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// ดึงข้อมูลนักเรียนจากฐานข้อมูล
$query = "SELECT u.*, s.student_code, s.classroom, s.year_level 
          FROM users u 
          LEFT JOIN student_meta s ON u.id = s.user_id 
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// จัดการข้อความแจ้งเตือน
$success_msg = '';
$error_msg = '';
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน - Student Hero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }

        .profile-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .profile-image-container {
            position: relative;
            display: inline-block;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            object-fit: cover;
            background: white;
        }

        .edit-image-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #ff6b6b;
            color: white;
            border: 3px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .edit-image-btn:hover {
            background: #ee5a52;
            transform: scale(1.1);
        }

        .profile-name {
            color: white;
            font-size: 2rem;
            font-weight: 800;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        .profile-role {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        .profile-body {
            padding: 40px;
        }

        .info-group {
            margin-bottom: 25px;
        }

        .info-label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.1rem;
            color: #2d3748;
            padding: 12px 15px;
            background: #f7fafc;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
        }

        .section-title {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: inline-block;
        }

        .readonly-mode .info-value {
            cursor: not-allowed;
        }

        .edit-mode-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: 0.3s;
        }

        .edit-mode-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .stat-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <div class="profile-card">
            <!-- Header Section -->
            <div class="profile-header">
                <button class="edit-mode-toggle" id="editModeBtn" onclick="toggleEditMode()">
                    <i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูล
                </button>
                
                <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="profile-image-container">
                        <img id="profileImagePreview" 
                             src="<?php echo !empty($student['profile_img']) ? $student['profile_img'] : 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($student['fullname']); ?>" 
                             alt="Profile" 
                             class="profile-image">
                        <label for="profileImageInput" class="edit-image-btn" id="editImageBtn" style="display: none;">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                        <input type="file" 
                               id="profileImageInput" 
                               name="profile_image" 
                               accept="image/*" 
                               style="display: none;" 
                               onchange="previewImage(event)">
                    </div>

                    <div class="profile-name"><?php echo htmlspecialchars($student['fullname']); ?></div>
                    <div class="profile-role">
                        <i class="bi bi-mortarboard-fill me-2"></i>นักเรียน
                        <?php if (!empty($student['classroom'])): ?>
                            - ชั้น <?php echo htmlspecialchars($student['classroom']); ?>
                        <?php endif; ?>
                    </div>
            </div>

            <!-- Body Section -->
            <div class="profile-body">
                    <!-- ข้อมูลพื้นฐาน -->
                    <h5 class="section-title"><i class="bi bi-person-circle me-2"></i>ข้อมูลส่วนตัว</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="bi bi-person me-2"></i>ชื่อ-นามสกุล
                                </div>
                                <input type="text" 
                                       name="fullname" 
                                       class="form-control info-value" 
                                       value="<?php echo htmlspecialchars($student['fullname']); ?>" 
                                       readonly 
                                       id="fullname">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="bi bi-telephone me-2"></i>เบอร์โทรศัพท์
                                </div>
                                <input type="text" 
                                       name="phone" 
                                       class="form-control info-value" 
                                       value="<?php echo htmlspecialchars($student['phone']); ?>" 
                                       readonly 
                                       id="phone">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="bi bi-person-badge me-2"></i>รหัสนักเรียน
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($student['student_code'] ?? '-'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="bi bi-house-door me-2"></i>ชั้นเรียน
                                </div>
                                <input type="text" 
                                       name="classroom" 
                                       class="form-control info-value" 
                                       value="<?php echo htmlspecialchars($student['classroom'] ?? '-'); ?>" 
                                       readonly 
                                       id="classroom">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="bi bi-calendar-check me-2"></i>ชั้นปี
                                </div>
                                <input type="number" 
                                       name="year_level" 
                                       class="form-control info-value" 
                                       value="<?php echo htmlspecialchars($student['year_level'] ?? '-'); ?>" 
                                       readonly 
                                       id="year_level">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="bi bi-person-circle me-2"></i>Username
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($student['username']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- เปลี่ยนรหัสผ่าน -->
                    <h5 class="section-title mt-5"><i class="bi bi-shield-lock me-2"></i>เปลี่ยนรหัสผ่าน</h5>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <div class="info-label">
                                    <i class="bi bi-key me-2"></i>รหัสผ่านใหม่ (เว้นว่างหากไม่ต้องการเปลี่ยน)
                                </div>
                                <input type="password" 
                                       name="new_password" 
                                       class="form-control info-value" 
                                       placeholder="กรอกรหัสผ่านใหม่" 
                                       readonly 
                                       id="new_password">
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มบันทึกและยกเลิก -->
                    <div class="text-center mt-5" id="actionButtons" style="display: none;">
                        <button type="submit" class="btn btn-save me-3">
                            <i class="bi bi-check-circle me-2"></i>บันทึกการเปลี่ยนแปลง
                        </button>
                        <button type="button" class="btn btn-back" onclick="cancelEdit()">
                            <i class="bi bi-x-circle me-2"></i>ยกเลิก
                        </button>
                    </div>
                </form>

                <!-- ปุ่มกลับไปหน้าหลัก -->
                <div class="text-center mt-4" id="backButton">
                    <a href="dashboard_student.php" class="btn btn-back">
                        <i class="bi bi-arrow-left me-2"></i>กลับไปหน้าหลัก
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isEditMode = false;

        function toggleEditMode() {
            isEditMode = !isEditMode;
            
            const editableFields = ['fullname', 'phone', 'classroom', 'year_level', 'new_password'];
            const editModeBtn = document.getElementById('editModeBtn');
            const actionButtons = document.getElementById('actionButtons');
            const backButton = document.getElementById('backButton');
            const editImageBtn = document.getElementById('editImageBtn');

            if (isEditMode) {
                // เปิดโหมดแก้ไข
                editableFields.forEach(field => {
                    document.getElementById(field).removeAttribute('readonly');
                    document.getElementById(field).style.borderColor = '#667eea';
                    document.getElementById(field).style.background = 'white';
                });
                
                editModeBtn.innerHTML = '<i class="bi bi-x-circle me-2"></i>ยกเลิก';
                editModeBtn.style.background = 'rgba(220, 38, 38, 0.3)';
                actionButtons.style.display = 'block';
                backButton.style.display = 'none';
                editImageBtn.style.display = 'flex';
            } else {
                // ปิดโหมดแก้ไข
                cancelEdit();
            }
        }

        function cancelEdit() {
            isEditMode = false;
            const editableFields = ['fullname', 'phone', 'classroom', 'year_level', 'new_password'];
            const editModeBtn = document.getElementById('editModeBtn');
            const actionButtons = document.getElementById('actionButtons');
            const backButton = document.getElementById('backButton');
            const editImageBtn = document.getElementById('editImageBtn');

            editableFields.forEach(field => {
                document.getElementById(field).setAttribute('readonly', true);
                document.getElementById(field).style.borderColor = '#e2e8f0';
                document.getElementById(field).style.background = '#f7fafc';
            });

            editModeBtn.innerHTML = '<i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูล';
            editModeBtn.style.background = 'rgba(255, 255, 255, 0.2)';
            actionButtons.style.display = 'none';
            backButton.style.display = 'block';
            editImageBtn.style.display = 'none';

            // รีเซ็ตฟอร์ม
            document.getElementById('profileForm').reset();
            location.reload();
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileImagePreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        // แสดง SweetAlert ถ้ามีข้อความ
        <?php if ($success_msg): ?>
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '<?php echo $success_msg; ?>',
                confirmButtonColor: '#667eea'
            });
        <?php endif; ?>

        <?php if ($error_msg): ?>
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: '<?php echo $error_msg; ?>',
                confirmButtonColor: '#667eea'
            });
        <?php endif; ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>