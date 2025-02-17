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
    --primary-color: #2196F3;
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

/* Rest of your existing CSS remains the same */



.form-group {
    margin-bottom: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
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
    <div id="main-content">
        <?php include 'sidebar.php'; ?>
        
        <header>
            <div class="header-container">
                <div class="nav-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </div>
                <a href="/" class="logo">
                    <i class="fas fa-wrench"></i>
                    Mendcare Service
                </a>
            </div>
        </header>

        <div class="container">
            <div class="progress">
                <div id="step1" class="step active"><i class="fas fa-user"></i></div>
                <div id="step2" class="step"><i class="fas fa-check"></i></div>
            </div>
            
            <div id="form-section" class="form-card">
                <h1>กรอกข้อมูลการซ่อม</h1>
                <form onsubmit="event.preventDefault(); showConfirmation();" class="service-form">
                    <div class="form-group">
                        <label for="name">ชื่อ-นามสกุล</label>
                        <input type="text" id="name" name="name" required placeholder="กรุณากรอกชื่อ-นามสกุล">
                    </div>

                    <div class="form-group">
                        <label for="address">ที่อยู่</label>
                        <textarea id="address" name="address" required placeholder="กรุณากรอกที่อยู่"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="datetime">วันที่และเวลานัดหมาย</label>
                            <input type="datetime-local" id="datetime" name="datetime" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">จำนวนชิ้นงาน</label>
                            <input type="number" id="quantity" name="quantity" min="1" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">รายละเอียดการซ่อม</label>
                        <textarea id="description" name="description" rows="4" required placeholder="กรุณาระบุรายละเอียดการซ่อม"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input type="tel" id="phone" name="phone" required placeholder="กรุณากรอกเบอร์โทรศัพท์">
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
            <span class="label">จำนวนชิ้นงาน:</span>
            <span id="confirm-quantity" class="value"></span>
        </div>
        <div class="confirm-item">
            <span class="label">รายละเอียดการซ่อม:</span>
            <span id="confirm-description" class="value"></span>
        </div>
        <div class="confirm-item">
            <span class="label">เบอร์โทรศัพท์:</span>
            <span id="confirm-phone" class="value"></span>
        </div>
    </div>
    <form action="submit.php" method="post">
        <input type="hidden" name="name" id="hidden-name">
        <input type="hidden" name="address" id="hidden-address">
        <input type="hidden" name="datetime" id="hidden-datetime">
        <input type="hidden" name="quantity" id="hidden-quantity">
        <input type="hidden" name="description" id="hidden-description">
        <input type="hidden" name="phone" id="hidden-phone">
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
    </div>
    <script>
function showConfirmation() {
    const formData = {
        name: document.getElementById('name').value,
        address: document.getElementById('address').value,
        phone: document.getElementById('phone').value,
        datetime: formatDateTime(document.getElementById('datetime').value),
        quantity: document.getElementById('quantity').value,
        description: document.getElementById('description').value
    };

    if (!validateFormData(formData)) {
        alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');
        return;
    }

    Object.keys(formData).forEach(key => {
        const confirmElement = document.getElementById(`confirm-${key}`);
        const hiddenElement = document.getElementById(`hidden-${key}`);
        
        if (confirmElement) {
            confirmElement.textContent = formData[key];
        }
        if (hiddenElement) {
            hiddenElement.value = formData[key];
        }
    });

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

function validateFormData(formData) {
    return Object.values(formData).every(value => value.trim() !== '');
}

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
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
