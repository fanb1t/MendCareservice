<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mendcare Service - ระบบช่างซ่อมมืออาชีพ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="ind.css">


</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div id="main-content">
        <header>
            <div class="header-container">
                <div class="nav-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </div>
                <a href="/" class="logo">
                    <i class="fas fa-wrench"></i>
                    Mendcare Service
                </a>
                    <!-- In the nav-menu ul, replace the profile icon with a dropdown or separate login/register links -->
<nav>
    <ul class="nav-menu">
        <li>
            <input type="search" class="search-box" placeholder="ค้นหาบริการ...">
        </li>
        <li><a href="/notifications"><i class="fas fa-bell"></i></a></li>
        <li><a href="/cart"><i class="fas fa-shopping-cart"></i></a></li>
        <li class="auth-links">
            <a href="login.html" class="login-btn"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
            <!-- <a href="register" class="register-btn"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a> -->
        </li>
    </ul>
</nav>

            </div>
        </header>

        <main>
            <section class="hero">
                <h1>บริการซ่อมบำรุงมืออาชีพ</h1>
                <p>มั่นใจกับทีมช่างมืออาชีพที่ผ่านการรับรอง พร้อมรับประกันผลงาน</p>
                <button class="cta-button">จองบริการเลย <i class="fas fa-arrow-right"></i></button>
            </section>

            <section class="services">
                <h2 class="section-title">บริการของเรา</h2>
                <div class="services-grid" >
                    <div class="service-card" onclick="window.location.href='airservice.php';">
                        <i class="fas fa-wind service-icon"></i>
                        <h3>ระบบปรับอากาศ</h3>
                        <p>บริการซ่อม ล้าง และติดตั้งแอร์โดยช่างผู้เชี่ยวชาญ</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-home service-icon"></i>
                        <h3>รีโนเวทและต่อเติม</h3>
                        <p>แก้ไขปัญหาหลังคารั่ว ซ่อมแซมและเปลี่ยนหลังคา</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-faucet service-icon"></i>
                        <h3>ห้องน้ำ/ประปา</h3>
                        <p>แก้ไขปัญหาท่อรั่ว ซ่อมก๊อกน้ำ และระบบประปา</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-bolt service-icon"></i>
                        <h3>งานไฟฟ้า/เครื่องใช้ไฟฟ้า</h3>
                        <p>ซ่อมแซมและติดตั้งระบบไฟฟ้าภายในบ้าน</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-broom service-icon"></i>
                        <h3>ทำความสะอาด</h3>
                        <p>บริการทำความสะอาดบ้าน สำนักงาน และพื้นที่ต่างๆ</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-tshirt service-icon"></i>
                        <h3>ซ่อมเครื่องซักผ้า</h3>
                        <p>ซ่อมแซมและบำรุงรักษาเครื่องซักผ้าทุกประเภท</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-tree service-icon"></i>
                        <h3>งานภายนอกบ้าน</h3>
                        <p>บริการดูแลสวน ตัดแต่งต้นไม้ และปรับปรุงภูมิทัศน์</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-tools service-icon"></i>
                        <h3>บริการอื่นๆ</h3>
                        <p>ซ่อมแซมทั่วไปและบริการอื่นๆ ตามความต้องการ</p>
                    </div>
                </div>
            </section>
            

            <!-- Add this after the services section -->

<section class="popular-services">
    <h2 class="section-title">บริการยอดนิยม</h2>
    <div class="popular-services-grid">
        <div class="popular-service-card">
            <img src="image/air.jpg" alt="ล้างแอร์">
            <h3>ล้างแอร์</h3>
            <p>เริ่มต้น 500 บาท</p>
            <span class="image/popular-badge">ยอดนิยม</span>
        </div>
        <div class="popular-service-card">
            <img src="image/water.jpg" alt="ซ่อมท่อน้ำรั่ว">
            <h3>ซ่อมท่อน้ำรั่ว</h3>
            <p>เริ่มต้น 350 บาท</p>
            <span class="popular-badge">ลดราคา</span>
        </div>
        <div class="popular-service-card">
            <img src="image/elc.jpg" alt="ติดตั้งสายไฟ">
            <h3>ติดตั้งสายไฟ</h3>
            <p>เริ่มต้น 1,000 บาท</p>
            <span class="popular-badge">แนะนำ</span>
        </div>
    </div>
</section>

<section class="portfolio">
    <h2 class="section-title">ผลงานของเรา</h2>
    <div class="portfolio-grid">
        <div class="portfolio-item">
            <img src="image/air2.jpg" alt="ผลงาน 1">
            <div class="portfolio-overlay">
                <h3>งานซ่อมแอร์คอนโด</h3>
                <p>ระยะเวลา: 2 ชั่วโมง</p>
            </div>
        </div>
        <div class="portfolio-item">
            <img src="image/md1.jpg" alt="ผลงาน 2">
            <div class="portfolio-overlay">
                <h3>งานซ่อมหลังคารั่ว</h3>
                <p>ระยะเวลา: 1 วัน</p>
            </div>
        </div>
        <div class="portfolio-item">
            <img src="image/elc2.jpg" alt="ผลงาน 3">
            <div class="portfolio-overlay">
                <h3>งานติดตั้งระบบไฟ</h3>
                <p>ระยะเวลา: 3 ชั่วโมง</p>
            </div>
        </div>
    </div>
</section>

<section class="why-choose-us">
    <h2 class="section-title">เหตุผลที่ต้องเลือกเรา</h2>
    <div class="reasons-grid">
        <div class="reason-card">
            <i class="fas fa-certificate"></i>
            <h3>รับประกันผลงาน</h3>
            <p>รับประกันงานซ่อมนาน 6 เดือน พร้อมบริการหลังการขาย</p>
        </div>
        <div class="reason-card">
            <i class="fas fa-clock"></i>
            <h3>บริการ 24/7</h3>
            <p>พร้อมให้บริการตลอด 24 ชั่วโมง ไม่มีวันหยุด</p>
        </div>
        <div class="reason-card">
            <i class="fas fa-user-tie"></i>
            <h3>ช่างมืออาชีพ</h3>
            <p>ทีมช่างผ่านการอบรมและได้รับการรับรองมาตรฐาน</p>
        </div>
        <div class="reason-card">
            <i class="fas fa-shield-alt"></i>
            <h3>ความปลอดภัยสูงสุด</h3>
            <p>มีประกันความเสียหายระหว่างการซ่อมสูงสุด 100,000 บาท</p>
        </div>
    </div>
</section>

            
        </main>

        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>เกี่ยวกับเรา</h3>
                    <p>Mendcare Service ให้บริการซ่อมบำรุงครบวงจร ด้วยทีมช่างมืออาชีพ</p>
                </div>
                <div class="footer-section">
                    <h3>ติดต่อเรา</h3>
                    <p>โทร: 02-xxx-xxxx</p>
                    <p>อีเมล: contact@mendcare.com</p>
                </div>
                <div class="footer-section">
                    <h3>ติดตามเรา</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-line"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Mendcare Service. สงวนลิขสิทธิ์.</p>
            </div>
        </footer>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mainContent = document.getElementById('main-content');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            
            if (sidebar.classList.contains('active')) {
                mainContent.style.marginLeft = '280px';
                document.body.style.overflow = 'hidden';
            } else {
                mainContent.style.marginLeft = '0';
                document.body.style.overflow = 'auto';
            }
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const navToggle = document.querySelector('.nav-toggle');
            
            if (!sidebar.contains(e.target) && !navToggle.contains(e.target) && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });

        // Close sidebar when pressing Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.getElementById('sidebar').classList.contains('active')) {
                toggleSidebar();
            }
        });
    </script>
</body>
</html>
