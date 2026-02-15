<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// รับค่าจากฟอร์ม
$fullname = trim($_POST['fullname']);
$phone = trim($_POST['phone']);
$classroom = trim($_POST['classroom']);
$year_level = intval($_POST['year_level']);
$new_password = trim($_POST['new_password']);

// จัดการอัปโหลดรูปโปรไฟล์
$profile_image_path = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['profile_image']['type'];
    
    if (in_array($file_type, $allowed_types)) {
        // สร้างโฟลเดอร์ uploads ถ้ายังไม่มี
        $upload_dir = 'uploads/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // สร้างชื่อไฟล์ที่ไม่ซ้ำ
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $student_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // ย้ายไฟล์ที่อัปโหลด
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $profile_image_path = $upload_path;
            
            // ลบรูปเก่าถ้ามี
            $old_image_query = "SELECT profile_img FROM users WHERE id = ?";
            $stmt = $conn->prepare($old_image_query);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $old_image_result = $stmt->get_result();
            $old_image_data = $old_image_result->fetch_assoc();
            
            if ($old_image_data && !empty($old_image_data['profile_img']) && $old_image_data['profile_img'] != 'default.png' && file_exists($old_image_data['profile_img'])) {
                unlink($old_image_data['profile_img']);
            }
        }
    }
}

try {
    // เริ่มต้น transaction
    $conn->begin_transaction();
    
    // อัปเดตตาราง users
    if ($profile_image_path) {
        // มีรูปใหม่
        $update_user_sql = "UPDATE users SET fullname = ?, phone = ?, profile_img = ? WHERE id = ?";
        $stmt = $conn->prepare($update_user_sql);
        $stmt->bind_param("sssi", $fullname, $phone, $profile_image_path, $student_id);
    } else {
        // ไม่มีรูปใหม่
        $update_user_sql = "UPDATE users SET fullname = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($update_user_sql);
        $stmt->bind_param("ssi", $fullname, $phone, $student_id);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("ไม่สามารถอัปเดตข้อมูลผู้ใช้ได้");
    }
    
    // อัปเดตตาราง student_meta
    $update_student_sql = "UPDATE student_meta SET classroom = ?, year_level = ? WHERE user_id = ?";
    $stmt2 = $conn->prepare($update_student_sql);
    $stmt2->bind_param("sii", $classroom, $year_level, $student_id);
    
    if (!$stmt2->execute()) {
        throw new Exception("ไม่สามารถอัปเดตข้อมูลนักเรียนได้");
    }
    
    // อัปเดตรหัสผ่านถ้ามีการกรอก
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password_sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt3 = $conn->prepare($update_password_sql);
        $stmt3->bind_param("si", $hashed_password, $student_id);
        
        if (!$stmt3->execute()) {
            throw new Exception("ไม่สามารถเปลี่ยนรหัสผ่านได้");
        }
    }
    
    // อัปเดตข้อมูลใน session
    $_SESSION['fullname'] = $fullname;
    if ($profile_image_path) {
        $_SESSION['profile_img'] = $profile_image_path;
    }
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['success_msg'] = 'อัปเดตข้อมูลโปรไฟล์เรียบร้อยแล้ว!';
    header("Location: profile_student.php");
    exit();
    
} catch (Exception $e) {
    // Rollback ถ้ามีข้อผิดพลาด
    $conn->rollback();
    
    $_SESSION['error_msg'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    header("Location: profile_student.php");
    exit();
}
?>