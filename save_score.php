<?php
session_start();
include('db_connect.php');

// เช็คว่าเป็นครูไหม
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $teacher_id = $_SESSION['user_id'];
    $behavior_type = $_POST['behavior_type']; // good หรือ bad
    $title_score = explode('|', $_POST['title_score']); // แยกชื่อเรื่องกับคะแนนออกจากกัน (เช่น "มาสาย|-5")
    
    $title = $title_score[0];
    $score = $title_score[1];
    $detail = $_POST['detail'];

    // บันทึกลงฐานข้อมูล
    $sql = "INSERT INTO behavior_logs (student_id, teacher_id, behavior_type, title, detail, score) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssi", $student_id, $teacher_id, $behavior_type, $title, $detail, $score);

    if ($stmt->execute()) {
        // บันทึกสำเร็จ ส่งกลับไปหน้า Dashboard พร้อมแจ้งเตือน
        echo "<script>
            alert('บันทึกพฤติกรรมเรียบร้อย! ✅');
            window.location.href = 'dashboard_teacher.php';
        </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>