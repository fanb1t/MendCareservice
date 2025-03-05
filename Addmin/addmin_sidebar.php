<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        background-color: #fff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
    }

    .header {
        background: #2C3E50;
        color: #FFFFFF;
        text-align: center;
        padding: 20px;
        font-size: 28px;
        font-weight: bold;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
        letter-spacing: 1px;
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999;
    }

    .sidenav {
        height: 100%;
        width: 0;
        position: fixed;
        z-index: 1000;
        top: 0;
        left: 0;
        background: #2C3E50;
        overflow-x: hidden;
        transition: 0.5s;
        padding-top: 60px;
    }

    .sidenav a {
        padding: 15px 20px;
        text-decoration: none;
        font-size: 18px;
        color: #FFFFFF;
        display: block;
        transition: 0.3s;
    }

    .sidenav a:hover {
        background: #2C3E50;
        color: #FFFFFF;
    }

    .sidenav .closebtn {
        position: absolute;
        top: 0;
        right: 25px;
        font-size: 36px;
        cursor: pointer;
        color: #FFFFFF;
    }

    .nav-toggle {
        font-size: 30px;
        cursor: pointer;
        position: fixed;
        top: 20px;
        left: 20px;
        color: #FFFFFF;
        z-index: 999;
    }
</style>
</head>
<body>
    <div class="header">ระบบจัดการช่างซ่อม</div>

    <span class="nav-toggle" onclick="toggleNav()">☰</span>

    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="toggleNav()">&times;</a>
        <a href="../User/ind.php"><i class="fas fa-home me-2"></i> หน้าแรก</a>
        <a href="ad_service_request.php"><i class="fas fa-file-alt me-2"></i> คำขอบริการ</a>
        <a href="upload_multiple_files.php"><i class="fas fa-cloud-upload-alt me-2"></i> รูปภาพ/บริการ</a>
        <a href="ad_technicians.php"><i class="fas fa-user-cog me-2"></i> ข้อมูลช่าง</a>
        <a href="reports.php"><i class="fas fa-chart-bar me-2"></i> รายงาน</a>
    </div>

    <script>
        function toggleNav() {
            let sidenav = document.getElementById("mySidenav");
            if (sidenav.style.width === "250px") {
                sidenav.style.width = "0";
            } else {
                sidenav.style.width = "250px";
            }
        }
    </script>
</body>
</html>