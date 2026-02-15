<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'parent') {
    header("Location: index.php");
    exit();
}

$parent_id = $_SESSION['user_id'];
$page = $_GET['page'] ?? 'home';

// ดึงข้อมูลผู้ปกครอง
$parent_info = $conn->query("SELECT * FROM users WHERE id = $parent_id")->fetch_assoc();

// ฟังก์ชันสร้าง avatar แบบสุ่ม
function getRandomAvatar($name, $student_id) {
    // สร้าง seed จาก student_id เพื่อให้ได้ avatar เดิมทุกครั้ง
    $seed = $student_id;
    
    // เลือกสีพื้นหลังแบบสุ่ม
    $colors = [
        ['bg' => '667eea', 'text' => 'ffffff'],
        ['bg' => 'f093fb', 'text' => 'ffffff'],
        ['bg' => '4facfe', 'text' => 'ffffff'],
        ['bg' => '43e97b', 'text' => 'ffffff'],
        ['bg' => 'fa709a', 'text' => 'ffffff'],
        ['bg' => 'feca57', 'text' => '2c3e50'],
        ['bg' => 'ff6348', 'text' => 'ffffff'],
        ['bg' => '1e3799', 'text' => 'ffffff'],
    ];
    
    $colorIndex = $seed % count($colors);
    $color = $colors[$colorIndex];
    
    // เอาตัวอักษรแรกของชื่อ
    $initial = mb_substr($name, 0, 1);
    
    return "https://ui-avatars.com/api/?name=" . urlencode($initial) . 
           "&background=" . $color['bg'] . 
           "&color=" . $color['text'] . 
           "&size=200&bold=true&rounded=true";
}

// ดึงรหัสนักเรียน "ทั้งหมด" ของผู้ปกครองคนนี้
$sql_parent = "SELECT child_student_code FROM parent_meta WHERE user_id = ?";
$stmt = $conn->prepare($sql_parent);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$res_codes = $stmt->get_result();

$my_children = [];
while ($row = $res_codes->fetch_assoc()) {
    $my_children[] = "'" . $conn->real_escape_string($row['child_student_code']) . "'";
}

$child_found = false;
$all_children_data = [];
$row_child = null;
$child_id = 0;
$child_code = '';

if (!empty($my_children)) {
    $codes_str = implode(',', $my_children);
    $sql_child = "SELECT u.id, u.fullname, u.username, u.profile_img, sm.classroom, sm.student_code, sm.year_level 
                  FROM users u JOIN student_meta sm ON u.id = sm.user_id 
                  WHERE sm.student_code IN ($codes_str)";

    $res_child = $conn->query($sql_child);

    if ($res_child && $res_child->num_rows > 0) {
        $child_found = true;
        while ($row = $res_child->fetch_assoc()) {
            $all_children_data[$row['id']] = $row;
        }

        if (isset($_GET['child_id']) && isset($all_children_data[$_GET['child_id']])) {
            $selected_id = $_GET['child_id'];
        } else {
            $selected_id = array_key_first($all_children_data);
        }

        $row_child = $all_children_data[$selected_id];
        $child_id = $row_child['id'];
        $child_code = $row_child['student_code'];
    }
}

// ดึงข้อมูลครู
if ($child_found) {
    $teachers = $conn->query("SELECT id, fullname FROM users WHERE role = 'teacher'");
}

// ดึงประกาศข่าวสารล่าสุด
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบผู้ปกครอง - Parent Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f0f2f5;
        }

        /* Header Styles */
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

        /* Navbar Styles */
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

        /* Menu Card Styles */
        .menu-card {
            border: none;
            border-radius: 20px;
            text-align: center;
            padding: 30px 20px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            cursor: pointer;
        }

        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .icon-box {
            font-size: 3rem;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            margin: 0 auto 20px;
            transition: all 0.3s ease;
        }

        .menu-card:hover .icon-box {
            transform: scale(1.1) rotate(5deg);
        }

        /* Theme Colors */
        .theme-blue .icon-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .theme-pink .icon-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .theme-orange .icon-box {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .theme-green .icon-box {
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
            color: white;
        }

        .theme-purple .icon-box {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #764ba2;
        }

        .theme-indigo .icon-box {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .theme-teal .icon-box {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .theme-red .icon-box {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .menu-card h5 {
            font-weight: 600;
            margin-top: 10px;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }

        .menu-card p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Child Selector */
        .child-selector {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .child-btn {
            padding: 15px 25px;
            border-radius: 15px;
            border: 2px solid #dee2e6;
            background: white;
            transition: all 0.3s;
            margin: 5px;
        }

        .child-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .child-btn.active {
            border-color: #FFB74D;
            background: linear-gradient(135deg, #FFE082 0%, #FFB74D 100%);
            color: white;
            font-weight: 600;
        }

        /* Student Card */
        .student-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
        }

        .student-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Behavior Log Styles */
        .behavior-log {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 5px solid #ddd;
            transition: all 0.3s;
        }

        .behavior-log:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .behavior-log.good {
            border-left-color: #66BB6A;
            background: linear-gradient(to right, #E8F5E9 0%, white 100%);
        }

        .behavior-log.bad {
            border-left-color: #EF5350;
            background: linear-gradient(to right, #FFEBEE 0%, white 100%);
        }

        .score-badge {
            font-size: 1.2rem;
            font-weight: 700;
            padding: 8px 15px;
            border-radius: 10px;
        }

        .score-badge.positive {
            background: #66BB6A;
            color: white;
        }

        .score-badge.negative {
            background: #EF5350;
            color: white;
        }

        /* Chat Styles */
        .chat-box {
            height: 450px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
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
            background: #FFB74D;
            color: white;
            border-bottom-right-radius: 0;
        }

        .message.other .msg-bubble {
            background: white;
            border: 1px solid #ddd;
            border-bottom-left-radius: 0;
        }

        .msg-time {
            font-size: 0.75rem;
            color: #999;
            margin-top: 5px;
        }

        /* Announcement Styles */
        .announcement-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #2196F3;
            transition: all 0.3s;
        }

        .announcement-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .announcement-card.info {
            border-left-color: #2196F3;
        }

        .announcement-card.warning {
            border-left-color: #FF9800;
        }

        .announcement-card.activity {
            border-left-color: #4CAF50;
        }

        .announcement-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .announcement-icon.info {
            background: #E3F2FD;
            color: #2196F3;
        }

        .announcement-icon.warning {
            background: #FFF3E0;
            color: #FF9800;
        }

        .announcement-icon.activity {
            background: #E8F5E9;
            color: #4CAF50;
        }

        /* Summary Cards */
        .summary-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .summary-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 15px 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .icon-box {
                width: 60px;
                height: 60px;
                line-height: 60px;
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="hero-header">
        <img src="https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&w=1600&q=80" 
             onerror="this.src='https://via.placeholder.com/1600x400/667eea/ffffff?text=Parent+Portal'" 
             alt="Parent Portal Banner" class="hero-img">
        <div class="hero-content">
            <h1 class="hero-title">PARENT PORTAL</h1>
            <p class="fs-5"><i class="bi bi-heart-fill"></i> ยินดีต้อนรับคุณ: <?php echo $parent_info['fullname']; ?></p>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand navbar-custom">
        <div class="container px-4">
            <a class="navbar-brand fw-bold text-primary" href="dashboard_parent.php">
                <i class="bi bi-house-heart-fill"></i> หน้าหลัก
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if ($child_found): ?>
            
            <!-- Child Selector (ถ้ามีลูกมากกว่า 1 คน) -->
            <?php if (count($all_children_data) > 1): ?>
                <div class="child-selector">
                    <h5 class="text-center mb-3"><i class="bi bi-people-fill"></i> เลือกบุตรหลาน</h5>
                    <div class="text-center">
                        <?php foreach ($all_children_data as $cid => $cdata): ?>
                            <a href="?page=<?php echo $page; ?>&child_id=<?php echo $cid; ?>" 
                               class="btn child-btn <?php echo ($cid == $child_id) ? 'active' : ''; ?>">
                                <i class="bi bi-person-circle"></i> <?php echo $cdata['fullname']; ?>
                                (ป.<?php echo $cdata['year_level']; ?>/<?php echo $cdata['classroom']; ?>)
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Student Info Card -->
            <div class="student-card">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <?php
                        // ใช้รูปโปรไฟล์ หรือสร้าง avatar สุ่ม
                        $avatar_url = (!empty($row_child['profile_img']) && $row_child['profile_img'] != 'default.png') 
                            ? $row_child['profile_img'] 
                            : getRandomAvatar($row_child['fullname'], $row_child['id']);
                        ?>
                        <img src="<?php echo $avatar_url; ?>" 
                             alt="Student" class="student-avatar"
                             onerror="this.src='<?php echo getRandomAvatar($row_child['fullname'], $row_child['id']); ?>'">
                    </div>
                    <div class="col-md-7">
                        <h3 class="mb-1"><?php echo $row_child['fullname']; ?></h3>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-card-text"></i> รหัส: <?php echo $row_child['student_code']; ?> | 
                            <i class="bi bi-book"></i> ชั้น ม.<?php echo $row_child['year_level']; ?>/<?php echo $row_child['classroom']; ?>
                        </p>
                    </div>
                    <div class="col-md-3 text-end">
                        <?php
                        $score_result = $conn->query("SELECT SUM(score) as total FROM behavior_logs WHERE student_id = $child_id");
                        $total_score = $score_result->fetch_assoc()['total'] ?? 0;
                        ?>
                        <h1 class="mb-0 fw-bold"><?php echo $total_score; ?></h1>
                        <p class="mb-0 opacity-75">คะแนนรวม</p>
                    </div>
                </div>
            </div>

            <?php
            // --- PAGE: HOME (เมนูหลัก) ---
            if ($page == 'home'):
            ?>

                <div class="row g-4">
                    <!-- Menu: ประวัติพฤติกรรม -->
                    <div class="col-md-3 col-sm-6">
                        <a href="?page=behavior&child_id=<?php echo $child_id; ?>" class="menu-card theme-blue">
                            <div class="icon-box">
                                <i class="bi bi-clipboard-data"></i>
                            </div>
                            <h5>ประวัติพฤติกรรม</h5>
                            <p>ดูบันทึกพฤติกรรมของลูก</p>
                        </a>
                    </div>

                    <!-- Menu: ประกาศข่าวสาร -->
                    <div class="col-md-3 col-sm-6">
                        <a href="?page=announcements&child_id=<?php echo $child_id; ?>" class="menu-card theme-orange">
                            <div class="icon-box">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>
                            <h5>ประกาศข่าวสาร</h5>
                            <p>ข่าวสารจากโรงเรียน</p>
                        </a>
                    </div>

                    <!-- Menu: แชทกับครู -->
                    <div class="col-md-3 col-sm-6">
                        <a href="?page=chat&child_id=<?php echo $child_id; ?>" class="menu-card theme-green">
                            <div class="icon-box">
                                <i class="bi bi-chat-dots-fill"></i>
                            </div>
                            <h5>แชทกับครู</h5>
                            <p>สนทนากับครู</p>
                        </a>
                    </div>

                    <!-- Menu: ร้านค้า/ของรางวัล -->
                    <div class="col-md-3 col-sm-6">
                        <a href="?page=rewards&child_id=<?php echo $child_id; ?>" class="menu-card theme-pink">
                            <div class="icon-box">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <h5>ของรางวัล</h5>
                            <p>ดูของรางวัลที่แลกได้</p>
                        </a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <?php
                $good_count = $conn->query("SELECT COUNT(*) as cnt FROM behavior_logs WHERE student_id = $child_id AND behavior_type = 'good'")->fetch_assoc()['cnt'];
                $bad_count = $conn->query("SELECT COUNT(*) as cnt FROM behavior_logs WHERE student_id = $child_id AND behavior_type = 'bad'")->fetch_assoc()['cnt'];
                $latest_logs = $conn->query("SELECT * FROM behavior_logs WHERE student_id = $child_id ORDER BY log_date DESC LIMIT 5");
                ?>

                <div class="row g-4 mt-4">
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="icon-box mx-auto" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; width: 60px; height: 60px; line-height: 60px; font-size: 2rem;">
                                <i class="bi bi-emoji-smile"></i>
                            </div>
                            <h5>พฤติกรรมดี</h5>
                            <div class="summary-number text-success"><?php echo $good_count; ?></div>
                            <small class="text-muted">ครั้ง</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="icon-box mx-auto" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; width: 60px; height: 60px; line-height: 60px; font-size: 2rem;">
                                <i class="bi bi-emoji-frown"></i>
                            </div>
                            <h5>พฤติกรรมไม่เหมาะสม</h5>
                            <div class="summary-number text-danger"><?php echo $bad_count; ?></div>
                            <small class="text-muted">ครั้ง</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="icon-box mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; width: 60px; height: 60px; line-height: 60px; font-size: 2rem;">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <h5>คะแนนสะสม</h5>
                            <div class="summary-number text-primary"><?php echo $total_score; ?></div>
                            <small class="text-muted">คะแนน</small>
                        </div>
                    </div>
                </div>

                <!-- Latest Activities -->
                <div class="card border-0 rounded-4 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 p-4">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> กิจกรรมล่าสุด</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($latest_logs->num_rows > 0): ?>
                            <?php while ($log = $latest_logs->fetch_assoc()): ?>
                                <div class="behavior-log <?php echo $log['behavior_type']; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php if ($log['behavior_type'] == 'good'): ?>
                                                    <i class="bi bi-emoji-smile text-success"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-emoji-frown text-danger"></i>
                                                <?php endif; ?>
                                                <?php echo $log['title']; ?>
                                            </h6>
                                            <p class="mb-1 text-muted small"><?php echo $log['detail'] ?: '-'; ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i> 
                                                <?php echo date('d/m/Y H:i', strtotime($log['log_date'])); ?>
                                            </small>
                                        </div>
                                        <span class="score-badge <?php echo $log['score'] >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo $log['score'] > 0 ? '+' : ''; ?><?php echo $log['score']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3">ยังไม่มีประวัติพฤติกรรม</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php
            // --- PAGE: BEHAVIOR (ประวัติพฤติกรรมทั้งหมด) ---
            elseif ($page == 'behavior'):
                $all_logs = $conn->query("SELECT * FROM behavior_logs WHERE student_id = $child_id ORDER BY log_date DESC");
            ?>

                <div class="mb-3">
                    <a href="?page=home&child_id=<?php echo $child_id; ?>" class="btn btn-outline-primary rounded-pill">
                        <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
                    </a>
                </div>

                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-header bg-white border-0 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> ประวัติพฤติกรรมทั้งหมด</h5>
                            <span class="badge bg-primary rounded-pill"><?php echo $all_logs->num_rows; ?> รายการ</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($all_logs->num_rows > 0): ?>
                            <?php while ($log = $all_logs->fetch_assoc()): 
                                $teacher = $conn->query("SELECT fullname FROM users WHERE id = {$log['teacher_id']}")->fetch_assoc();
                            ?>
                                <div class="behavior-log <?php echo $log['behavior_type']; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php if ($log['behavior_type'] == 'good'): ?>
                                                    <i class="bi bi-emoji-smile text-success"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-emoji-frown text-danger"></i>
                                                <?php endif; ?>
                                                <?php echo $log['title']; ?>
                                            </h6>
                                            <p class="mb-1 text-muted small"><?php echo $log['detail'] ?: 'ไม่มีรายละเอียด'; ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i> 
                                                <?php echo date('d/m/Y H:i น.', strtotime($log['log_date'])); ?>
                                                <?php if ($teacher): ?>
                                                    | <i class="bi bi-person"></i> บันทึกโดย: <?php echo $teacher['fullname']; ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="score-badge <?php echo $log['score'] >= 0 ? 'positive' : 'negative'; ?>">
                                                <?php echo $log['score'] > 0 ? '+' : ''; ?><?php echo $log['score']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3">ยังไม่มีประวัติพฤติกรรม</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php
            // --- PAGE: ANNOUNCEMENTS (ประกาศข่าวสาร) ---
            elseif ($page == 'announcements'):
            ?>

                <div class="mb-3">
                    <a href="?page=home&child_id=<?php echo $child_id; ?>" class="btn btn-outline-primary rounded-pill">
                        <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
                    </a>
                </div>

                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-header bg-white border-0 p-4">
                        <h5 class="mb-0"><i class="bi bi-megaphone-fill"></i> ประกาศข่าวสารจากโรงเรียน</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($announcements->num_rows > 0): ?>
                            <?php 
                            mysqli_data_seek($announcements, 0); // Reset pointer
                            while ($ann = $announcements->fetch_assoc()): 
                            ?>
                                <div class="announcement-card <?php echo $ann['type']; ?>">
                                    <div class="d-flex">
                                        <div class="announcement-icon <?php echo $ann['type']; ?> me-3">
                                            <?php
                                            $icons = [
                                                'info' => 'bi-info-circle-fill',
                                                'warning' => 'bi-exclamation-triangle-fill',
                                                'activity' => 'bi-calendar-event-fill'
                                            ];
                                            ?>
                                            <i class="bi <?php echo $icons[$ann['type']]; ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0"><?php echo $ann['title']; ?></h6>
                                                <span class="badge bg-secondary rounded-pill">
                                                    <?php
                                                    $type_text = [
                                                        'info' => 'ข้อมูลทั่วไป',
                                                        'warning' => 'สำคัญ',
                                                        'activity' => 'กิจกรรม'
                                                    ];
                                                    echo $type_text[$ann['type']];
                                                    ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($ann['content'])): ?>
                                                <p class="mb-2"><?php echo nl2br($ann['content']); ?></p>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> 
                                                <?php echo date('d/m/Y H:i น.', strtotime($ann['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-megaphone" style="font-size: 3rem;"></i>
                                <p class="mt-3">ยังไม่มีประกาศข่าวสาร</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php
            // --- PAGE: CHAT (แชทกับครู) ---
            elseif ($page == 'chat'):
                $selected_tid = $_GET['tid'] ?? null;
                
                if (!$selected_tid && $teachers->num_rows > 0) {
                    $selected_tid = $teachers->fetch_assoc()['id'];
                    mysqli_data_seek($teachers, 0);
                }
            ?>

                <div class="mb-3">
                    <a href="?page=home&child_id=<?php echo $child_id; ?>" class="btn btn-outline-primary rounded-pill">
                        <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
                    </a>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-header bg-white border-0 p-3">
                                <h6 class="mb-0"><i class="bi bi-people"></i> รายชื่อครู</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php while ($t = $teachers->fetch_assoc()): ?>
                                    <a href="?page=chat&tid=<?php echo $t['id']; ?>&child_id=<?php echo $child_id; ?>" 
                                       class="list-group-item list-group-item-action <?php echo ($selected_tid == $t['id']) ? 'active' : ''; ?>">
                                        <i class="bi bi-person-circle"></i> <?php echo $t['fullname']; ?>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <?php if ($selected_tid): 
                                $teacher_info = $conn->query("SELECT fullname FROM users WHERE id = $selected_tid")->fetch_assoc();
                            ?>
                                <div class="card-header bg-white border-0 p-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-chat-dots-fill"></i> 
                                        แชทกับ: <?php echo $teacher_info['fullname']; ?>
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="chat-box" id="chatContainer">
                                        <?php
                                        $messages = $conn->query("SELECT m.*, u.fullname as sender_name 
                                            FROM messages m 
                                            JOIN users u ON m.sender_id = u.id 
                                            WHERE (m.sender_id = $parent_id AND m.receiver_id = $selected_tid) 
                                               OR (m.sender_id = $selected_tid AND m.receiver_id = $parent_id)
                                            ORDER BY m.created_at ASC");
                                        
                                        if ($messages->num_rows > 0):
                                            while ($msg = $messages->fetch_assoc()):
                                                $is_me = ($msg['sender_id'] == $parent_id);
                                        ?>
                                            <div class="message <?php echo $is_me ? 'me' : 'other'; ?>">
                                                <div class="msg-bubble">
                                                    <?php if (!$is_me): ?>
                                                        <strong class="d-block mb-1"><?php echo $msg['sender_name']; ?></strong>
                                                    <?php endif; ?>
                                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                                    <div class="msg-time">
                                                        <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                            <p class="text-center text-muted mt-5">เริ่มการสนทนาได้เลย...</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 p-3">
                                    <form action="parent_action.php?action=send_message&child_id=<?php echo $child_id; ?>" method="POST">
                                        <input type="hidden" name="teacher_id" value="<?php echo $selected_tid; ?>">
                                        <div class="input-group">
                                            <input type="text" name="message" class="form-control rounded-pill" 
                                                   placeholder="พิมพ์ข้อความ..." required autocomplete="off">
                                            <button class="btn btn-primary rounded-pill ms-2 px-4">
                                                <i class="bi bi-send-fill"></i> ส่ง
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php
            // --- PAGE: REWARDS (ของรางวัล) ---
            elseif ($page == 'rewards'):
                $rewards = $conn->query("SELECT * FROM rewards WHERE stock > 0 ORDER BY point_cost ASC");
                $redemption_history = $conn->query("SELECT r.*, rw.name as reward_name, rw.point_cost 
                    FROM redemption_logs r 
                    JOIN rewards rw ON r.reward_id = rw.id 
                    WHERE r.student_id = $child_id 
                    ORDER BY r.redeem_date DESC LIMIT 10");
            ?>

                <div class="mb-3">
                    <a href="?page=home&child_id=<?php echo $child_id; ?>" class="btn btn-outline-primary rounded-pill">
                        <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
                    </a>
                </div>

                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-header bg-white border-0 p-4">
                                <h5 class="mb-0"><i class="bi bi-gift-fill"></i> ของรางวัลที่สามารถแลกได้</h5>
                                <small class="text-muted">คะแนนปัจจุบัน: <strong><?php echo $total_score; ?></strong> คะแนน</small>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($rewards->num_rows > 0): ?>
                                    <div class="row g-3">
                                        <?php while ($reward = $rewards->fetch_assoc()): ?>
                                            <div class="col-md-6">
                                                <div class="card h-100">
                                                    <div class="card-body text-center">
                                                        <div class="fs-1 mb-3">🎁</div>
                                                        <h6><?php echo $reward['name']; ?></h6>
                                                        <p class="text-primary fw-bold"><?php echo $reward['point_cost']; ?> คะแนน</p>
                                                        <small class="text-muted">คงเหลือ: <?php echo $reward['stock']; ?> ชิ้น</small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-gift" style="font-size: 3rem;"></i>
                                        <p class="mt-3">ยังไม่มีของรางวัล</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-header bg-white border-0 p-3">
                                <h6 class="mb-0"><i class="bi bi-clock-history"></i> ประวัติการแลก</h6>
                            </div>
                            <div class="card-body p-3">
                                <?php if ($redemption_history->num_rows > 0): ?>
                                    <?php while ($history = $redemption_history->fetch_assoc()): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <small class="d-block text-muted">
                                                <?php echo date('d/m/Y', strtotime($history['redeem_date'])); ?>
                                            </small>
                                            <strong><?php echo $history['reward_name']; ?></strong>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-primary">-<?php echo $history['point_cost']; ?> คะแนน</small>
                                                <span class="badge bg-<?php 
                                                    echo $history['status'] == 'approved' ? 'success' : 
                                                         ($history['status'] == 'rejected' ? 'danger' : 'warning'); 
                                                ?>">
                                                    <?php 
                                                    $status_text = [
                                                        'pending' => 'รอดำเนินการ',
                                                        'approved' => 'อนุมัติแล้ว',
                                                        'rejected' => 'ปฏิเสธ'
                                                    ];
                                                    echo $status_text[$history['status']];
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted small">ยังไม่มีประวัติ</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-exclamation-circle text-warning" style="font-size: 5rem;"></i>
                <h3 class="mt-4">ไม่พบข้อมูลบุตรหลาน</h3>
                <p class="text-muted">กรุณาติดต่อเจ้าหน้าที่โรงเรียนเพื่อเชื่อมโยงข้อมูล</p>
                <a href="logout.php" class="btn btn-warning rounded-pill px-4 mt-3">
                    <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                </a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var chatBox = document.getElementById('chatContainer');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>

</body>
</html>