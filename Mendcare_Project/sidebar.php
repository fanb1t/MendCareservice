
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
        <li><a href="ind.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
        <!-- <li><a href="/services"><i class="fas fa-tools"></i> บริการทั้งหมด</a></li> -->
        <li><a href="success.php"><i class="fas fa-calendar-alt"></i> จองบริการ</a></li>
        <li><a href="status.php"><i class="fas fa-map-marker-alt"></i> ติดตามงาน</a></li>
        <li><a href="service_his.php"><i class="fas fa-history"></i> ประวัติการใช้บริการ</a></li>
        <!-- <li><a href="/promotions"><i class="fas fa-gift"></i> โปรโมชั่น</a></li> -->
        <li><a href="/reviews"><i class="fas fa-star"></i> รีวิวจากลูกค้า</a></li>
        <li><a href="/faq"><i class="fas fa-question-circle"></i> คำถามที่พบบ่อย</a></li>
        <li><a href="/contact"><i class="fas fa-phone"></i> ติดต่อเรา</a></li>
    </ul>
</div>
 
<!-- Overlay -->
<div id="overlay" class="overlay"></div>



<script>
        // ฟังก์ชันสำหรับ Modal
function openLoginPopup() {
    document.getElementById("loginPopup").style.display = "flex";
}

function closeLoginPopup() {
    document.getElementById("loginPopup").style.display = "none";
}

function showLoginForm() {
    document.getElementById("login-form").classList.add("active");
    document.getElementById("register-form").classList.remove("active");
}

function showRegisterForm() {
    document.getElementById("register-form").classList.add("active");
    document.getElementById("login-form").classList.remove("active");
}

// ฟังก์ชันจัดการ Login
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.querySelector('#login-form input[name="email"]').value;
    const password = document.querySelector('#login-form input[name="password"]').value;
    
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('email', email);
    formData.append('password', password);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeLoginPopup();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

// ฟังก์ชันจัดการ Register
function handleRegister(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('action', 'register');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeLoginPopup();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

// ฟังก์ชันจัดการ Logout
function handleLogout() {
    const formData = new FormData();
    formData.append('action', 'logout');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: 'ออกจากระบบสำเร็จ!',
                text: 'ขอบคุณที่ใช้บริการ',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            }).then((result) => {
                location.reload();
            });
        }
    });
}

// ฟังก์ชันจัดการ Sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

    </script>
 
</body>
</html>

 