<!-- sidebar.php -->
<div id="sidebar" class="sidebar">
    <span class="close-btn" onclick="toggleSidebar()">
        <i class="fas fa-times"></i>
    </span>
    <ul class="sidebar-menu">
        <li><a href="/"><i class="fas fa-home"></i> หน้าแรก</a></li>
        <li><a href="/services"><i class="fas fa-tools"></i> บริการทั้งหมด</a></li>
        <li><a href="/booking"><i class="fas fa-calendar-alt"></i> จองบริการ</a></li>
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