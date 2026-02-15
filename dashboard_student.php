<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$page = $_GET['page'] ?? 'home';

// --- 1. คำนวณแต้ม ---
// คะแนนรวมจากพฤติกรรม
$q1 = $conn->query("SELECT COALESCE(SUM(score), 0) as total FROM behavior_logs WHERE student_id = $student_id");
$behavior_points = $q1->fetch_assoc()['total'];
// คะแนนที่ใช้แลกของไปแล้ว
$q2 = $conn->query("SELECT COALESCE(SUM(r.point_cost), 0) as spent FROM redemption_logs l JOIN rewards r ON l.reward_id = r.id WHERE l.student_id = $student_id AND l.status != 'rejected'");
$spent_points = $q2->fetch_assoc()['spent'];

$my_balance = $behavior_points - $spent_points;

// --- 2. ดึงข้อมูลอื่นๆ ---
// ประวัติล่าสุด (พฤติกรรม)
$hist = $conn->query("SELECT * FROM behavior_logs WHERE student_id = $student_id ORDER BY log_date DESC LIMIT 10");
// ประวัติการแลกรางวัล
$redeem_hist = $conn->query("SELECT l.*, r.name as reward_name, r.point_cost FROM redemption_logs l JOIN rewards r ON l.reward_id = r.id WHERE l.student_id = $student_id ORDER BY l.redeem_date DESC LIMIT 10");
// ประกาศข่าว
$news = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
// Top 5 Leaderboard
$rank_sql = "SELECT u.fullname, COALESCE(SUM(b.score), 0) as total 
             FROM users u JOIN behavior_logs b ON u.id = b.student_id 
             WHERE u.role='student' GROUP BY u.id ORDER BY total DESC LIMIT 5";
$ranks = $conn->query($rank_sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - School System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f0f2f5;
        }

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

        .score-card {
            background: linear-gradient(135deg, #1976D2, #64B5F6);
            color: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(25, 118, 210, 0.2);
        }

        .item-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 50px;
        }
    </style>
</head>

<body>

    <div class="hero-header">
        <img src="images/banner.gif" onerror="this.src='https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop'" alt="School Banner" class="hero-img">
        <div class="hero-content">
            <h1 class="hero-title">STUDENT PORTAL</h1>
            <p class="fs-5"><i class="bi bi-person-circle"></i> ยินดีต้อนรับ: <?php echo $_SESSION['fullname']; ?></p>
        </div>
    </div>

    <nav class="navbar navbar-expand navbar-custom">
        <div class="container px-4">
            <a class="navbar-brand fw-bold text-primary" href="dashboard_student.php">
                <i class="bi bi-house-door-fill"></i> HOME
            </a>
            <div class="ms-auto d-flex align-items-center">
                <!-- ปุ่มโปรไฟล์ -->
                <a href="profile_student.php" class="btn btn-light btn-sm rounded-pill px-3 me-2" style="border: 2px solid #667eea;">
                    <?php
                    // ดึงรูปโปรไฟล์จากฐานข้อมูล
                    $profile_query = "SELECT profile_img FROM users WHERE id = $student_id";
                    $profile_result = $conn->query($profile_query);
                    $profile_data = $profile_result->fetch_assoc();
                    $profile_image = !empty($profile_data['profile_img']) ? $profile_data['profile_img'] : 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($_SESSION['fullname']);
                    ?>
                    <img src="<?php echo $profile_image; ?>" 
                         alt="Profile" 
                         style="width: 25px; height: 25px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                    <span style="color: #667eea; font-weight: 600;">โปรไฟล์</span>
                </a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-4">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <?php if ($page == 'home'): ?>
            <div class="row g-4 mb-4">
                <!-- คะแนน -->
                <div class="col-md-4">
                    <div class="score-card h-100 d-flex flex-column justify-content-center">
                        <h6 class="text-uppercase opacity-75 fw-bold mb-3">คะแนนคงเหลือของคุณ</h6>
                        <h1 class="display-3 fw-bold mb-0"><?php echo number_format($my_balance); ?></h1>
                        <p class="mt-2 mb-0">จากคะแนนสะสมทั้งหมด <?php echo number_format($behavior_points); ?></p>
                    </div>
                </div>

                <!-- เมนูหลัก -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 20px;">
                        <h5 class="fw-bold mb-4 text-secondary">เมนูสำหรับนักเรียน</h5>
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <a href="?page=shop" class="menu-card theme-purple">
                                    <div class="icon-box"><i class="bi bi-shop"></i></div>
                                    <h6>ร้านค้ารางวัล</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="?page=history" class="menu-card theme-blue">
                                    <div class="icon-box"><i class="bi bi-clock-history"></i></div>
                                    <h6>ประวัติของฉัน</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="?page=rank" class="menu-card theme-orange">
                                    <div class="icon-box"><i class="bi bi-trophy-fill"></i></div>
                                    <h6>อันดับเด็กดี</h6>
                                </a>
                            </div>
                            <div class="col-6 col-md-4">
                                <a href="?page=news" class="menu-card theme-teal">
                                    <div class="icon-box"><i class="bi bi-megaphone-fill"></i></div>
                                    <h6>ข่าวสาร</h6>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- ข่าวสารล่าสุด -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-megaphone text-primary me-2"></i>ข่าวสารล่าสุด</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php while ($n = $news->fetch_assoc()): ?>
                                    <div class="list-group-item p-3 border-0 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold text-primary mb-1"><?php echo $n['title']; ?></h6>
                                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($n['created_at'])); ?></small>
                                        </div>
                                        <p class="small text-secondary mb-0"><?php echo mb_strimwidth($n['content'], 0, 100, "..."); ?></p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5 อันดับเด็กดี -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; overflow: hidden;">
                        <div class="card-header bg-dark text-white fw-bold text-center py-3">
                            <i class="bi bi-trophy-fill text-warning"></i> 5 อันดับเด็กดี
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php
                            $rank = 1;
                            while ($r = $ranks->fetch_assoc()):
                                $is_me = ($r['fullname'] == $_SESSION['fullname']);
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 <?php echo $is_me ? 'bg-light' : ''; ?>">
                                    <div>
                                        <span class="badge rounded-pill bg-<?php echo $rank == 1 ? 'danger' : ($rank == 2 ? 'warning' : 'secondary'); ?> me-2">
                                            <?php echo $rank++; ?>
                                        </span>
                                        <span class="fw-bold <?php echo $is_me ? 'text-primary' : ''; ?>"><?php echo $r['fullname']; ?></span>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo number_format($r['total']); ?> แต้ม</span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>

        <?php elseif ($page == 'shop'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">ร้านค้ารางวัล</h4>
                <div class="badge bg-primary p-2 px-3 rounded-pill">แต้มของคุณ: <?php echo number_format($my_balance); ?></div>
            </div>
            <div class="row g-4">
                <?php
                $rewards = $conn->query("SELECT * FROM rewards WHERE stock > 0");
                if ($rewards->num_rows > 0):
                    while ($item = $rewards->fetch_assoc()):
                ?>
                        <div class="col-6 col-md-3">
                            <div class="card item-card h-100">
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px;">
                                    <i class="bi bi-gift text-secondary" style="font-size: 4rem;"></i>
                                </div>
                                <div class="card-body text-center">
                                    <h6 class="fw-bold mb-2"><?php echo $item['name']; ?></h6>
                                    <p class="text-primary fw-bold mb-3"><?php echo number_format($item['point_cost']); ?> แต้ม</p>
                                    <button class="btn btn-primary w-100 rounded-pill btn-sm"
                                        onclick="confirmRedeem(<?php echo $item['id']; ?>, '<?php echo $item['name']; ?>', <?php echo $item['point_cost']; ?>)">
                                        แลกรางวัล
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">ยังไม่มีของรางวัลในขณะนี้</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($page == 'history'): ?>
            <h4 class="fw-bold mb-4">ประวัติของฉัน</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-3 text-success"><i class="bi bi-star-fill me-2"></i>ประวัติคะแนนความดี/พฤติกรรม</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>วันที่</th>
                                        <th>รายการ</th>
                                        <th class="text-end">คะแนน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($h = $hist->fetch_assoc()): ?>
                                        <tr>
                                            <td><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($h['log_date'])); ?></small></td>
                                            <td><?php echo $h['title']; ?></td>
                                            <td class="text-end fw-bold <?php echo $h['score'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo ($h['score'] > 0 ? '+' : '') . $h['score']; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-gift-fill me-2"></i>ประวัติการแลกของรางวัล</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>วันที่</th>
                                        <th>ของรางวัล</th>
                                        <th class="text-center">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($rh = $redeem_hist->fetch_assoc()): ?>
                                        <tr>
                                            <td><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($rh['redeem_date'])); ?></small></td>
                                            <td><?php echo $rh['reward_name']; ?></td>
                                            <td class="text-center">
                                                <?php
                                                $status_class = [
                                                    'pending' => 'bg-warning text-dark',
                                                    'approved' => 'bg-success text-white',
                                                    'rejected' => 'bg-danger text-white'
                                                ];
                                                $status_text = [
                                                    'pending' => 'รออนุมัติ',
                                                    'approved' => 'สำเร็จ',
                                                    'rejected' => 'ถูกปฏิเสธ'
                                                ];
                                                ?>
                                                <span class="status-badge <?php echo $status_class[$rh['status']]; ?>">
                                                    <?php echo $status_text[$rh['status']]; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($page == 'rank'): ?>
            <h4 class="fw-bold mb-4 text-center">อันดับเด็กดี</h4>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                        <div class="card-header bg-dark text-white text-center py-4">
                            <h2 class="mb-0"><i class="bi bi-trophy-fill text-warning"></i> TOP 5 PLAYERS</h2>
                        </div>
                        <div class="card-body p-0">
                            <?php
                            $ranks->data_seek(0);
                            $i = 1;
                            while ($r = $ranks->fetch_assoc()):
                                $is_me = ($r['fullname'] == $_SESSION['fullname']);
                            ?>
                                <div class="d-flex align-items-center p-4 border-bottom <?php echo $is_me ? 'bg-primary bg-opacity-10' : ''; ?>">
                                    <div class="fs-2 fw-bold me-4 text-<?php echo $i == 1 ? 'danger' : ($i == 2 ? 'warning' : 'secondary'); ?>" style="width: 40px;">#<?php echo $i++; ?></div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-0 <?php echo $is_me ? 'text-primary' : ''; ?>"><?php echo $r['fullname']; ?></h5>
                                        <small class="text-muted">นักเรียนตัวอย่าง</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="fw-bold mb-0 text-primary"><?php echo number_format($r['total']); ?></h4>
                                        <small class="text-muted">แต้มสะสม</small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($page == 'news'): ?>
            <h4 class="fw-bold mb-4">ข่าวสารและประกาศ</h4>
            <div class="row">
                <?php
                $news->data_seek(0);
                while ($n = $news->fetch_assoc()):
                ?>
                    <div class="col-md-12 mb-3">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold text-primary mb-2"><i class="bi bi-megaphone me-2"></i><?php echo $n['title']; ?></h5>
                                    <p class="mb-3 text-secondary"><?php echo $n['content']; ?></p>
                                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i> ประกาศเมื่อ: <?php echo date('d/m/Y H:i', strtotime($n['created_at'])); ?></small>
                                </div>
                                <?php
                                $types = ['info' => 'primary', 'warning' => 'warning', 'activity' => 'success'];
                                ?>
                                <span class="badge bg-<?php echo $types[$n['type']]; ?> rounded-pill px-3"><?php echo $n['type']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmRedeem(id, name, cost) {
            Swal.fire({
                title: 'ยืนยันการแลกรางวัล?',
                text: "คุณต้องการแลก '" + name + "' โดยใช้ " + cost + " แต้ม ใช่หรือไม่?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ตกลง, แลกเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'student_action.php?action=redeem';

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'reward_id';
                    idInput.value = id;

                    form.appendChild(idInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            })
        }

        // เช็คการแจ้งเตือนจาก URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('msg')) {
            const msg = urlParams.get('msg');
            const itemName = urlParams.get('item_name');

            if (msg === 'redeem_success') {
                Swal.fire({
                    title: 'แลกรางวัลสำเร็จ!',
                    text: 'คุณได้แลก ' + itemName + ' เรียบร้อยแล้ว กรุณารอครูอนุมัติ',
                    icon: 'success',
                    confirmButtonText: 'รับทราบ'
                });
            } else if (msg === 'not_enough_points') {
                Swal.fire({
                    title: 'แต้มไม่พอ!',
                    text: 'คุณมีแต้มไม่เพียงพอสำหรับการแลกรางวัลนี้',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            } else if (msg === 'out_of_stock') {
                Swal.fire({
                    title: 'ของหมด!',
                    text: 'ขออภัย รางวัลนี้หมดสต็อกแล้ว',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                });
            }
        }
    </script>
</body>