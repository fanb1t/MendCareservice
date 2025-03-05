<?php
require_once 'connect.php';

// ดึงข้อมูลหมวดหมู่จากฐานข้อมูล
$sql = "SELECT service_category_id, name FROM service_categories ORDER BY name";
$result = $conn->query($sql);
$categories = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดบริการย่อย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .form-label {
            font-weight: 600;
            color: #34495e;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52,152,219,0.25);
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        .preview-image {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>
<?php include 'addmin_sidebar.php'; ?>
<br><br>
    <div class="container mt-5">
        <h2><i class="fas fa-upload me-2"></i>อัปโหลดบริการย่อย</h2>
        
        <form action="process_upload.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-list-alt me-2"></i>หมวดหมู่หลัก</label>
                <select class="form-select" name="service_category_id" required>
                    <option value="">เลือกหมวดหมู่</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category['service_category_id']; ?>">
                            <?php echo $category['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-tag me-2"></i>ชื่อบริการย่อย</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-align-left me-2"></i>รายละเอียด</label>
                <textarea class="form-control" name="description" rows="4" required></textarea>
            </div>
            
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-money-bill me-2"></i>ราคา (บาท)</label>
                <input type="number" class="form-control" name="price" step="0.01" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-image me-2"></i>อัปโหลดรูปภาพ</label>
                <input type="file" class="form-control" name="image" accept="image/*" required onchange="previewImage(this)">
                <img id="preview" class="preview-image mt-3">
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-cloud-upload-alt me-2"></i>อัปโหลด
                </button>
            </div>
        </form>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>