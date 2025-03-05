<?php
session_start();
require_once 'connect.php';

// คำสั่ง SQL สำหรับดึงข้อมูลสถิติรายเดือน

if (isset($_GET['month'])) {
    $month = $_GET['month'];
    $sql = "SELECT 
        DATE_FORMAT(request_date, '%Y-%m-%d') as request_date,
        customer_name,
        service_details,
        status,
        amount
    FROM requests 
    WHERE DATE_FORMAT(request_date, '%Y-%m') = ?
    ORDER BY request_date";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $daily_data = $result->fetch_all(MYSQLI_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($daily_data);
    exit();
}
$monthly_stats = $conn->query("
    SELECT 
        DATE_FORMAT(request_date, '%Y-%m') as month,
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'Reject service request' THEN 1 ELSE 0 END) as cancelled,
        SUM(amount) as total_revenue
    FROM requests 
    GROUP BY DATE_FORMAT(request_date, '%Y-%m')
    ORDER BY month DESC
");

$stats_data = $monthly_stats->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานบริการ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #E3F2FD;
        }
        .content {
            margin: 50px auto;
            padding: 20px;
            max-width: 1400px;
            width: 90%; /* เพิ่มการควบคุมความกว้าง */
            background: white;
        }

        h2 {
            color: rgb(0, 0, 0);
            margin-bottom: 30px;
            padding-bottom: 10px;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center; /* เพิ่มการจัดกึ่งกลางแนวนอน */
            gap: 10px;
            width: 100%; /* กำหนดความกว้างเต็ม */
            text-align: center; /* จัดข้อความกึ่งกลาง */
        }

        .statistics-container {
            margin-bottom: 30px;
        }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
        }

        .stat-card i {
            font-size: 2em;
            color: rgb(0, 0, 0);
            margin-bottom: 10px;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            margin: 40px auto;
            padding: 20px;
            max-width: 1000px;
            width: 90%; /* เพิ่มการควบคุมความกว้าง */
        }

        .chart-wrapper {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 400px;
            width: 100%;
            position: relative;
            margin: 0 auto; /* เพิ่มการจัดกึ่งกลาง */
        }


        .chart-title {
            color: #1A237E;
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 2px solid #F5F5F5;
        }

        .report-table th, 
        .report-table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }

        .report-table th {
            background-color:#1a237e;
            color: white;
            font-weight: 600;
            text-align: center;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .grand-total {
            background-color:#1a237e;
            color: white;
            font-weight: bold;
        }
        /* เพิ่ม Media Queries สำหรับ Responsive */
        @media screen and (max-width: 768px) {
        .charts-container {
            grid-template-columns: 1fr; /* แสดง 1 คอลัมน์บนหน้าจอขนาดเล็ก */
             gap: 20px;
        }
    
        .chart-wrapper {
            height: 300px; /* ปรับความสูงลงสำหรับหน้าจอขนาดเล็ก */
        }
}
        .daily-details {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
            background: #f9f9f9;
        }

        .daily-details th,
        .daily-details td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .daily-details th {
            background: #FF9800;
            color: white;
        }

        tr[data-month] {
            cursor: pointer;
        }

        tr[data-month]:hover {
            background-color: #f5f5f5;
        }

        .detail-row {
            background-color: #f8f9fa;
        }

        .detail-row td {
            padding: 0;
        }
    </style>
</head>
<body>
<?php include 'addmin_sidebar.php'; ?>
    <div class="content">
        <!-- ส่วนแสดงหัวข้อรายงาน -->
      <br>
        <h2>
            <i class="fas fa-file-invoice"></i>
            รายงานการให้บริการ
        </h2>
       <br>

        <div class="statistics-container">
            <!-- ส่วนแสดงการ์ดสถิติ -->
            <div class="stat-cards">
                <!-- การ์ดแสดงคำขอซ่อมทั้งหมด -->
                <div class="stat-card">
                    <i class="fas fa-tools"></i>
                    <h3>คำขอซ่อมทั้งหมด</h3>
                    <p><?php echo array_sum(array_column($stats_data, 'total_requests')); ?></p>
                </div>
                
                <!-- การ์ดแสดงงานเสร็จสิ้น -->
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>งานเสร็จสิ้น</h3>
                    <p><?php echo array_sum(array_column($stats_data, 'completed')); ?></p>
                </div>
                
                <!-- การ์ดแสดงรายได้รวม -->
                <div class="stat-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>รายได้รวม</h3>
                    <p><?php echo number_format(array_sum(array_column($stats_data, 'total_revenue')), 2); ?> บาท</p>
                </div>
            </div>

            <!-- ตารางแสดงข้อมูลรายเดือน -->
            <table class="report-table">
                <!-- ส่วนหัวตาราง -->
                <thead>
                    <tr>
                        <th>เดือน</th>
                        <th>จำนวนคำขอซ่อม</th>
                        <th>งานเสร็จสิ้น</th>
                        <th>งานยกเลิก</th>
                        <th>รายได้จากค่าบริการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // ตัวแปรสำหรับคำนวณผลรวม
                    $total_requests = 0;
                    $total_completed = 0;
                    $total_cancelled = 0;
                    $total_revenue = 0;

                    // วนลูปแสดงข้อมูลแต่ละเดือน
                    foreach ($stats_data as $month_stat) {
                        // แสดงข้อมูลแต่ละแถว
                        echo "<tr data-month='" . $month_stat['month'] . "' onclick='toggleDailyDetails(\"" . $month_stat['month'] . "\")'>";
                        echo "<td>" . date('F Y', strtotime($month_stat['month'] . '-01')) . "</td>";
                        echo "<td class='text-center'>" . $month_stat['total_requests'] . "</td>";
                        echo "<td class='text-center'>" . $month_stat['completed'] . "</td>";
                        echo "<td class='text-center'>" . $month_stat['cancelled'] . "</td>";
                        echo "<td class='text-right'>" . number_format($month_stat['total_revenue'], 2) . " บาท</td>";
                        echo "</tr>";
                    
                        // คำนวณผลรวม
                        $total_requests += $month_stat['total_requests'];
                        $total_completed += $month_stat['completed'];
                        $total_cancelled += $month_stat['cancelled'];
                        $total_revenue += $month_stat['total_revenue'];
                    }

                    // แสดงแถวผลรวมท้ายตาราง
                    echo "<tr class='grand-total'>";
                    echo "<td>ยอดรวมทั้งหมด</td>";
                    echo "<td class='text-center'>" . $total_requests . "</td>";
                    echo "<td class='text-center'>" . $total_completed . "</td>";
                    echo "<td class='text-center'>" . $total_cancelled . "</td>";
                    echo "<td class='text-right'>" . number_format($total_revenue, 2) . " บาท</td>";
                    echo "</tr>";
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ส่วนแสดงกราฟ -->
       <h2>
       <i class="fas fa-chart-bar"></i>
            กราฟรายงานการให้บริการ
        </h2>

<div class="charts-container">
    <!-- กราฟแสดงจำนวนคำขอซ่อมรายเดือน -->
    <div class="chart-wrapper">
        <div class="chart-title">กราฟแสดงจำนวนคำขอซ่อมรายเดือน</div>
        <canvas id="requestsChart"></canvas>
    </div>
    
    <!-- กราฟแสดงรายได้รายเดือน -->
    <div class="chart-wrapper">
        <div class="chart-title">กราฟแสดงรายได้รายเดือน</div>
        <canvas id="revenueChart"></canvas>
    </div>
</div>

    <!-- สคริปต์สำหรับสร้างกราฟ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
         function toggleDailyDetails(month) {
    const detailRow = document.getElementById('detail-' + month);
    if (detailRow) {
        detailRow.style.display = detailRow.style.display === 'none' ? 'table-row' : 'none';
    } else {
        fetch('reports.php?month=' + month)
            .then(response => response.json())
            .then(data => {
                const tr = document.createElement('tr');
                tr.id = 'detail-' + month;
                tr.className = 'detail-row';
                tr.innerHTML = `
                    <td colspan="5">
                        <table class="daily-details">
                            <thead>
                                <tr>
                                    <th>วันที่</th>
                                    <th>ชื่อลูกค้า</th>
                                    <th>รายละเอียดบริการ</th>
                                    <th>สถานะ</th>
                                    <th>ค่าบริการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.map(item => `
                                    <tr>
                                        <td>${item.request_date}</td>
                                        <td>${item.customer_name}</td>
                                        <td>${item.service_details}</td>
                                        <td>${item.status}</td>
                                        <td class="text-right">${item.amount} บาท</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </td>
                `;
                const monthRow = document.querySelector(`tr[data-month="${month}"]`);
                monthRow.parentNode.insertBefore(tr, monthRow.nextSibling);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
}
        document.addEventListener('DOMContentLoaded', function() {
            // ข้อมูลสำหรับกราฟ
            const monthlyData = <?php echo json_encode($stats_data); ?>;
            const labels = monthlyData.map(item => item.month);
            const requests = monthlyData.map(item => item.total_requests);
            const revenue = monthlyData.map(item => item.total_revenue);

            // สร้างกราฟแท่งแสดงจำนวนคำขอซ่อม
            new Chart(document.getElementById('requestsChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'จำนวนคำขอซ่อม',
                        data: requests,
                        backgroundColor: '#FF0000'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'คำขอซ่อมรายเดือน'
                        }
                    }
                }
            });

            // สร้างกราฟเส้นแสดงรายได้
            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'รายได้',
                        data: revenue,
                        borderColor: '#FF0000',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'รายได้รายเดือน'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>