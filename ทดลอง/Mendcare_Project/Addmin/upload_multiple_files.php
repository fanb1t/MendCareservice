<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <span class="nav-toggle" onclick="toggleNav()">☰</span>
        <?php include 'addmin_sidebar.php'; ?>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="text-center mb-2"><i class="fas fa-upload me-2"></i>รูปภาพ/บริการ</h1>
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
