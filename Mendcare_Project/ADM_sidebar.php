<style>
    body {
            background-color:rgba(255, 204, 0, 0.17);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, rgb(164, 0, 11) 0%, rgb(249, 132, 15) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .sidenav {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            background: linear-gradient(135deg, rgb(164, 0, 11) 0%, rgb(249, 132, 15) 100%);
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
        }

        .sidenav a {
            padding: 8px 8px 8px 32px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            transition: 0.3s;
        }

        .sidenav a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidenav .closebtn {
            position: absolute;
            top: 0;
            right: 25px;
            font-size: 36px;
            margin-left: 50px;
        }

        .nav-toggle {
            font-size: 30px;
            cursor: pointer;
            position: fixed;
            top: 20px;
            left: 20px;
            color: white;
            z-index: 999;
        }

        .upload-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 2rem;
            margin-top: 2rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, rgb(6, 19, 255) 0%, rgb(35, 185, 255));
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, rgb(164, 0, 11) 0%, rgb(249, 132, 15));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.2);
        }
</style>
<div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="toggleNav()">&times;</a>
        <a href="ind.php"><i class="fas fa-home me-2"></i> หน้าแรก</a>
        <a href="service_request.php"><i class="fas fa-file-alt me-2"></i> คำขอบริการ</> 
        <a href="upload_multiple_files.php"><i class="fas fa-cloud-upload-alt me-2"></i>รูปภาพ/บริการ</a>
        <a href="#"><i class="fas fa-user-cog me-2"></i> ข้อมูลช่าง</a>
        <a href="#"><i class="fas fa-chart-bar me-2"></i> รายงาน</a>
    </div>
