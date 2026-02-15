<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Student Hero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* 1. Rainbow Pastel Background (Inspired by your image) */
        body {
            background: radial-gradient(circle at top left, #e0f2fe, transparent),
                        radial-gradient(circle at bottom right, #fce7f3, transparent),
                        radial-gradient(circle at bottom left, #fef3c7, transparent),
                        radial-gradient(circle at top right, #d1fae5, transparent);
            background-color: #ffffff;
            font-family: 'Prompt', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Desktop: ชิดขวา */
            padding-right: 8%; /* Desktop: เว้นระยะขวา */
            overflow: hidden;
        }

        /* 2. Glassmorphism Card Style */
        .login-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            animation: fadeInRight 1s ease-out;
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* 3. Input Styling */
        .form-control {
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.05);
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
        }

        .btn-custom {
            background: #64b5f6; /* สีฟ้าพาสเทลตามภาพ */
            border: none;
            border-radius: 20px;
            padding: 15px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-custom:hover {
            background: #42a5f5;
            transform: scale(1.02);
            color: white;
        }

        /* 4. Help Button (Floating) */
        .help-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: white;
            color: #64b5f6;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1000;
            text-decoration: none;
        }

        .help-btn:hover {
            transform: rotate(15deg) scale(1.1);
            background: #64b5f6;
            color: white;
        }

        /* 5. Modal Styling (หน้าช่วยเหลือ) */
        .modal-content {
            border-radius: 30px;
            border: none;
            text-align: center;
            padding: 30px;
        }

        .social-icon {
            font-size: 40px;
            margin: 20px;
            transition: transform 0.3s;
            display: inline-block;
        }
        
        .social-icon:hover { transform: translateY(-10px); }
        .icon-line { color: #06C755; }
        .icon-fb { color: #1877F2; }
        .icon-tel { color: #34A853; }

        /* สไตล์ปุ่มคู่มือ */
        .manual-btn {
            background: white;
            color: #64b5f6;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .manual-btn:hover {
            background: #64b5f6;
            color: white;
            transform: translateY(-2px);
        }

        /* ========================================= */
        /* 🔥 เพิ่มส่วนนี้: ปรับแต่งสำหรับมือถือ 🔥   */
        /* ========================================= */
        @media (max-width: 991px) {
            body {
                justify-content: center; /* เปลี่ยนเป็นจัดกึ่งกลาง */
                padding-right: 0;        /* ยกเลิกการเว้นขวา */
                padding-left: 15px;      /* เพิ่มระยะห่างซ้ายนิดหน่อยกันติดจอ */
                padding-right: 15px;     /* เพิ่มระยะห่างขวานิดหน่อยกันติดจอ */
            }
            
            .login-card {
                padding: 40px 25px;      /* ลด Padding ในการ์ดนิดนึงให้ไม่แน่นจอเกินไป */
            }
        }

    </style>
</head>
<body>

    <a href="manual.php" class="btn manual-btn position-absolute top-0 start-0 m-4 rounded-pill px-4 py-2 text-decoration-none" style="z-index: 1000;">
        <i class="fa-solid fa-book-open me-2"></i> คู่มือการใช้งาน
    </a>

    <div class="login-card">
        <div class="text-center mb-5">
            <img src="https://cdn-icons-png.flaticon.com/512/3222/3222800.png" width="80" alt="Logo" class="mb-3">
            <h2 style="font-weight: 600; color: #333;">School App!</h2>
            <p class="text-muted">กรุณาเข้าสู่ระบบเพื่อใช้งาน School App!</p>
        </div>
        
        <form action="check_login.php" method="POST">
            <input type="text" class="form-control" name="username" placeholder="Username" required>
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            
            <button type="submit" class="btn btn-custom mt-3">Login</button>

            <div class="text-center mt-4">
                <a href="register.php" class="text-decoration-none text-muted small">ยังไม่มีบัญชี? <span style="color:#64b5f6">สร้างบัญชีใหม่</span></a>
            </div>
        </form>
    </div>

    <a class="help-btn" data-bs-toggle="modal" data-bs-target="#helpModal">
        <i class="fa-solid fa-headset"></i>
    </a>

    <div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h3 class="mb-4">ต้องการความช่วยเหลือ?</h3>
                    <p class="text-muted">คุณสามารถติดต่อผู้ดูแลระบบได้ผ่านช่องทางด้านล่างนี้</p>
                    
                    <a href="https://line.me/ti/p/YOUR_LINE_ID" target="_blank" class="social-icon icon-line">
                        <i class="fa-brands fa-line"></i>
                    </a>
                    <a href="https://www.facebook.com/farm.aphinan?locale=th_TH" target="_blank" class="social-icon icon-fb">
                        <i class="fa-brands fa-facebook"></i>
                    </a>
                    <a href="tel:0611381071" class="social-icon icon-tel">
                        <i class="fa-solid fa-phone-volume"></i>
                    </a>
                    
                    <div class="mt-3">
                        <small class="text-muted">เวลาทำการ: 07:00 - 16:30 น.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>