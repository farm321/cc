<?php
session_start();
include('db_connect.php');

$username = $_POST['username'];
$password = $_POST['password'];

// ค้นหา User จาก username
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // ตรวจสอบรหัสผ่าน (Hash Verify)
    if (password_verify($password, $row['password'])) {
        // Login สำเร็จ! เก็บ Session
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['fullname'] = $row['fullname'];

        // ส่งไปหน้า Dashboard ตาม Role
        if ($row['role'] == 'teacher') {
            header("Location: dashboard_teacher.php");
        } elseif ($row['role'] == 'student') {
            header("Location: dashboard_student.php");
        } elseif ($row['role'] == 'parent') {
            header("Location: dashboard_parent.php");
        }
    } else {
        echo "<script>alert('รหัสผ่านไม่ถูกต้อง ❌'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('ไม่พบชื่อผู้ใช้งานนี้ ❌'); window.history.back();</script>";
}
?>