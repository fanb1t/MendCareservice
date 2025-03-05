<?php
session_start();
require_once 'connect.php';
require_once 'sidebar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ind.php');
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $newname = "profile_" . $user_id . "." . $filetype;
            $target = "image/" . $newname;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target)) {
                $sql = "UPDATE users SET name=?, email=?, phone=?, address=?, profile_picture=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $name, $email, $phone, $address, $newname, $user_id);
            }
        }
    } else {
        $sql = "UPDATE users SET name=?, email=?, phone=?, address=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        if (isset($newname)) $_SESSION['profile_picture'] = $newname;
        header('Location: profile.php?success=1');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ผู้ใช้</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    .profile-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-card {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.profile-header {
    margin-bottom: 2rem;
    text-align: center;
}

.profile-picture-section {
    text-align: center;
    margin-bottom: 2rem;
}

#profile-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 1rem;
    border: 3px solid var(--primary-color);
}

.upload-btn-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
}

.btn {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    background-color: white;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
}

.upload-btn-wrapper input[type=file] {
    font-size: 100px;
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    cursor: pointer;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #666;
    font-weight: 500;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
}

.save-btn {
    background-color: var(--primary-color);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s;
}

.save-btn:hover {
    background-color: #b00000;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.alert.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>
<body>

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <h2>โปรไฟล์ของฉัน</h2>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert success">อัพเดทข้อมูลสำเร็จ!</div>
            <?php endif; ?>
        </div>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-picture-section">
                <img src="<?php echo $user['profile_picture'] ? 'image/' . $user['profile_picture'] : 'assets/default-avatar.png'; ?>" 
                     alt="Profile Picture" 
                     id="profile-preview">
                <div class="upload-btn-wrapper">
                    <button class="btn" type="button">เปลี่ยนรูปโปรไฟล์</button>
                    <input type="file" name="profile_picture" id="profile-upload" accept="image/*">
                </div>
            </div>

            <div class="form-group">
                <label for="name">ชื่อ</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">อีเมล</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>

            <div class="form-group">
                <label for="address">ที่อยู่</label>
                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>

            <button type="submit" class="save-btn">บันทึกการเปลี่ยนแปลง</button>
        </form>
    </div>
</div>

<script>
document.getElementById('profile-upload').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

</body>
</html>