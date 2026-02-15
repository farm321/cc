<?php
session_start();
include('db_connect.php'); // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ฟังก์ชันอัปโหลดรูปภาพ (ปรับปรุงแล้ว)
function uploadImage($file) {
    if(isset($file) && $file['error'] == 0) {
        $target_dir = "uploads/profiles/"; // เปลี่ยนเป็น profiles
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!file_exists($target_dir)) { 
            mkdir($target_dir, 0755, true); 
        }
        
        // ตรวจสอบประเภทไฟล์
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            return null;
        }
        
        // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
        if ($file['size'] > 5000000) {
            return null;
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_name = uniqid() . "." . $ext;
        $target_file = $target_dir . $new_name;
        
        $check = getimagesize($file["tmp_name"]);
        if($check !== false) {
            if(move_uploaded_file($file['tmp_name'], $target_file)) {
                return $target_file; // คืนค่า path เต็ม
            }
        }
    }
    return null;
}

// รับค่าจากฟอร์ม
$username = trim($_POST['username']);
$password = trim($_POST['password']);
$firstname = trim($_POST['firstname']);  // ชื่อ
$lastname = trim($_POST['lastname']);    // นามสกุล
$fullname = $firstname . ' ' . $lastname; // รวมชื่อ-นามสกุล
$phone = trim($_POST['phone']);
$role = $_POST['role'];

// ตรวจสอบข้อมูลพื้นฐาน
if (empty($username) || empty($password) || empty($firstname) || empty($lastname) || empty($phone) || empty($role)) {
    header("Location: register.php?status=error&msg=" . urlencode("กรุณากรอกข้อมูลให้ครบถ้วน"));
    exit();
}

// ตรวจสอบว่า username ซ้ำหรือไม่
$check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check_username->bind_param("s", $username);
$check_username->execute();
$result = $check_username->get_result();

if ($result->num_rows > 0) {
    header("Location: register.php?status=error&msg=" . urlencode("Username นี้มีผู้ใช้แล้ว กรุณาเลือก Username ใหม่"));
    exit();
}

// อัปโหลดรูป (ถ้ามี)
$profile_img = 'default.png'; // ค่าเริ่มต้น
if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $uploaded_img = uploadImage($_FILES['profile_image']);
    if ($uploaded_img !== null) {
        $profile_img = $uploaded_img;
    }
}

// เข้ารหัสรหัสผ่าน
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// เริ่ม Transaction
$conn->begin_transaction();

try {
    // 1. บันทึกลงตาราง users ก่อน
    $sql_user = "INSERT INTO users (username, password, role, fullname, phone, profile_img) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("ssssss", $username, $hashed_password, $role, $fullname, $phone, $profile_img);
    
    if (!$stmt->execute()) {
        throw new Exception("ไม่สามารถบันทึกข้อมูลผู้ใช้ได้");
    }
    
    // ดึง ID ล่าสุดที่เพิ่งสมัครได้มาเก็บไว้
    $last_user_id = $conn->insert_id;

    // 2. ตรวจสอบ Role เพื่อบันทึกลงตารางย่อย
    if ($role == 'teacher') {
        $teacher_code = trim($_POST['teacher_code']);
        $subject_dept = trim($_POST['subject_dept']);
        
        $sql_meta = "INSERT INTO teacher_meta (user_id, teacher_code, subject_dept) VALUES (?, ?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("iss", $last_user_id, $teacher_code, $subject_dept);
        
        if (!$stmt_meta->execute()) {
            throw new Exception("ไม่สามารถบันทึกข้อมูลครูได้");
        }

    } elseif ($role == 'student') {
        $student_code = trim($_POST['student_code']);
        $classroom = trim($_POST['classroom']);
        $year_level = intval($_POST['year_level']);
        
        // ตรวจสอบรหัสนักเรียน - ต้องเป็นตัวเลขเท่านั้น
        if (!preg_match('/^[0-9]+$/', $student_code)) {
            throw new Exception("รหัสนักเรียนต้องเป็นตัวเลขเท่านั้น");
        }
        
        // ตรวจสอบชั้นปี - ต้องเป็น 1-3
        if ($year_level < 1 || $year_level > 3) {
            throw new Exception("ชั้นปีต้องเป็น 1, 2 หรือ 3 เท่านั้น");
        }
        
        // ตรวจสอบว่ารหัสนักเรียนซ้ำหรือไม่
        $check_student = $conn->prepare("SELECT user_id FROM student_meta WHERE student_code = ?");
        $check_student->bind_param("s", $student_code);
        $check_student->execute();
        $student_result = $check_student->get_result();
        
        if ($student_result->num_rows > 0) {
            throw new Exception("รหัสนักเรียนนี้มีในระบบแล้ว");
        }
        
        $sql_meta = "INSERT INTO student_meta (user_id, student_code, classroom, year_level) VALUES (?, ?, ?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("issi", $last_user_id, $student_code, $classroom, $year_level);
        
        if (!$stmt_meta->execute()) {
            throw new Exception("ไม่สามารถบันทึกข้อมูลนักเรียนได้");
        }

    } elseif ($role == 'parent') {
        $child_student_code = trim($_POST['child_student_code']);
        $relation = $_POST['relation'];
        
        // ตรวจสอบรหัสนักเรียนของบุตรหลาน - ต้องเป็นตัวเลขเท่านั้น
        if (!preg_match('/^[0-9]+$/', $child_student_code)) {
            throw new Exception("รหัสนักเรียนของบุตรหลานต้องเป็นตัวเลขเท่านั้น");
        }
        
        // ตรวจสอบว่ามีนักเรียนคนนี้ในระบบหรือไม่
        $check_child = $conn->prepare("SELECT user_id FROM student_meta WHERE student_code = ?");
        $check_child->bind_param("s", $child_student_code);
        $check_child->execute();
        $child_result = $check_child->get_result();
        
        if ($child_result->num_rows == 0) {
            throw new Exception("ไม่พบรหัสนักเรียนนี้ในระบบ กรุณาตรวจสอบอีกครั้ง");
        }
        
        $sql_meta = "INSERT INTO parent_meta (user_id, child_student_code, relation) VALUES (?, ?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("iss", $last_user_id, $child_student_code, $relation);
        
        if (!$stmt_meta->execute()) {
            throw new Exception("ไม่สามารถบันทึกข้อมูลผู้ปกครองได้");
        }
    }

    // ถ้าทุกอย่างผ่าน ยืนยันการบันทึก
    $conn->commit();

    // Redirect พร้อม status success
    header("Location: register.php?status=success");
    exit();

} catch (Exception $e) {
    // ถ้ามี Error ให้ยกเลิกทั้งหมด
    $conn->rollback();
    
    // ลบรูปที่อัปโหลดไว้ (ถ้ามี)
    if ($profile_img != 'default.png' && file_exists($profile_img)) {
        @unlink($profile_img);
    }
    
    // Redirect พร้อม error message
    header("Location: register.php?status=error&msg=" . urlencode($e->getMessage()));
    exit();
}

$conn->close();
?>