<?php
session_start();
include('db_connect.php'); // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// ฟังก์ชันอัปโหลดรูปภาพ
function uploadImage($file) {
    if(isset($file) && $file['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . "." . $ext;
        $target_file = $target_dir . $new_name;
        
        $check = getimagesize($file["tmp_name"]);
        if($check !== false) {
            if(move_uploaded_file($file['tmp_name'], $target_file)) {
                return $new_name;
            }
        }
    }
    return null;
}

// รับค่าจากฟอร์ม
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // เข้ารหัสพาสเวิร์ดทันที
$fullname = $_POST['fullname'];
$phone = $_POST['phone'];
$role = $_POST['role'];

// อัปโหลดรูป (ถ้ามี)
$profile_img = null;
if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $profile_img = uploadImage($_FILES['profile_image']);
}

// เริ่ม Transaction (เพื่อให้มั่นใจว่าถ้าตารางที่ 2 พัง ตารางแรกจะไม่ถูกบันทึก)
$conn->begin_transaction();

try {
    // 1. บันทึกลงตาราง users ก่อน
    $sql_user = "INSERT INTO users (username, password, role, fullname, phone, profile_img) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("ssssss", $username, $password, $role, $fullname, $phone, $profile_img);
    $stmt->execute();
    
    // ดึง ID ล่าสุดที่เพิ่งสมัครได้มาเก็บไว้
    $last_user_id = $conn->insert_id;

    // 2. ตรวจสอบ Role เพื่อบันทึกลงตารางย่อย
    if ($role == 'teacher') {
        $teacher_code = $_POST['teacher_code'];
        $subject_dept = $_POST['subject_dept'];
        
        $sql_meta = "INSERT INTO teacher_meta (user_id, teacher_code, subject_dept) VALUES (?, ?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("iss", $last_user_id, $teacher_code, $subject_dept);
        $stmt_meta->execute();

    } elseif ($role == 'student') {
        $student_code = $_POST['student_code'];
        $classroom = $_POST['classroom'];
        $year_level = $_POST['year_level'];
        
        $sql_meta = "INSERT INTO student_meta (user_id, student_code, classroom, year_level) VALUES (?, ?, ?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("issi", $last_user_id, $student_code, $classroom, $year_level);
        $stmt_meta->execute();

    } elseif ($role == 'parent') {
        $child_student_code = $_POST['child_student_code'];
        $relation = $_POST['relation'];
        
        $sql_meta = "INSERT INTO parent_meta (user_id, child_student_code, relation) VALUES (?, ?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("iss", $last_user_id, $child_student_code, $relation);
        $stmt_meta->execute();
    }

    // ถ้าทุกอย่างผ่าน ยืนยันการบันทึก
    $conn->commit();

    // แสดงผลลัพธ์แบบน่ารักๆ (ใช้ SweetAlert ในหน้า HTML หรือ Redirect)
    echo "<script>
        alert('✨ สมัครสมาชิกสำเร็จ! ยินดีต้อนรับครับ');
        window.location.href = 'index.php'; // กลับไปหน้า Login
    </script>";

} catch (Exception $e) {
    // ถ้ามี Error ให้ยกเลิกทั้งหมด
    $conn->rollback();
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

$conn->close();
?>