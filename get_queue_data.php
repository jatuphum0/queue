<?php
// get_queue_data.php - แก้ไขเพื่อรองรับการดึงข้อมูลแผนกเดียว
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// การตั้งค่าฐานข้อมูล
require_once 'config.php';

try {
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8");

    // รับพารามิเตอร์แผนกที่เลือก
    $selectedDepartment = isset($_GET['department']) ? $_GET['department'] : '';
    
    if (empty($selectedDepartment)) {
        throw new Exception("No department selected");
    }

    // คำสั่ง SQL เพื่อดึงข้อมูลคิว 10 รายการล่าสุดของแผนกที่เลือก
    $sql = "
        SELECT 
        vn,hn,
            oqueue,
            name,
            cur_dep_time,
            lab_count,
            report_count,
            doctor,
            doctor_name
        FROM view_ovst_doctor 
        WHERE cur_dep = ? and 
        vn not in (select vn from queue_called where date(cur_time) = curdate()  and cur_dep =?)
        AND cur_dep_time IS NOT NULL
        ORDER BY cur_dep_time asc
        LIMIT 20
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    
    $stmt->bind_param("ss", $selectedDepartment, $selectedDepartment);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Query failed: " . $mysqli->error);
    }

    $queues = [];
    while ($row = $result->fetch_assoc()) {
        // แปลงเวลาให้อยู่ในรูปแบบที่อ่านง่าย
        $time = date('H:i', strtotime($row['cur_dep_time']));
        
        $queues[] = [
            'vn' => $row['vn'],
            'hn' => $row['hn'],
            'oqueue' => $row['oqueue'],
            'name' => $row['name'],
            'cur_dep_time' => $time,
            'lab_count' => (int)$row['lab_count'],
            'report_count' => (int)$row['report_count'],
            'doctor' => $row['doctor'],
            'doctor_name' => $row['doctor_name']
        ];
    }

    echo json_encode([
        'success' => true,
        'queues' => $queues,
        'department' => $selectedDepartment,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>