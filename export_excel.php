<?php
include('db_connect.php');

// รับค่ากรอง
$classroom = $_GET['classroom'] ?? '';
$year_level = $_GET['year_level'] ?? '';

// สร้างชื่อไฟล์
$filename = "student_report_" . date('Ymd') . ".xls";

// แจ้ง Browser ว่าเป็นไฟล์ Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// SQL ดึงข้อมูล (Join 4 ตาราง: users(เด็ก) -> student_meta -> parent_meta -> users(ผู้ปกครอง))
$sql = "SELECT 
            u_st.fullname AS student_name, 
            sm.student_code, 
            sm.classroom, 
            sm.year_level,
            u_pa.fullname AS parent_name, 
            u_pa.phone AS parent_phone,
            pm.relation
        FROM users u_st
        JOIN student_meta sm ON u_st.id = sm.user_id
        LEFT JOIN parent_meta pm ON sm.student_code = pm.child_student_code
        LEFT JOIN users u_pa ON pm.user_id = u_pa.id
        WHERE u_st.role = 'student' ";

if($classroom) $sql .= " AND sm.classroom = '$classroom' ";
if($year_level) $sql .= " AND sm.year_level = '$year_level' ";

$sql .= " ORDER BY sm.classroom ASC, sm.student_code ASC";

$result = $conn->query($sql);
?>

<table border="1">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th>รหัสนักเรียน</th>
            <th>ชื่อ-นามสกุล นักเรียน</th>
            <th>ชั้นปี</th>
            <th>ห้อง</th>
            <th>ชื่อผู้ปกครอง</th>
            <th>ความสัมพันธ์</th>
            <th>เบอร์โทรผู้ปกครอง</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['student_code']; ?></td>
            <td><?php echo $row['student_name']; ?></td>
            <td><?php echo $row['year_level']; ?></td>
            <td><?php echo $row['classroom']; ?></td>
            <td><?php echo $row['parent_name'] ? $row['parent_name'] : '-'; ?></td>
            <td><?php echo $row['relation'] ? $row['relation'] : '-'; ?></td>
            <td><?php echo $row['parent_phone'] ? $row['parent_phone'] : '-'; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>