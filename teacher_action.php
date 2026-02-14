<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    die("Access Denied");
}

$action = $_GET['action'] ?? '';

// --- ฟังก์ชันอัปโหลดรูปภาพ ---
function uploadImage($file) {
    if(isset($file) && $file['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); } // สร้างโฟลเดอร์ถ้าไม่มี
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION); // นามสกุลไฟล์
        $new_name = uniqid() . "." . $ext; // ตั้งชื่อใหม่กันซ้ำ
        $target_file = $target_dir . $new_name;
        
        // ตรวจสอบว่าเป็นรูปภาพจริงไหม
        $check = getimagesize($file["tmp_name"]);
        if($check !== false) {
            if(move_uploaded_file($file['tmp_name'], $target_file)) {
                return $new_name;
            }
        }
    }
    return null;
}

// ------------------------------------------
// 1. จัดการนักเรียน (เพิ่ม / แก้ไข / ลบ)
// ------------------------------------------
if ($action == 'add_student') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $st_code = $_POST['student_code'];
    $year_level = $_POST['year_level']; // รับค่าระดับชั้น (เป็นตัวเลข 1, 2, 3)
    $class = $_POST['classroom'];       // รับค่าห้อง
    
    // อัปโหลดรูป (ถ้ามี)
    $profile_img = null;
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $profile_img = uploadImage($_FILES['profile_image']);
    }

    // บันทึก User
    $sql = "INSERT INTO users (username, password, fullname, role, profile_img) VALUES (?, ?, ?, 'student', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $fullname, $profile_img);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        // บันทึกข้อมูล meta (year_level เป็น INT)
        $sql_meta = "INSERT INTO student_meta (user_id, student_code, year_level, classroom) VALUES (?, ?, ?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("isis", $user_id, $st_code, $year_level, $class);
        $stmt_meta->execute();
        
        header("Location: dashboard_teacher.php?page=students&msg=added");
    } else {
        echo "Error: " . $conn->error;
    }
    exit();
}

if ($action == 'edit_student') {
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $st_code = $_POST['student_code'];
    $year_level = $_POST['year_level']; // รับค่าระดับชั้น (เป็นตัวเลข 1, 2, 3)
    $class = $_POST['classroom'];       // รับค่าห้อง
    
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
    
    // อัปเดต Meta (year_level เป็น INT)
    $conn->query("UPDATE student_meta SET student_code = '$st_code', year_level = $year_level, classroom = '$class' WHERE user_id = $id");

    header("Location: dashboard_teacher.php?page=students&msg=updated");
    exit();
}

if ($action == 'delete_student') {
    $id = intval($_GET['id']); // แปลงเป็นตัวเลขเพื่อความปลอดภัย

    // 1. ลบประวัติการทำความดี/ความผิด ของนักเรียนคนนี้
    $conn->query("DELETE FROM behavior_logs WHERE student_id = $id");

    // 2. ลบประวัติการแลกของรางวัล ของนักเรียนคนนี้
    $conn->query("DELETE FROM redemption_logs WHERE student_id = $id");

    // 3. ลบข้อมูล Meta (ระดับชั้น/ห้อง)
    $conn->query("DELETE FROM student_meta WHERE user_id = $id");

    // 4. ลบ User หลักออกจากระบบ
    $del = $conn->query("DELETE FROM users WHERE id = $id");

    if ($del) {
        header("Location: dashboard_teacher.php?page=students&msg=deleted");
    } else {
        echo "Error deleting user: " . $conn->error;
    }
    exit();
}

// ------------------------------------------
// 2. จัดการพฤติกรรม (แก้ไขให้บันทึกคะแนน bad เป็นลบอัตโนมัติ)
// ------------------------------------------
if ($action == 'add_behavior_config' || $action == 'add_behavior') {
    $title = $_POST['title'];
    $score = intval($_POST['score']);
    $type = $_POST['type'] ?? (($score >= 0) ? 'good' : 'bad');
    
    // ===== แก้ไขส่วนนี้ =====
    // ถ้าเป็น bad ให้บันทึกเป็นลบ
    if ($type == 'bad') {
        $score = -abs($score); // บังคับให้เป็นลบ
    } else {
        $score = abs($score); // บังคับให้เป็นบวก
    }
    // ===== จบส่วนแก้ไข =====
    
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
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $child_code = $_POST['child_student_code']; // รับรหัสนักเรียนของลูก
    
    // บันทึก User ผู้ปกครอง
    $sql_user = "INSERT INTO users (username, password, role, fullname) VALUES (?, ?, 'parent', ?)";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("sss", $username, $password, $fullname);
    
    if($stmt->execute()) {
        $parent_id = $stmt->insert_id;
        
        // บันทึกข้อมูลเชื่อมโยงในตาราง parent_meta
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
    
    // เช็คว่ามีการเปลี่ยนรหัสผ่านไหม
    $pwd_sql = "";
    if(!empty($_POST['password'])) {
        $pwd = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pwd_sql = ", password = '$pwd'";
    }

    // อัปเดต Users
    $conn->query("UPDATE users SET fullname = '$fullname' $pwd_sql WHERE id = $id");
    
    // อัปเดต parent_meta
    $conn->query("UPDATE parent_meta SET child_student_code = '$child_code' WHERE user_id = $id");

    header("Location: dashboard_teacher.php?page=parents&msg=updated");
    exit();
}

if ($action == 'delete_parent') {
    $id = intval($_GET['id']);

    // 1. ลบข้อมูลการเชื่อมโยงลูกหลาน
    $conn->query("DELETE FROM parent_meta WHERE user_id = $id");

    // 2. ลบประวัติคะแนนที่ผู้ปกครองคนนี้เคยให้ลูก
    $conn->query("DELETE FROM behavior_logs WHERE teacher_id = $id");

    // 3. ลบข้อความแชท (ถ้ามี)
    $conn->query("DELETE FROM messages WHERE sender_id = $id OR receiver_id = $id");

    // 4. ลบ User หลักออกจากระบบ
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
    $new_child_code = $_POST['new_child_code'];
    
    // เช็คก่อนว่ามีลูกคนนี้ในระบบจริงไหม
    $chk = $conn->query("SELECT * FROM student_meta WHERE student_code = '$new_child_code'");
    if($chk->num_rows == 0) {
        header("Location: dashboard_teacher.php?page=parents&err=student_not_found");
        exit();
    }

    // เพิ่มข้อมูลลง parent_meta
    $stmt = $conn->prepare("INSERT INTO parent_meta (user_id, child_student_code) VALUES (?, ?)");
    $stmt->bind_param("is", $parent_user_id, $new_child_code);
    
    if($stmt->execute()) {
        header("Location: dashboard_teacher.php?page=parents&msg=child_added");
    } else {
        header("Location: dashboard_teacher.php?page=parents&err=duplicate");
    }
    exit();
}

// ------------------------------------------
// 4. ให้คะแนน (แก้ไขให้ใช้คะแนนจาก config อย่างถูกต้อง)
// ------------------------------------------
if ($action == 'save_score') {
    $student_id = $_POST['student_id'];
    $teacher_id = $_SESSION['user_id'];
    $config_id = $_POST['config_id'];
    $detail = $_POST['detail'];

    $res = $conn->query("SELECT * FROM behavior_config WHERE id = $config_id");
    $cfg = $res->fetch_assoc();
    
    // ===== แก้ไขส่วนนี้ =====
    // ใช้คะแนนจาก config โดยตรง (ไม่ต้องแปลงเพราะเก็บไว้ถูกต้องแล้ว)
    $final_score = $cfg['score'];
    // ===== จบส่วนแก้ไข =====

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
    
    // ส่งกลับหน้าแชทเดิม
    header("Location: dashboard_teacher.php?page=chat&pid=" . $parent_id);
    exit();
}

?>