<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'parent') {
    die("Access Denied");
}

$parent_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// --- ส่งข้อความหาครู ---
if ($action == 'send_message') {
    $teacher_id = $_POST['teacher_id']; // รับ ID ครูที่จะส่งหา
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $parent_id, $teacher_id, $message);
        
        if ($stmt->execute()) {
            // ดึง child_id ถ้ามี (เพื่อรักษา state)
            $child_param = isset($_GET['child_id']) ? '&child_id=' . $_GET['child_id'] : '';
            header("Location: dashboard_parent.php?page=chat&tid=" . $teacher_id . $child_param . "&msg=sent");
        } else {
            $child_param = isset($_GET['child_id']) ? '&child_id=' . $_GET['child_id'] : '';
            header("Location: dashboard_parent.php?page=chat&tid=" . $teacher_id . $child_param . "&err=failed");
        }
    } else {
        // ข้อความว่างเปล่า
        $child_param = isset($_GET['child_id']) ? '&child_id=' . $_GET['child_id'] : '';
        header("Location: dashboard_parent.php?page=chat&tid=" . $teacher_id . $child_param . "&err=empty");
    }
    exit();
}

// ถ้ามี action อื่นที่ไม่รู้จัก
header("Location: dashboard_parent.php");
exit();
?>