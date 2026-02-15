<?php
// ข้อมูลการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "school60_fa_db"; // เปลี่ยนตามของคุณ
$password = "TgBPAKHRTAucQNrVfXJK"; // เปลี่ยนตามของคุณ
$dbname = "school60_fa_db"; // ชื่อฐานข้อมูลตาม SQL file ที่คุณอัปโหลด

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'การเชื่อมต่อฐานข้อมูลล้มเหลว: ' . $conn->connect_error
    ]));
}

// ตั้งค่า charset เป็น utf8
$conn->set_charset("utf8");
?>