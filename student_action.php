<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    die("Access Denied");
}

$student_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// --- ฟังก์ชันคำนวณแต้มคงเหลือ ---
function getMyBalance($conn, $student_id) {
    // 1. หาคะแนนพฤติกรรมรวม
    $sql_score = "SELECT COALESCE(SUM(score), 0) as total FROM behavior_logs WHERE student_id = ?";
    $stmt = $conn->prepare($sql_score);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $score = $stmt->get_result()->fetch_assoc()['total'];

    // 2. หาคะแนนที่ใช้แลกของไปแล้ว (ไม่นับรายการที่ถูกปฏิเสธ)
    $sql_spent = "SELECT COALESCE(SUM(r.point_cost), 0) as spent 
                  FROM redemption_logs l 
                  JOIN rewards r ON l.reward_id = r.id 
                  WHERE l.student_id = ? AND l.status != 'rejected'";
    $stmt2 = $conn->prepare($sql_spent);
    $stmt2->bind_param("i", $student_id);
    $stmt2->execute();
    $spent = $stmt2->get_result()->fetch_assoc()['spent'];

    return $score - $spent;
}

// --- แลกของรางวัล (Redeem) ---
if ($action == 'redeem') {
    $reward_id = $_POST['reward_id'];
    
    // ดึงข้อมูลสินค้า
    $res = $conn->query("SELECT * FROM rewards WHERE id = $reward_id");
    $item = $res->fetch_assoc();
    
    // เช็ค 1: ของหมดไหม?
    if ($item['stock'] <= 0) {
        header("Location: dashboard_student.php?msg=out_of_stock");
        exit();
    }

    // เช็ค 2: แต้มพอไหม?
    $balance = getMyBalance($conn, $student_id);
    if ($balance < $item['point_cost']) {
        header("Location: dashboard_student.php?msg=not_enough_points");
        exit();
    }

    // ผ่านฉลุย! ทำการบันทึก
    // 1. บันทึกการแลก
    $stmt = $conn->prepare("INSERT INTO redemption_logs (student_id, reward_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $student_id, $reward_id);
    $stmt->execute();

    // 2. ตัดสต็อก
    $conn->query("UPDATE rewards SET stock = stock - 1 WHERE id = $reward_id");

    // ส่งชื่อของรางวัลกลับไปด้วยเพื่อโชว์ SweetAlert
header("Location: dashboard_student.php?msg=redeem_success&item_name=" . urlencode($item['name']));
    exit();
}
?>