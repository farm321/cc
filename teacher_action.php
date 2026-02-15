<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    die("Access Denied");
}

$action = $_GET['action'] ?? '';

// --- ฟังก์ชันอัปโหลดรูปภาพ (แก้ไขแล้ว) ---
function uploadImage($file) {
    if(isset($file) && $file['error'] == 0) {
        $target_dir = "uploads/profiles/"; // เปลี่ยนเป็น profiles
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

// ------------------------------------------
// 1. จัดการนักเรียน (เพิ่ม / แก้ไข / ลบ)
// ------------------------------------------
if ($action == 'add_student') {
    // รับชื่อ-นามสกุลแยกกัน
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $fullname = $firstname . ' ' . $lastname;
    
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $st_code = trim($_POST['student_code']);
    $year_level = intval($_POST['year_level']);
    $classroom = trim($_POST['classroom']);
    
    // ตรวจสอบรหัสนักเรียน - ต้องเป็นตัวเลขเท่านั้น
    if (!preg_match('/^[0-9]+$/', $st_code)) {
        header("Location: dashboard_teacher.php?page=students&error=invalid_code");
        exit();
    }
    
    // ตรวจสอบชั้นปี - ต้องเป็น 1-3
    if ($year_level < 1 || $year_level > 3) {
        header("Location: dashboard_teacher.php?page=students&error=invalid_year");
        exit();
    }
    
    // ตรวจสอบรหัสซ้ำ
    $check_code = $conn->prepare("SELECT user_id FROM student_meta WHERE student_code = ?");
    $check_code->bind_param("s", $st_code);
    $check_code->execute();
    if ($check_code->get_result()->num_rows > 0) {
        header("Location: dashboard_teacher.php?page=students&error=duplicate_code");
        exit();
    }
    
    // อัปโหลดรูป (ถ้ามี)
    $profile_img = 'default.png';
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $uploaded = uploadImage($_FILES['profile_image']);
        if($uploaded) {
            $profile_img = $uploaded;
        }
    }

    // บันทึก User
    $sql = "INSERT INTO users (username, password, fullname, role, profile_img) VALUES (?, ?, ?, 'student', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $fullname, $profile_img);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        // บันทึกข้อมูล meta
        $sql_meta = "INSERT INTO student_meta (user_id, student_code, year_level, classroom) VALUES (?, ?, ?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("isis", $user_id, $st_code, $year_level, $classroom);
        $stmt_meta->execute();
        
        header("Location: dashboard_teacher.php?page=students&msg=added");
    } else {
        echo "Error: " . $conn->error;
    }
    exit();
}

if ($action == 'edit_student') {
    $id = $_POST['id'];
    
    // รับชื่อ-นามสกุลแยกกัน
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $fullname = $firstname . ' ' . $lastname;
    
    $st_code = trim($_POST['student_code']);
    $year_level = intval($_POST['year_level']);
    $classroom = trim($_POST['classroom']);
    
    // ตรวจสอบรหัสนักเรียน
    if (!preg_match('/^[0-9]+$/', $st_code)) {
        header("Location: dashboard_teacher.php?page=students&error=invalid_code");
        exit();
    }
    
    // ตรวจสอบชั้นปี
    if ($year_level < 1 || $year_level > 3) {
        header("Location: dashboard_teacher.php?page=students&error=invalid_year");
        exit();
    }
    
    // เช็คว่ามีการเปลี่ยนรูปไหม
    $img_sql = "";
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $new_img = uploadImage($_FILES['profile_image']);
        if($new_img) {
            $img_sql = ", profile_img = '$new_img'";
        }
    }

    // เช็คว่ามีการเปลี่ยนรหัสผ่านไหม
    $pwd_sql = "";
    if(!empty($_POST['password'])) {
        $pwd = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pwd_sql = ", password = '$pwd'";
    }

    // อัปเดต Users
    $conn->query("UPDATE users SET fullname = '$fullname' $pwd_sql $img_sql WHERE id = $id");
    
    // อัปเดต Meta
    $conn->query("UPDATE student_meta SET student_code = '$st_code', year_level = $year_level, classroom = '$classroom' WHERE user_id = $id");

    header("Location: dashboard_teacher.php?page=students&msg=updated");
    exit();
}

if ($action == 'delete_student') {
    $id = intval($_GET['id']);

    $conn->query("DELETE FROM behavior_logs WHERE student_id = $id");
    $conn->query("DELETE FROM redemption_logs WHERE student_id = $id");
    $conn->query("DELETE FROM student_meta WHERE user_id = $id");
    $del = $conn->query("DELETE FROM users WHERE id = $id");

    if ($del) {
        header("Location: dashboard_teacher.php?page=students&msg=deleted");
    } else {
        echo "Error deleting user: " . $conn->error;
    }
    exit();
}

// ------------------------------------------
// 2. จัดการพฤติกรรม
// ------------------------------------------
if ($action == 'add_behavior_config' || $action == 'add_behavior') {
    $title = $_POST['title'];
    $score = intval($_POST['score']);
    $type = $_POST['type'] ?? (($score >= 0) ? 'good' : 'bad');
    
    if ($type == 'bad') {
        $score = -abs($score);
    } else {
        $score = abs($score);
    }
    
    $conn->query("INSERT INTO behavior_config (title, score, type) VALUES ('$title', '$score', '$type')");
    header("Location: dashboard_teacher.php?page=behavior&msg=added");
    exit();
}

if ($action == 'delete_behavior_config' || $action == 'delete_behavior') {
    $id = $_GET['id'];
    $conn->query("DELETE FROM behavior_config WHERE id = $id");
    header("Location: dashboard_teacher.php?page=behavior&msg=deleted");
    exit();
}

// ------------------------------------------
// 3. จัดการผู้ปกครอง
// ------------------------------------------
if ($action == 'add_parent') {
    // รับชื่อ-นามสกุลแยกกัน
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $fullname = $firstname . ' ' . $lastname;
    
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $child_code = trim($_POST['child_student_code']);
    
    // ตรวจสอบรหัสนักเรียน - ต้องเป็นตัวเลขเท่านั้น
    if (!preg_match('/^[0-9]+$/', $child_code)) {
        header("Location: dashboard_teacher.php?page=parents&error=invalid_code");
        exit();
    }
    
    // ตรวจสอบว่ามีนักเรียนคนนี้ในระบบหรือไม่
    $check_child = $conn->prepare("SELECT user_id FROM student_meta WHERE student_code = ?");
    $check_child->bind_param("s", $child_code);
    $check_child->execute();
    if ($check_child->get_result()->num_rows == 0) {
        header("Location: dashboard_teacher.php?page=parents&error=student_not_found");
        exit();
    }
    
    // บันทึก User ผู้ปกครอง
    $sql_user = "INSERT INTO users (username, password, role, fullname) VALUES (?, ?, 'parent', ?)";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("sss", $username, $password, $fullname);
    
    if($stmt->execute()) {
        $parent_id = $stmt->insert_id;
        
        $sql_meta = "INSERT INTO parent_meta (user_id, child_student_code) VALUES (?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("is", $parent_id, $child_code);
        $stmt_meta->execute();
    }
    
    header("Location: dashboard_teacher.php?page=parents&msg=added");
    exit();
}

if ($action == 'edit_parent') {
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $child_code = $_POST['child_student_code'];
    
    $pwd_sql = "";
    if(!empty($_POST['password'])) {
        $pwd = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pwd_sql = ", password = '$pwd'";
    }

    $conn->query("UPDATE users SET fullname = '$fullname' $pwd_sql WHERE id = $id");
    $conn->query("UPDATE parent_meta SET child_student_code = '$child_code' WHERE user_id = $id");

    header("Location: dashboard_teacher.php?page=parents&msg=updated");
    exit();
}

if ($action == 'delete_parent') {
    $id = intval($_GET['id']);

    $conn->query("DELETE FROM parent_meta WHERE user_id = $id");
    $conn->query("DELETE FROM behavior_logs WHERE teacher_id = $id");
    $conn->query("DELETE FROM messages WHERE sender_id = $id OR receiver_id = $id");
    $del = $conn->query("DELETE FROM users WHERE id = $id");

    if ($del) {
        header("Location: dashboard_teacher.php?page=parents&msg=deleted");
    } else {
        echo "Error deleting user: " . $conn->error;
    }
    exit();
}

if ($action == 'delete_user') {
    $id = $_GET['id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

if ($action == 'append_child_to_parent') {
    $parent_user_id = $_POST['parent_user_id'];
    $new_child_code = trim($_POST['new_child_code']);
    
    // ตรวจสอบรหัส
    if (!preg_match('/^[0-9]+$/', $new_child_code)) {
        header("Location: dashboard_teacher.php?page=parents&error=invalid_code");
        exit();
    }
    
    $chk = $conn->query("SELECT * FROM student_meta WHERE student_code = '$new_child_code'");
    if($chk->num_rows == 0) {
        header("Location: dashboard_teacher.php?page=parents&error=student_not_found");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO parent_meta (user_id, child_student_code) VALUES (?, ?)");
    $stmt->bind_param("is", $parent_user_id, $new_child_code);
    
    if($stmt->execute()) {
        header("Location: dashboard_teacher.php?page=parents&msg=child_added");
    } else {
        header("Location: dashboard_teacher.php?page=parents&error=duplicate");
    }
    exit();
}

// ------------------------------------------
// 4. ให้คะแนน
// ------------------------------------------
if ($action == 'save_score') {
    $student_id = $_POST['student_id'];
    $teacher_id = $_SESSION['user_id'];
    $config_id = $_POST['config_id'];
    $detail = $_POST['detail'];

    $res = $conn->query("SELECT * FROM behavior_config WHERE id = $config_id");
    $cfg = $res->fetch_assoc();
    
    $final_score = $cfg['score'];

    $sql = "INSERT INTO behavior_logs (student_id, teacher_id, behavior_type, title, detail, score) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssi", $student_id, $teacher_id, $cfg['type'], $cfg['title'], $detail, $final_score);
    $stmt->execute();
    
    header("Location: dashboard_teacher.php?page=students&msg=scored");
    exit();
}

// ------------------------------------------
// 5. ข่าวสาร & ร้านค้า
// ------------------------------------------
if ($action == 'add_news') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $type = $_POST['type'];
    $conn->query("INSERT INTO announcements (title, content, type) VALUES ('$title', '$content', '$type')");
    header("Location: dashboard_teacher.php?page=news&msg=added");
    exit();
}

if ($action == 'delete_news') {
    $id = $_GET['id'];
    $conn->query("DELETE FROM announcements WHERE id = $id");
    header("Location: dashboard_teacher.php?page=news&msg=deleted");
    exit();
}

if ($action == 'add_reward') {
    $name = $_POST['name'];
    $cost = $_POST['point_cost'];
    $stock = $_POST['stock'];
    $conn->query("INSERT INTO rewards (name, point_cost, stock) VALUES ('$name', '$cost', '$stock')");
    header("Location: dashboard_teacher.php?page=shop&msg=added");
    exit();
}

if ($action == 'delete_reward') {
    $id = $_GET['id'];
    $conn->query("DELETE FROM rewards WHERE id = $id");
    header("Location: dashboard_teacher.php?page=shop&msg=deleted");
    exit();
}

if ($action == 'send_message') {
    $parent_id = $_POST['parent_id'];
    $message = $_POST['message'];
    $teacher_id = $_SESSION['user_id'];
    
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $teacher_id, $parent_id, $message);
        $stmt->execute();
    }
    
    header("Location: dashboard_teacher.php?page=chat&pid=" . $parent_id);
    exit();
}

?>