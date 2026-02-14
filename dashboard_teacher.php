<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    // ถ้ายังไม่ได้ล็อกอิน ให้เด้งออก หรือถ้าเป็นการทดสอบให้สร้าง Session จำลอง
    // header("Location: index.php"); exit();
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'teacher';
    $_SESSION['fullname'] = 'Admin Test';
}

$page = $_GET['page'] ?? 'home';
$filter_class = $_GET['class'] ?? '';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Super Admin - School System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f0f2f5;
        }

        /* --- Header & Navbar Styles (คงเดิม) --- */
        .hero-header {
            position: relative;
            height: 350px;
            overflow: hidden;
            border-radius: 0 0 40px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin-bottom: -60px;
        }

        .hero-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.6);
        }

        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -60%);
            text-align: center;
            color: white;
            width: 100%;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            text-transform: uppercase;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            letter-spacing: 2px;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 90%;
            margin: 0 auto 30px auto;
            border-radius: 50px;
            position: relative;
            z-index: 10;
        }

        /* --- Card Styles (คงเดิม) --- */
        .menu-card {
            border: none;
            border-radius: 20px;
            text-align: center;
            padding: 25px;
            transition: 0.3s;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .icon-box {
            font-size: 2.5rem;
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
            margin: 0 auto 15px;
        }

        /* Theme Colors */
        .theme-blue .icon-box {
            background: #E3F2FD;
            color: #1976D2;
        }

        .theme-pink .icon-box {
            background: #FCE4EC;
            color: #C2185B;
        }

        .theme-orange .icon-box {
            background: #FFF3E0;
            color: #E65100;
        }

        .theme-green .icon-box {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .theme-purple .icon-box {
            background: #F3E5F5;
            color: #7B1FA2;
        }

        .theme-teal .icon-box {
            background: #E0F2F1;
            color: #00796B;
        }

        /* เพิ่มสีใหม่สำหรับแชท */
        .theme-indigo .icon-box {
            background: #E8EAF6;
            color: #3F51B5;
        }

        /* --- Chat Styles (เพิ่มใหม่) --- */
        .chat-box {
            height: 450px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
        }

        .message.me {
            justify-content: flex-end;
        }

        .msg-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 20px;
            position: relative;
            font-size: 0.95rem;
        }

        .message.me .msg-bubble {
            background: #007bff;
            color: white;
            border-bottom-right-radius: 0;
        }

        .message.other .msg-bubble {
            background: white;
            border: 1px solid #ddd;
            border-bottom-left-radius: 0;
        }
    </style>
</head>

<body>

    <div class="hero-header">
        <img src="images/banner.gif" onerror="this.src='https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop'" alt="School Banner" class="hero-img">
        <div class="hero-content">
            <h1 class="hero-title">SCHOOL COMMAND</h1>
            <p class="fs-5"><i class="bi bi-person-badge-fill"></i> ยินดีต้อนรับ: อ.<?php echo $_SESSION['fullname']; ?></p>
        </div>
    </div>

    <nav class="navbar navbar-expand navbar-custom">
        <div class="container px-4">
            <a class="navbar-brand fw-bold text-primary" href="dashboard_teacher.php">
                <i class="bi bi-grid-fill"></i> DASHBOARD
            </a>
            <div class="ms-auto">
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-4">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">

        <?php if ($page == 'home'): ?>
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 20px;">
                        <h5 class="fw-bold mb-4 text-secondary">เมนูหลัก (Main Menu)</h5>
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <a href="?page=students" class="menu-card theme-blue">
                                    <div class="icon-box"><i class="bi bi-people-fill"></i></div>
                                    <h6>นักเรียน</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="?page=parents" class="menu-card theme-pink">
                                    <div class="icon-box"><i class="bi bi-person-heart"></i></div>
                                    <h6>ผู้ปกครอง</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="?page=behavior" class="menu-card theme-orange">
                                    <div class="icon-box"><i class="bi bi-sliders"></i></div>
                                    <h6>ตั้งค่าคะแนน</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="?page=shop" class="menu-card theme-purple">
                                    <div class="icon-box"><i class="bi bi-shop"></i></div>
                                    <h6>ร้านค้ารางวัล</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="?page=news" class="menu-card theme-teal">
                                    <div class="icon-box"><i class="bi bi-megaphone-fill"></i></div>
                                    <h6>ประกาศข่าว</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="export_excel.php" target="_blank" class="menu-card theme-green">
                                    <div class="icon-box"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                                    <h6>Export</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="?page=chat_list" class="menu-card theme-indigo">
                                    <div class="icon-box"><i class="bi bi-chat-dots-fill"></i></div>
                                    <h6>แชทผู้ปกครอง</h6>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; overflow: hidden;">
                        <div class="card-header bg-dark text-white fw-bold text-center py-3">
                            <i class="bi bi-trophy-fill text-warning"></i> 5 อันดับเด็กดี
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php
                            $sql_top = "SELECT u.fullname, sm.classroom, COALESCE(SUM(b.score), 0) as total 
                                    FROM users u 
                                    JOIN student_meta sm ON u.id = sm.user_id 
                                    LEFT JOIN behavior_logs b ON u.id = b.student_id 
                                    WHERE u.role = 'student' 
                                    GROUP BY u.id 
                                    ORDER BY total DESC LIMIT 5";
                            $res_top = $conn->query($sql_top);
                            if ($res_top) {
                                $rank = 1;
                                while ($top = $res_top->fetch_assoc()):
                            ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <span class="badge rounded-pill bg-<?php echo $rank == 1 ? 'danger' : ($rank == 2 ? 'warning' : 'secondary'); ?> me-2">
                                                #<?php echo $rank++; ?>
                                            </span>
                                            <?php echo $top['fullname']; ?>
                                            <small class="text-muted d-block ps-5" style="margin-top:-3px;">ห้อง <?php echo $top['classroom']; ?></small>
                                        </div>
                                        <span class="fw-bold text-primary"><?php echo $top['total']; ?></span>
                                    </li>
                            <?php endwhile;
                            } ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page == 'students'):
            // 1. รับค่าตัวแปรระดับชั้น (ถ้าไม่มีให้เป็นค่าว่าง)
            $selected_level = $_GET['level'] ?? '';
        ?>
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <h4>👨‍🎓 จัดการนักเรียน</h4>
                <a href="dashboard_teacher.php" class="btn btn-secondary btn-sm rounded-pill">กลับหน้าหลัก</a>
            </div>

            <div class="card p-3 mb-3 border-0 shadow-sm rounded-4">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="page" value="students">

                    <div class="col-md-6">
                        <div class="nav nav-pills">
                            <a href="?page=students" class="nav-link rounded-pill border btn-sm me-1 <?php echo $selected_level == '' ? 'active bg-primary' : 'text-muted'; ?>">ทั้งหมด</a>
                            <a href="?page=students&level=1" class="nav-link rounded-pill border btn-sm me-1 <?php echo $selected_level == '1' ? 'active bg-primary' : 'text-muted'; ?>">ม.1</a>
                            <a href="?page=students&level=2" class="nav-link rounded-pill border btn-sm me-1 <?php echo $selected_level == '2' ? 'active bg-primary' : 'text-muted'; ?>">ม.2</a>
                            <a href="?page=students&level=3" class="nav-link rounded-pill border btn-sm me-1 <?php echo $selected_level == '3' ? 'active bg-primary' : 'text-muted'; ?>">ม.3</a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <input type="text" name="class" class="form-control" placeholder="ระบุห้อง (เช่น 1)..." value="<?php echo htmlspecialchars($filter_class); ?>">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">ค้นหา</button>
                    </div>

                    <div class="col-12 text-end mt-2">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="bi bi-person-plus-fill"></i> เพิ่มนักเรียนใหม่
                        </button>
                    </div>
                </form>
            </div>

            <div class="row g-3">
                <?php
                // สร้าง Query
                $sql = "SELECT users.*, sm.student_code, sm.year_level, sm.classroom, COALESCE(SUM(bl.score), 0) as total_score 
                    FROM users 
                    JOIN student_meta sm ON users.id = sm.user_id 
                    LEFT JOIN behavior_logs bl ON users.id = bl.student_id 
                    WHERE users.role = 'student' ";

                // 1. กรองตามห้อง (จากช่อง Input)
                if ($filter_class) $sql .= " AND sm.classroom LIKE '%$filter_class%' ";

                // 2. กรองตามระดับชั้น (จากปุ่มกด)
                if ($selected_level) $sql .= " AND sm.year_level = '$selected_level' ";

                $sql .= " GROUP BY users.id ORDER BY sm.year_level ASC, sm.classroom ASC";

                $res = $conn->query($sql);

                // วนลูปแสดงการ์ด
                if ($res): while ($row = $res->fetch_assoc()):
                        // เช็ครูปภาพ
                        $img_src = "https://api.dicebear.com/7.x/avataaars/svg?seed=" . $row['username'];
                        if (!empty($row['profile_image']) && file_exists("uploads/" . $row['profile_image'])) {
                            $img_src = "uploads/" . $row['profile_image'];
                        }
                ?>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm p-3 text-center h-100 rounded-4 position-relative">
                                <div class="dropdown position-absolute top-0 end-0 m-2">
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                        <li><a class="dropdown-item small" href="#" onclick="openEditStudentModal(
                                '<?php echo $row['id']; ?>', 
                                '<?php echo htmlspecialchars($row['username']); ?>', 
                                '<?php echo htmlspecialchars($row['fullname']); ?>', 
                                '<?php echo htmlspecialchars($row['student_code']); ?>', 
                                '<?php echo htmlspecialchars($row['year_level']); ?>', 
                                '<?php echo htmlspecialchars($row['classroom']); ?>'
                            )"><i class="bi bi-pencil text-warning"></i> แก้ไข</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item small text-danger" href="teacher_action.php?action=delete_student&id=<?php echo $row['id']; ?>" onclick="return confirm('ยืนยันการลบ?');"><i class="bi bi-trash"></i> ลบ</a></li>
                                    </ul>
                                </div>

                                <span class="badge bg-info text-dark position-absolute top-0 start-0 m-2 shadow-sm">
                                    ม.<?php echo $row['year_level']; ?>/<?php echo $row['classroom']; ?>
                                </span>

                                <img src="<?php echo $img_src; ?>" class="rounded-circle mx-auto mt-3 student-img" width="80" height="80" style="object-fit:cover;">

                                <h6 class="mt-3 text-truncate fw-bold mb-0"><?php echo $row['fullname']; ?></h6>
                                <small class="text-muted mb-2 d-block">รหัส: <?php echo $row['student_code']; ?></small>

                                <h4 class="<?php echo $row['total_score'] >= 0 ? 'text-success' : 'text-danger'; ?> fw-bold my-2"><?php echo $row['total_score']; ?></h4>

                                <button class="btn btn-sm btn-primary rounded-pill mt-auto w-100" onclick="openScoreModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['fullname']); ?>')">
                                    <i class="bi bi-star"></i> ให้คะแนน
                                </button>
                            </div>
                        </div>
                <?php endwhile;
                endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($page == 'chat_list'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <h4>💬 รายชื่อผู้ปกครองที่ติดต่อ</h4>
                <a href="dashboard_teacher.php" class="btn btn-secondary btn-sm rounded-pill">กลับหน้าหลัก</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 p-3">
                        <div class="list-group list-group-flush">
                            <?php
                            // ดึงรายชื่อผู้ปกครอง (ในที่นี้ดึงทั้งหมด ถ้าต้องการดึงเฉพาะที่เคยคุยต้องแก้ SQL)
                            $parents = $conn->query("SELECT * FROM users WHERE role='parent' ORDER BY id DESC");

                            if ($parents->num_rows > 0) {
                                while ($p = $parents->fetch_assoc()):
                                    // นับข้อความ
                                    // สมมติว่ามีตาราง messages: id, sender_id, receiver_id, message, created_at
                                    // เช็คว่ามี table messages หรือยัง ถ้ายังอาจ error (ต้องสร้าง table ก่อน)
                                    $msg_count = 0;
                                    $chk_msg = $conn->query("SHOW TABLES LIKE 'messages'");
                                    if ($chk_msg->num_rows > 0) {
                                        $msg_sql = "SELECT COUNT(*) FROM messages WHERE sender_id={$p['id']} AND receiver_id={$_SESSION['user_id']}";
                                        $msg_res = $conn->query($msg_sql);
                                        if ($msg_res) $msg_count = $msg_res->fetch_row()[0];
                                    }
                            ?>
                                    <a href="?page=chat&pid=<?php echo $p['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-3 text-secondary">
                                                <i class="bi bi-person-fill fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?php echo $p['fullname']; ?></h6>
                                                <small class="text-muted">ผู้ปกครอง</small>
                                            </div>
                                        </div>
                                        <?php if ($msg_count > 0): ?>
                                            <span class="badge bg-danger rounded-pill"><?php echo $msg_count; ?> ข้อความ</span>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill">แชท</button>
                                        <?php endif; ?>
                                    </a>
                            <?php endwhile;
                            } else {
                                echo "<div class='text-center py-5 text-muted'>ยังไม่มีข้อมูลผู้ปกครองในระบบ</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page == 'chat'):
            $pid = $_GET['pid'] ?? 0;
            $parent_info = $conn->query("SELECT fullname FROM users WHERE id=$pid")->fetch_assoc();
        ?>
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <h4><i class="bi bi-chat-text text-primary"></i> แชทกับ: <?php echo $parent_info['fullname'] ?? 'ไม่พบชื่อ'; ?></h4>
                <a href="?page=chat_list" class="btn btn-secondary btn-sm rounded-pill">ย้อนกลับ</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="chat-box mb-3" id="chatContainer">
                                <?php
                                $tid = $_SESSION['user_id'];
                                // เช็คตาราง messages
                                $chk_msg_table = $conn->query("SHOW TABLES LIKE 'messages'");
                                if ($chk_msg_table->num_rows > 0) {
                                    $msgs = $conn->query("SELECT * FROM messages WHERE (sender_id=$tid AND receiver_id=$pid) OR (sender_id=$pid AND receiver_id=$tid) ORDER BY created_at ASC");
                                    if ($msgs->num_rows > 0) {
                                        while ($m = $msgs->fetch_assoc()):
                                            $me = ($m['sender_id'] == $tid);
                                ?>
                                            <div class="message <?php echo $me ? 'me' : 'other'; ?>">
                                                <div class="msg-bubble">
                                                    <?php echo htmlspecialchars($m['message']); ?>
                                                    <div class="text-end" style="font-size:0.65rem; opacity:0.7; margin-top:3px;">
                                                        <?php echo date('H:i', strtotime($m['created_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                <?php endwhile;
                                    } else {
                                        echo "<p class='text-center text-muted mt-5'>ยังไม่มีการสนทนา เริ่มทักทายเลย!</p>";
                                    }
                                } else {
                                    echo "<p class='text-center text-danger'>ไม่พบตาราง messages กรุณาสร้างฐานข้อมูลก่อน</p>";
                                }
                                ?>
                            </div>

                            <form action="teacher_action.php?action=send_message" method="POST">
                                <input type="hidden" name="parent_id" value="<?php echo $pid; ?>">
                                <div class="input-group">
                                    <input type="text" name="message" class="form-control rounded-pill bg-light" placeholder="พิมพ์ข้อความ..." required autocomplete="off">
                                    <button class="btn btn-primary rounded-pill ms-2 px-4"><i class="bi bi-send-fill"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                // เลื่อนแชทลงล่างสุด
                var chatBox = document.getElementById('chatContainer');
                if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
            </script>
        <?php endif; ?>

        <?php if ($page == 'parents'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <h4>👨‍👩‍👧‍👦 จัดการผู้ปกครอง</h4>
                <a href="dashboard_teacher.php" class="btn btn-secondary btn-sm rounded-pill">กลับหน้าหลัก</a>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm p-4 rounded-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-plus-fill"></i> เพิ่มผู้ปกครองใหม่</h6>
                        <form action="teacher_action.php?action=add_parent" method="POST">
                            <div class="mb-3">
                                <label class="form-label small text-muted">ชื่อ-นามสกุล ผู้ปกครอง</label>
                                <input type="text" name="fullname" class="form-control rounded-pill" placeholder="เช่น นายสมชาย ใจดี" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Username (เบอร์โทร)</label>
                                <input type="text" name="username" class="form-control rounded-pill" placeholder="ใช้สำหรับล็อกอิน" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">รหัสผ่าน</label>
                                <input type="password" name="password" class="form-control rounded-pill" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">รหัสนักเรียน (บุตรหลาน)</label>
                                <input type="text" name="child_student_code" class="form-control rounded-pill" placeholder="ระบุรหัสนักเรียน เช่น ST001" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                                <i class="bi bi-save"></i> บันทึกข้อมูล
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-4 rounded-4">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-list-ul"></i> รายชื่อผู้ปกครองในระบบ</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>นักเรียนในความดูแล</th>
                                        <th class="text-end">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $parents = $conn->query("SELECT * FROM users WHERE role = 'parent'");
                                    while ($p = $parents->fetch_assoc()):
                                        // ดึงข้อมูลลูกๆ ของผู้ปกครองคนนี้
                                        $p_id = $p['id'];
                                        $kids = $conn->query("SELECT child_student_code FROM parent_meta WHERE user_id = $p_id");
                                        $kid_list = [];
                                        while ($k = $kids->fetch_assoc()) $kid_list[] = $k['child_student_code'];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex justify-content-center align-items-center" style="width:35px;height:35px;">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold d-block"><?php echo $p['fullname']; ?></span>
                                                        <small class="text-muted" style="font-size: 0.8rem;">User: <?php echo $p['username']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (count($kid_list) > 0): ?>
                                                    <?php foreach ($kid_list as $kc): ?>
                                                        <span class="badge rounded-pill bg-info text-dark mb-1">
                                                            <i class="bi bi-backpack"></i> <?php echo $kc; ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="text-muted small">- ไม่มีข้อมูล -</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-success rounded-pill me-1"
                                                    onclick="openAddChildModal('<?php echo $p['id']; ?>', '<?php echo htmlspecialchars($p['fullname']); ?>')">
                                                    <i class="bi bi-person-plus-fill"></i> เพิ่มลูก
                                                </button>

                                                <a href="teacher_action.php?action=delete_parent&id=<?php echo $p['id']; ?>"
                                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                                    onclick="return confirm('⚠️ ยืนยันการลบ?\n\nการลบผู้ปกครองจะทำให้ประวัติแชทและการให้คะแนนทั้งหมดหายไปด้วย!');">
                                                    <i class="bi bi-trash"></i> ลบ
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page == 'behavior'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <h4>⚙️ ตั้งค่าคะแนนพฤติกรรม</h4>
                <a href="dashboard_teacher.php" class="btn btn-secondary btn-sm rounded-pill">กลับหน้าหลัก</a>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 rounded-4 mb-3" style="background-color: #FFF3E0;">
                        <h6 class="fw-bold mb-3 text-dark">เพิ่มหัวข้อพฤติกรรม</h6>
                        <form action="teacher_action.php?action=add_behavior_config" method="POST">
                            <div class="mb-2"><input type="text" name="title" class="form-control" required placeholder="ชื่อพฤติกรรม"></div>
                            <div class="mb-2"><input type="number" name="score" class="form-control" required placeholder="คะแนน (ตัวเลข)"></div>
                            <div class="mb-3">
                                <select name="type" class="form-select">
                                    <option value="good">✅ พฤติกรรมดี (บวก)</option>
                                    <option value="bad">❌ ไม่เหมาะสม (ลบ)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-warning w-100 fw-bold">บันทึก</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-header bg-success text-white">พฤติกรรมดี (Good)</div>
                                <ul class="list-group list-group-flush">
                                    <?php $bgood = $conn->query("SELECT * FROM behavior_config WHERE type='good' ORDER BY score ASC");
                                    while ($g = $bgood->fetch_assoc()): ?>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span><?php echo $g['title']; ?> <span class="badge bg-success">+<?php echo $g['score']; ?></span></span>
                                            <a href="teacher_action.php?action=delete_behavior_config&id=<?php echo $g['id']; ?>" class="text-danger"><i class="bi bi-x"></i></a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-header bg-danger text-white">ไม่เหมาะสม (Bad)</div>
                                <ul class="list-group list-group-flush">
                                    <?php $bbad = $conn->query("SELECT * FROM behavior_config WHERE type='bad' ORDER BY score ASC");
                                    while ($b = $bbad->fetch_assoc()): ?>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span><?php echo $b['title']; ?> <span class="badge bg-danger">-<?php echo abs($b['score']); ?></span></span>
                                            <a href="teacher_action.php?action=delete_behavior_config&id=<?php echo $b['id']; ?>" class="text-danger"><i class="bi bi-x"></i></a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page == 'shop'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <h4>🛍️ จัดการร้านค้า</h4>
                <a href="dashboard_teacher.php" class="btn btn-secondary btn-sm rounded-pill">กลับหน้าหลัก</a>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 rounded-4">
                        <h6 class="fw-bold">เพิ่มของรางวัล</h6>
                        <form action="teacher_action.php?action=add_reward" method="POST">
                            <div class="mb-2"><input type="text" name="name" class="form-control" required placeholder="ชื่อรางวัล"></div>
                            <div class="mb-2"><input type="number" name="point_cost" class="form-control" required placeholder="แต้มที่ใช้แลก"></div>
                            <div class="mb-3"><input type="number" name="stock" class="form-control" value="1" placeholder="จำนวน"></div>
                            <button class="btn btn-purple text-white w-100" style="background-color: #7B1FA2;">ลงสินค้า</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row g-3">
                        <?php $shop = $conn->query("SELECT * FROM rewards ORDER BY point_cost ASC");
                        while ($item = $shop->fetch_assoc()): ?>
                            <div class="col-md-4 col-6">
                                <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-3">
                                    <i class="bi bi-gift-fill text-warning display-4"></i>
                                    <h6 class="mt-2 fw-bold"><?php echo $item['name']; ?></h6>
                                    <p class="text-primary fw-bold mb-1"><?php echo $item['point_cost']; ?> แต้ม</p>
                                    <small class="text-muted">เหลือ: <?php echo $item['stock']; ?></small>
                                    <a href="teacher_action.php?action=delete_reward&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger w-100 mt-2" onclick="return confirm('ลบ?');">ลบ</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page == 'news'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                <h4>📢 ประกาศข่าวสาร</h4>
                <a href="dashboard_teacher.php" class="btn btn-secondary btn-sm rounded-pill">กลับหน้าหลัก</a>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 rounded-4">
                        <h6 class="fw-bold">สร้างประกาศ</h6>
                        <form action="teacher_action.php?action=add_news" method="POST">
                            <div class="mb-2"><input type="text" name="title" class="form-control" required placeholder="หัวข้อ"></div>
                            <div class="mb-2">
                                <select name="type" class="form-select">
                                    <option value="info">ℹ️ ข่าวทั่วไป</option>
                                    <option value="warning">⚠️ แจ้งเตือน</option>
                                    <option value="activity">🎈 กิจกรรม</option>
                                </select>
                            </div>
                            <div class="mb-3"><textarea name="content" class="form-control" rows="3" placeholder="รายละเอียด"></textarea></div>
                            <button class="btn btn-primary w-100">ประกาศ</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-3 rounded-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>หัวข้อ</th>
                                    <th>ลบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $news = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
                                while ($n = $news->fetch_assoc()): ?>
                                    <tr>
                                        <td style="font-size:0.8rem;"><?php echo date('d/m', strtotime($n['created_at'])); ?></td>
                                        <td><?php echo $n['title']; ?></td>
                                        <td><a href="teacher_action.php?action=delete_news&id=<?php echo $n['id']; ?>" class="text-danger"><i class="bi bi-trash"></i></a></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <div class="modal fade" id="scoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <form action="teacher_action.php?action=save_score" method="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">📝 ให้คะแนน <span id="modal_st_name" class="text-primary"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="student_id" id="modal_st_id">
                        <div class="mb-3">
                            <select name="config_id" class="form-select form-select-lg" required>
                                <option value="" disabled selected>-- เลือกพฤติกรรม --</option>
                                <optgroup label="✅ พฤติกรรมดี">
                                    <?php $good = $conn->query("SELECT * FROM behavior_config WHERE type='good'");
                                    while ($g = $good->fetch_assoc()) echo "<option value='{$g['id']}'>{$g['title']} (+{$g['score']})</option>"; ?>
                                </optgroup>
                                <optgroup label="❌ พฤติกรรมไม่เหมาะสม">
                                    <?php $bad = $conn->query("SELECT * FROM behavior_config WHERE type='bad'");
                                    while ($b = $bad->fetch_assoc()) echo "<option value='{$b['id']}'>{$b['title']} ({$b['score']})</option>"; ?>
                                </optgroup>
                            </select>
                        </div>
                        <textarea name="detail" class="form-control" rows="2" placeholder="หมายเหตุ (ถ้ามี)"></textarea>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">บันทึกผล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <form action="teacher_action.php?action=add_student" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">เพิ่มนักเรียนใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 text-center">
                            <label class="form-label fw-bold">รูปโปรไฟล์</label>
                            <div class="mb-2">
                                <img id="preview_add" src="https://api.dicebear.com/7.x/avataaars/svg?seed=default" class="rounded-circle border border-3 border-primary" width="100" height="100" style="object-fit: cover;">
                            </div>
                            <input type="file" name="profile_image" class="form-control" accept="image/*" onchange="previewImage(event, 'preview_add')">
                            <small class="text-muted">เว้นว่างถ้าต้องการใช้รูปสุ่ม</small>
                        </div>
                        <div class="mb-2"><label>ชื่อ-นามสกุล</label><input type="text" name="fullname" class="form-control" required></div>
                        <div class="mb-2"><label>รหัสนักเรียน</label><input type="text" name="student_code" class="form-control" required></div>
                        <div class="mb-2">
                            <label>ระดับชั้น</label>
                            <select name="year_level" class="form-select" required>
                                <option value="">-- เลือกระดับชั้น --</option>
                                <option value="1">ม.1</option>
                                <option value="2">ม.2</option>
                                <option value="3">ม.3</option>
                            </select>
                        </div>
                        <div class="mb-2"><label>ห้องเรียน</label><input type="text" name="classroom" class="form-control" placeholder="เช่น 1/1" required></div>
                        <div class="mb-2"><label>Username</label><input type="text" name="username" class="form-control" required></div>
                        <div class="mb-2"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success w-100">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <form action="teacher_action.php?action=edit_student" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">แก้ไขข้อมูล</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3 text-center">
                            <label class="form-label fw-bold">รูปโปรไฟล์</label>
                            <div class="mb-2">
                                <img id="preview_edit" src="https://api.dicebear.com/7.x/avataaars/svg?seed=default" class="rounded-circle border border-3 border-warning" width="100" height="100" style="object-fit: cover;">
                            </div>
                            <input type="file" name="profile_image" class="form-control" accept="image/*" onchange="previewImage(event, 'preview_edit')">
                            <small class="text-muted">เว้นว่างถ้าไม่เปลี่ยนรูป</small>
                        </div>
                        <div class="mb-2"><label>ชื่อ-นามสกุล</label><input type="text" name="fullname" id="edit_fullname" class="form-control" required></div>
                        <div class="mb-2"><label>รหัสนักเรียน</label><input type="text" name="student_code" id="edit_code" class="form-control" required></div>
                        <div class="mb-2">
                            <label>ระดับชั้น</label>
                            <select name="year_level" id="edit_year_level" class="form-select" required>
                                <option value="">-- เลือกระดับชั้น --</option>
                                <option value="1">ม.1</option>
                                <option value="2">ม.2</option>
                                <option value="3">ม.3</option>
                            </select>
                        </div>
                        <div class="mb-2"><label>ห้องเรียน</label><input type="text" name="classroom" id="edit_classroom" class="form-control" required></div>
                        <div class="mb-2"><label>Username</label><input type="text" name="username" id="edit_username" class="form-control" required></div>
                        <div class="mb-2"><label>Password ใหม่</label><input type="password" name="password" class="form-control" placeholder="เว้นว่างถ้าไม่เปลี่ยน"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning w-100">อัพเดทข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addChildModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มนักเรียนในความดูแล</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="teacher_action.php?action=append_child_to_parent" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="parent_user_id" id="append_parent_id">
                        
                        <div class="mb-3">
                            <label>ชื่อผู้ปกครอง:</label>
                            <input type="text" class="form-control" id="append_parent_name" readonly disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label>รหัสนักเรียน (ของลูกคนใหม่):</label>
                            <input type="text" name="new_child_code" class="form-control" placeholder="กรอกรหัสนักเรียน เช่น ST002" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกเพิ่ม</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openScoreModal(id, name) {
            document.getElementById('modal_st_id').value = id;
            document.getElementById('modal_st_name').innerText = name;
            new bootstrap.Modal(document.getElementById('scoreModal')).show();
        }

        function openEditStudentModal(id, username, fullname, code, year, classroom) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_fullname').value = fullname;
            document.getElementById('edit_code').value = code;
            document.getElementById('edit_year_level').value = year; // ใส่ค่าระดับชั้น
            document.getElementById('edit_classroom').value = classroom;
            new bootstrap.Modal(document.getElementById('editStudentModal')).show();
        }

        function previewImage(event, targetId) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(targetId).src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }
        
        // ฟังก์ชันเปิด Modal เพิ่มลูก
        function openAddChildModal(parentId, parentName) {
            document.getElementById('append_parent_id').value = parentId;
            document.getElementById('append_parent_name').value = parentName;
            new bootstrap.Modal(document.getElementById('addChildModal')).show();
        }
    </script>
</body>
</html>