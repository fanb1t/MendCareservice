<?php include 'sidebar.php'; ?>
<?php
// ฟังก์ชันดึงบริการยอดนิยม
function getPopularServices() {
    global $conn;
    $sql = "SELECT ss.*, sc.name as category_name 
            FROM sub_services ss 
            JOIN service_categories sc ON ss.service_category_id = sc.service_category_id 
            ORDER BY RAND() LIMIT 3";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$popularServices = getPopularServices();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mendcare Service - บริการซ่อมบำรุงครบวงจร โดยทีมช่างมืออาชีพ พร้อมรับประกันผลงาน">
    <title>Mendcare Service - ระบบช่างซ่อมมืออาชีพ</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* เพิ่มฟอนต์ Google */
@import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');

/* ตั้งค่าสีและตัวแปรหลัก */
:root {
    --primary-color: #374151;
    --secondary-color: #6B7280;
    --accent-color: #10B981;
    --gradient-1: linear-gradient(45deg, #374151, #4B5563);
    --gradient-2: linear-gradient(45deg, #6B7280, #9CA3AF);
    --gradient-3: linear-gradient(45deg, #10B981, #059669);
    --text-color: #1F2937;
    --light-bg: #F9FAFB;
    --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    --hover-shadow: 0 20px 40px rgba(55, 65, 81, 0.15);
}

/* Base Styles */
body {
    background-color: #F5F5F5;  /* สีเทาอ่อนแบบคลาสสิค */
    font-family: 'Kanit', sans-serif;
    line-height: 1.6;
    color: var(--text-color);
    background: var(--primary-color);
}

/* Hero Section */
.hero {
    background:linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('image/chang.png');
    background-size: cover;
    background-position: center;
    min-height: 60vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 0 20px;
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 50%);
    animation: pulse 8s infinite;
}

.hero h1 {
    font-size: 4.5em;
    font-weight: 700;
    color: white;
    text-shadow: 2px 2px 15px rgba(0,0,0,0.3);
    margin-bottom: 30px;
    background: linear-gradient(45deg, #fff, #e0e7ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: glow 3s infinite;
}
.hero p {
    color: #FFFFFF;
    font-size: 1.5em;
    text-align: center;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
}

/* Service Cards */
.services-grid {
    background-color: #F5F5F5;  /* สีเทาอ่อนแบบคลาสสิค */
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 35px;
    padding: 60px 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.service-card {
    background: white;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: 2px solid transparent;
    box-shadow: var(--card-shadow);
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--gradient-1);
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: -1;
}

.service-card:hover {
    transform: translateY(-15px) scale(1.02);
    border-color: var(--secondary-color);
    box-shadow: var(--hover-shadow);
}

.service-card:hover::before {
    opacity: 0.1;
}

.service-icon {
    font-size: 3em;
    background: linear-gradient(45deg, #2563EB, #60A5FA);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}
}

.service-card:hover .service-icon {
    transform: scale(1.2) rotate(5deg);
}

/* Popular Services */
.popular-services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 40px;
    padding: 60px 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.popular-service-card {
    position: relative;
    border-radius: 25px;
    background: white;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    transition: all 0.4s ease;
}

.popular-service-card:hover {
    transform: translateY(-15px);
    box-shadow: var(--hover-shadow);
}

.popular-service-card img {
    width: 100%;
    height: 320px;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.popular-service-card:hover img {
    transform: scale(1.1);
}

.popular-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--gradient-2);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 600;
    box-shadow: 0 5px 15px rgba(244, 114, 182, 0.3);
}

/* Animations */
@keyframes pulse {
    0% { transform: translate(-50%, -50%) scale(1); }
    50% { transform: translate(-50%, -50%) scale(1.2); }
    100% { transform: translate(-50%, -50%) scale(1); }
}

@keyframes glow {
    0% { text-shadow: 0 0 10px rgba(255,255,255,0.8); }
    50% { text-shadow: 0 0 20px rgba(255,255,255,0.8), 0 0 30px rgba(79, 70, 229, 0.6); }
    100% { text-shadow: 0 0 10px rgba(255,255,255,0.8); }
}

/* ส่วนผลงานของเรา */
.portfolio {
    padding: 80px 0;
    background: linear-gradient(to bottom, #F9FAFB, #F3F4F6);
}

.portfolio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    padding: 40px;
    max-width: 1400px;
    margin: 0 auto;
}

.portfolio-item {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.4s ease;
}

.portfolio-item img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.portfolio-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.portfolio-item:hover img {
    transform: scale(1.1);
}

.portfolio-overlay {
    position: absolute;
    bottom: -100%;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
    padding: 30px;
    color: white;
    transition: bottom 0.4s ease;
}

.portfolio-item:hover .portfolio-overlay {
    bottom: 0;
}

/* ส่วนเหตุผลที่ต้องเลือกเรา */
.why-choose-us {
    padding: 100px 0;
    background: #FFFFFF;
}

.reasons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 40px;
    padding: 40px;
    max-width: 1400px;
    margin: 0 auto;
}

.reason-card {
    background: white;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    transition: all 0.4s ease;
    position: relative;
    border: 2px solid #E5E7EB;
}

.reason-card:hover {
    border-color: #374151;
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.reason-card i {
    font-size: 3em;
    color: #374151;
    margin-bottom: 20px;
    transition: all 0.4s ease;
}

.reason-card:hover i {
    transform: scale(1.2);
    color: #10B981;
}

.reason-card h3 {
    font-size: 1.5em;
    color: #1F2937;
    margin-bottom: 15px;
    font-weight: 600;
}

.reason-card p {
    color: #6B7280;
    line-height: 1.7;
}

.section-title {
    background-color: #F5F5F5;  /* สีเทาอ่อนแบบคลาสสิค */
    text-align: center;
    font-size: 2.5em;
    color: #1F2937;
    margin-bottom: 50px;
    position: relative;
    padding-bottom: 20px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(to right, #374151, #6B7280);
    border-radius: 2px;
}

/* Footer */
footer {
    background: var(--gradient-1);
    color: white;
    padding: 100px 20px 60px;
    margin-top: 120px;
    position: relative;
    overflow: hidden;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 50px;
    max-width: 1400px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.footer-section h3 {
    font-size: 1.8em;
    margin-bottom: 25px;
    position: relative;
    display: inline-block;
}

.footer-section h3::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 3px;
    background: var(--accent-color);
    border-radius: 2px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero h1 {
        font-size: 3em;
    }
    
    .services-grid,
    .popular-services-grid {
        grid-template-columns: 1fr;
        padding: 40px 15px;
    }
}

    </style>
</head>
<body>
    <main>
        <!-- ส่วน Hero -->
     <section class="hero">
    <h1>บริการซ่อมบำรุงอย่างทั่วถึง</h1>
     <p>มั่นใจกับทีมช่างมืออาชีพที่ผ่านการรับรอง พร้อมรับประกันผลงาน</p>
     </section>

     <!-- ส่วนบริการ -->
    <section class="services">
        <h2 class="section-title">บริการของเรา</h2>
        <div class="services-grid">
            <div class="service-card" onclick="window.location.href='air_conditioning.php';">
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

   <!-- ส่วนบริการยอดนิยม -->
<section class="popular-services">
    <h2 class="section-title">บริการยอดนิยม</h2>
    <div class="popular-services-grid">
        <?php foreach($popularServices as $service): ?>
            <div class="popular-service-card">
                <img src="<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>">
                <h3><?php echo $service['name']; ?></h3>
                <p>เริ่มต้น <?php echo number_format($service['price'], 2); ?> บาท</p>
                <span class="popular-badge">ยอดนิยม</span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
    <!-- ส่วน Portfolio -->
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
    <!-- ส่วนเหตุผลที่เลือกเรา -->
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
    <br>
    <center>
     <p>&copy; 2025 Mendcare Service.</p>
    </center>
    </div>
    </footer>
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation for service cards
            const serviceCards = document.querySelectorAll('.service-card');
            serviceCards.forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                });
            });

            // Portfolio overlay effects
            const portfolioItems = document.querySelectorAll('.portfolio-item');
            portfolioItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.querySelector('.portfolio-overlay').style.opacity = '1';
                });
                item.addEventListener('mouseleave', function() {
                    this.querySelector('.portfolio-overlay').style.opacity = '0';
                });
            });
        });
    </script>
</body>
</html>