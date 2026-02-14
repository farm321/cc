<?php
session_start();
// ... (code ตรวจสอบ username/password จาก db) ...

if ($login_success) {
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['role'] = $row['role'];
    $_SESSION['fullname'] = $row['fullname'];

    // แยกทางเดิน
    if ($row['role'] == 'teacher') {
        header("Location: dashboard_teacher.php");
    } elseif ($row['role'] == 'student') {
        header("Location: dashboard_student.php");
    } else {
        header("Location: dashboard_parent.php");
    }
}
?>