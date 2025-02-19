<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: rgb(202, 230, 146);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, rgb(27, 150, 14) 0%, rgb(105, 180, 18) 100%);
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
            background: linear-gradient(135deg, rgb(27, 150, 14) 0%, rgb(105, 180, 18) 100%);
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
            background: linear-gradient(45deg, rgb(27, 150, 14) 0%, rgb(105, 180, 18));
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, rgb(27, 150, 14) 0%, rgb(105, 180, 18));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.2);
        }
    </style>
</head>
<body>
    <span class="nav-toggle" onclick="toggleNav()">☰</span>

    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="toggleNav()">&times;</a>
        <a href="airservice.php"><i class="fas fa-home me-2"></i>Home</a>
        <a href="airservice.php"><i class="fas fa-images me-2"></i>Gallery</a>
        <a href="upload_multiple_files.php"><i class="fas fa-upload me-2"></i>Upload</a>
        <a href="#"><i class="fas fa-info-circle me-2"></i>About</a>
        <a href="#"><i class="fas fa-envelope me-2"></i>Contact</a>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="text-center mb-2"><i class="fas fa-upload me-2"></i>File Upload</h1>
            <p class="text-center mb-0">โปรดใส่รูปที่คุณต้องการแสดง</p>
        </div>
    </div>

    <div class="container">
        <div id="alert-box"></div>
        
        <form action="" method="post" enctype="multipart/form-data" class="upload-form">
            <h3 class="mb-3">Upload Files</h3>

            <label class="form-label">Select files:</label>
            <input type="file" id="fileToUpload" name="fileToUpload[]" class="form-control mb-3" multiple required onchange="updateFileInputs()">

            <div id="file-details"></div>
            <div class="mb-3">
            <label class="form-label">Select Service ID:</label>
            <input type="number" name="sub_services_id" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary" name="upload">
                <i class="fas fa-cloud-upload-alt me-2"></i>Upload Files
            </button>
        </form>
    </div>

    <!-- Rest of your existing PHP and JavaScript code -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleNav() {
            const sidenav = document.getElementById("mySidenav");
            if (sidenav.style.width === "250px") {
                sidenav.style.width = "0";
            } else {
                sidenav.style.width = "250px";
            }
        }
    </script>
     <?php
$target_dir = "image/";

if (isset($_POST["upload"])) {
    include "connect.php";

    // Add a new form field for sub_services_id
    $sub_services_id = $_POST["sub_services_id"];

    foreach ($_FILES["fileToUpload"]["tmp_name"] as $key => $value) {
        $original_name = basename($_FILES["fileToUpload"]["name"][$key]);
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $custom_name = !empty($_POST["fileNames"][$key]) ? $_POST["fileNames"][$key] : pathinfo($original_name, PATHINFO_FILENAME);
        $new_name = $custom_name . "." . $file_extension;
        $new_tmp = $_FILES["fileToUpload"]["tmp_name"][$key];
        $file_path = $target_dir . $new_name;

        if (move_uploaded_file($new_tmp, $file_path)) {
            // Update the existing record instead of inserting a new one
            $stmt = $conn->prepare("UPDATE sub_services SET image = ? WHERE sub_services_id = ?");
            $stmt->bind_param("si", $new_name, $sub_services_id);
            
            if ($stmt->execute()) {
                echo "<script>showAlert('✅ Image updated for ID: $sub_services_id', 'success');</script>";
            } else {
                echo "<script>showAlert('❌ Error updating image for ID: $sub_services_id', 'danger');</script>";
            }
            
            $stmt->close();
        } else {
            echo "<script>showAlert('❌ Cannot upload: $new_name', 'danger');</script>";
        }
    }
    $conn->close();
}
?>

</body>
</html>
