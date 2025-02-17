<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="ind.css">
</head>
<body>
<!-- sidebar.php -->
<div id="sidebar" class="sidebar">
    <span class="close-btn" onclick="toggleSidebar()">
        <i class="fas fa-times"></i>
    </span>
    <ul class="sidebar-menu">
        <li><a href="/"><i class="fas fa-home"></i> หน้าแรก</a></li>
        <li><a href="/services"><i class="fas fa-tools"></i> บริการทั้งหมด</a></li>
        <li><a href="CollectService.php"><i class="fas fa-calendar-alt"></i> จองบริการ</a></li>
        <li><a href="/tracking"><i class="fas fa-map-marker-alt"></i> ติดตามงาน</a></li>
        <li><a href="/history"><i class="fas fa-history"></i> ประวัติการใช้บริการ</a></li>
        <li><a href="/promotions"><i class="fas fa-gift"></i> โปรโมชั่น</a></li>
        <li><a href="/reviews"><i class="fas fa-star"></i> รีวิวจากลูกค้า</a></li>
        <li><a href="/faq"><i class="fas fa-question-circle"></i> คำถามที่พบบ่อย</a></li>
        <li><a href="/contact"><i class="fas fa-phone"></i> ติดต่อเรา</a></li>
    </ul>
</div>
 
<!-- Overlay -->
<div id="overlay" class="overlay"></div>

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

 