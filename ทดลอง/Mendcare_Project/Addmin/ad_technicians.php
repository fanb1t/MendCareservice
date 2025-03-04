<?php include 'addmin_sidebar.php'; ?>
<?php
session_start();
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $tech_id = $_POST['technician_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE technicians SET status = ? WHERE technician_id = ?");
    $stmt->bind_param("si", $status, $tech_id);
    $stmt->execute();
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$sql = "SELECT t.*, 
        COALESCE(t.status, 'ว่าง') as status,
        (SELECT COUNT(*) FROM requests r 
         WHERE r.technician_id = t.technician_id 
         AND r.status IN ('pending', 'accepted')) as active_jobs
        FROM technicians t
        ORDER BY t.name";

$result = $conn->query($sql);
$technicians = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการข้อมูลช่าง - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        h1 {
            font-size: 2.5em;
            color: #1a237e;
            text-align: center;
            margin: 30px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
        }

        .technicians-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 15px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .technicians-table thead th {
            background: #1a237e;
            color: #ffffff;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #0d47a1;
        }

        .technicians-table tbody td {
            padding: 15px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        .technicians-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .technicians-table tbody tr:hover {
            background-color: #e8eaf6;
            transition: all 0.3s ease;
        }

        .status-badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }

        .status-available {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #81c784;
        }

        .status-busy {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .btn-available {
            background: #4caf50;
            color: white;
        }

        .btn-busy {
            background: #f44336;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .job-count {
            background: #e3f2fd;
            color: #1565c0;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 600;
            border: 1px solid #90caf9;
        }

        .working-info {
            color: #424242;
            font-size: 14px;
            line-height: 1.4;
        }
        
    </style>
</head>
<body>

    <div class="content">
        <h1>จัดการข้อมูลช่าง</h1>
        
        <table class="technicians-table">
            <thead>
                <tr>
                    <th>รหัสช่าง</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>เบอร์โทร</th>
                    <th>วันทำงาน</th>
                    <th>เวลาทำงาน</th>
                    <th>งานที่กำลังทำ</th>
                    <th>สถานะ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($technicians as $tech): ?>
                    <tr>
                        <td><?php echo $tech['technician_id']; ?></td>
                        <td><?php echo $tech['name']; ?></td>
                        <td><?php echo $tech['phone']; ?></td>
                        <td class="working-info"><?php echo $tech['working_days']; ?></td>
                        <td class="working-info"><?php echo $tech['working_hours']; ?></td>
                        <td><span class="job-count"><?php echo $tech['active_jobs']; ?> งาน</span></td>
                        <td>
                            <span class="status-badge <?php echo $tech['status'] === 'ว่าง' ? 'status-available' : 'status-busy'; ?>">
                                <?php echo $tech['status']; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="technician_id" value="<?php echo $tech['technician_id']; ?>">
                                <?php if($tech['status'] === 'ว่าง'): ?>
                                    <button type="submit" name="update_status" value="ไม่ว่าง" class="btn btn-busy">
                                        ตั้งเป็นไม่ว่าง
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="update_status" value="ว่าง" class="btn btn-available">
                                        ตั้งเป็นว่าง
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
