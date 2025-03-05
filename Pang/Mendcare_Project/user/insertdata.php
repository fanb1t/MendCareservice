<?php
ob_start(); // เริ่มเก็บ output buffer
include 'sidebar.php';

// รับค่า sub_service_id จาก URL
$selected_sub_services = isset($_GET['items']) ? explode(',', $_GET['items']) : [];

// ตรวจสอบว่ามี request_id ถูกส่งมาหรือไม่
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : null;
$request_data = [];
$user_data = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $request_date = date('Y-m-d H:i:s', strtotime($_POST['datetime']));

    $conn->begin_transaction();

    try {
        // ตรวจสอบว่ามีข้อมูลผู้ใช้อยู่แล้วหรือไม่
        if (empty($user_data)) {
            // ถ้าไม่มีข้อมูลผู้ใช้ ให้ทำการ insert
            $stmt = $conn->prepare("INSERT INTO users (user_id, name_last, address) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", 
                $user_id,
                $_POST['name_last'],
                $_POST['address']
            );
        } else {
            // ถ้ามีข้อมูลผู้ใช้อยู่แล้ว ให้ทำการ update
            $stmt = $conn->prepare("UPDATE users SET name_last = ?, address = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", 
                $_POST['name_last'],
                $_POST['address'],
                $user_id
            );
        }
        $stmt->execute();

        // ตรวจสอบว่ามี request_id หรือไม่
        if ($request_id) {
            // อัปเดต request ที่มีอยู่
            $stmt = $conn->prepare("UPDATE requests SET request_date = ?, notes = ?, status = 'pending' WHERE request_id = ?");
            $stmt->bind_param("ssi", 
                $request_date,
                $_POST['description'],
                $request_id
            );
        } else {
            // สร้าง request ใหม่
            $stmt = $conn->prepare("INSERT INTO requests (user_id, request_date, notes, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("iss", 
                $user_id, 
                $request_date, 
                $_POST['description']
            );
        }
        $stmt->execute();
        $request_id = $conn->insert_id;

        // บันทึกบริการที่เลือกลงตาราง request_services
        foreach ($selected_sub_services as $sub_service_id) {
            $stmt = $conn->prepare("INSERT INTO request_services (request_id, sub_service_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $request_id, $sub_service_id);
            $stmt->execute();
        }

        $conn->commit();
        ob_end_clean(); // ล้าง output buffer ก่อนใช้ header
        header('Location: success.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage() . "');
            window.history.back();
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกข้อมูลบริการซ่อม | Mendcare Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<style>
:root {
    --primary-color: #d00000;
    --success-color: #28a745;
    --text-color: #333;
    --border-color: #ddd;
    --background-color: #f8f9fa;
}

body {
    font-family: 'Prompt', sans-serif;
    background-color: var(--background-color);
    margin: 0;
    padding: 0;
    color: var(--text-color);
}

.container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 20px;
}

.form-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

h1 {
    color: var(--primary-color);
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.8rem;
}

.progress {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 3rem;
    position: relative;
}

.progress::before {
    content: "";
    position: absolute;
    top: 50%;
    left: calc(50% - 100px);
    width: 200px;
    height: 4px;
    background: var(--border-color);
    z-index: 0;
}

.progress::after {
    content: "";
    position: absolute;
    top: 50%;
    left: calc(50% - 100px);
    width: 0;
    height: 4px;
    background: rgb(255, 87, 51);
    z-index: 0;
    transition: width 0.5s ease;
}

.progress.step-2::after {
    width: 200px;
}

.step {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--border-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    position: relative;
    z-index: 1;
    margin: 0 50px;
    transition: all 0.3s ease;
}

.step.active {
    background: rgb(255, 87, 51);
    transform: scale(1.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

input, textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

input:focus, textarea:focus {
    outline: none;
    border-color: var(--primary-color);
}

.btn-submit, .btn-confirm {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    font-size: 1.1rem;
    transition: background 0.3s ease;
}

.btn-confirm {
    background: var(--success-color);
}

.btn-submit:hover, .btn-confirm:hover {
    opacity: 0.9;
}

.button-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-back {
    background: #6c757d;
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 5px;
    cursor: pointer;
    flex: 1;
    font-size: 1.1rem;
    transition: background 0.3s ease;
}

.btn-back:hover {
    background: #5a6268;
}

.btn-confirm {
    flex: 1;
}

.confirmation-details {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.confirm-item {
    display: flex;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.confirm-item:last-child {
    border-bottom: none;
}

.confirm-item .label {
    font-weight: 600;
    min-width: 180px;
    color: #495057;
}

.confirm-item .value {
    color: #2196F3;
    flex: 1;
}

#confirmation-section {
    display: none;
}
</style>
<body>
    <div class="container">
        <div class="progress">
            <div id="step1" class="step active"><i class="fas fa-user"></i></div>
            <div id="step2" class="step"><i class="fas fa-check"></i></div>
        </div>
        
        <div id="form-section" class="form-card">
            <h1>กรอกข้อมูลการซ่อม</h1>
            <form onsubmit="event.preventDefault(); showConfirmation();" class="service-form">
                <div class="form-group">
                    <label for="name_last">ชื่อ-นามสกุล</label>
                    <input type="text" id="name_last" name="name_last" placeholder="กรุณากรอกชื่อ-นามสกุล" value="<?php echo isset($user_data['name_last']) ? $user_data['name_last'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="address">ที่อยู่</label>
                    <textarea id="address" name="address" placeholder="กรุณากรอกที่อยู่"><?php echo isset($user_data['address']) ? $user_data['address'] : ''; ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="datetime">วันที่และเวลานัดหมาย</label>
                        <input type="datetime-local" id="datetime" name="datetime">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">รายละเอียดการซ่อม</label>
                    <textarea id="description" name="description" rows="4" placeholder="กรุณาระบุรายละเอียดการซ่อม"></textarea>
                </div>
                <button type="submit" class="btn-submit">ถัดไป <i class="fas fa-arrow-right"></i></button>
            </form>
        </div>

        <div id="confirmation-section" class="form-card">
            <h1>ยืนยันข้อมูลการซ่อม</h1>
            <div class="confirmation-details">
                <div class="confirm-item">
                    <span class="label">ชื่อ-นามสกุล:</span>
                    <span id="confirm-name" class="value"></span>
                </div>
                <div class="confirm-item">
                    <span class="label">ที่อยู่:</span>
                    <span id="confirm-address" class="value"></span>
                </div>
                <div class="confirm-item">
                    <span class="label">วันที่และเวลา:</span>
                    <span id="confirm-datetime" class="value"></span>
                </div>
                <div class="confirm-item">
                    <span class="label">รายละเอียดการซ่อม:</span>
                    <span id="confirm-description" class="value"></span>
                </div>
            </div>
            <form action="" method="post" class="service-form">
                <input type="hidden" name="name_last" id="hidden-name_last">
                <input type="hidden" name="address" id="hidden-address">
                <input type="hidden" name="datetime" id="hidden-datetime">
                <input type="hidden" name="description" id="hidden-description">
                <div class="button-group">
                    <button type="button" class="btn-back" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i> แก้ไขข้อมูล
                    </button>
                    <button type="submit" class="btn-confirm">
                        ยืนยันข้อมูล <i class="fas fa-check"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
function showConfirmation() {
    // ดึงค่าจากฟอร์ม
    const name_last = document.getElementById('name_last').value.trim();
    const address = document.getElementById('address').value.trim();
    const datetime = document.getElementById('datetime').value.trim();
    const description = document.getElementById('description').value.trim();

    // ตรวจสอบค่าว่าง
    let errors = [];
    if (!name_last) errors.push('ชื่อ-นามสกุล');
    if (!address) errors.push('ที่อยู่');
    if (!datetime) errors.push('วันที่และเวลานัดหมาย');
    if (!description) errors.push('รายละเอียดการซ่อม');

    // หากมีฟิลด์ว่าง
    if (errors.length > 0) {
        alert('กรุณากรอกข้อมูลให้ครบทุกช่อง: ' + errors.join(', '));
        return; // หยุดการทำงานหากข้อมูลไม่ครบ
    }

    // อัปเดตค่า hidden fields
    document.getElementById('hidden-name_last').value = name_last;
    document.getElementById('hidden-address').value = address;
    document.getElementById('hidden-datetime').value = datetime;
    document.getElementById('hidden-description').value = description;

    // อัปเดตข้อมูลในส่วนยืนยัน
    document.getElementById('confirm-name').textContent = name_last || '-';
    document.getElementById('confirm-address').textContent = address || '-';
    document.getElementById('confirm-datetime').textContent = datetime ? formatDateTime(datetime) : '-';
    document.getElementById('confirm-description').textContent = description || '-';

    // แสดงส่วนยืนยันข้อมูล
    document.getElementById('form-section').style.display = 'none';
    document.getElementById('confirmation-section').style.display = 'block';
    document.querySelector('.progress').classList.add('step-2');
    document.getElementById('step2').classList.add('active');
}

function goBack() {
    document.getElementById('form-section').style.display = 'block';
    document.getElementById('confirmation-section').style.display = 'none';
    document.querySelector('.progress').classList.remove('step-2');
    document.getElementById('step2').classList.remove('active');
}

function formatDateTime(dateTimeStr) {
    const dt = new Date(dateTimeStr);
    return dt.toLocaleString('th-TH', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23'
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('form-section').style.display = 'block';
    document.getElementById('confirmation-section').style.display = 'none';
    document.getElementById('step2').classList.remove('active');
    document.querySelector('.progress').classList.remove('step-2');
});
</script>
</body>
</html>
<?php
ob_end_flush(); // ส่ง output ที่เก็บไว้ทั้งหมด
?>