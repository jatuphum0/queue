<?php
// get_called_queue.php - ดึงข้อมูลคิวที่เรียกแล้ว
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';


try {
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8");

    $selectedDepartment = isset($_GET['department']) ? $_GET['department'] : '';
    
    if (empty($selectedDepartment)) {
        throw new Exception("No department selected");
    }

    // ดึงข้อมูลคิวที่เรียกแล้วของแผนกนั้น ๆ วันนี้
    $sql = "
        SELECT 
            vn,
            hn,
            cur_dep,
            cur_time,
            name,
            called_time
        FROM queue_called 
        WHERE cur_dep = ?
        AND DATE(called_time) = CURDATE()
        ORDER BY called_time DESC
        LIMIT 20
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    

    $cur_dep = $selectedDepartment ;
    
    $stmt->bind_param("s", $cur_dep);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Query failed: " . $mysqli->error);
    }

    $queues = [];
    while ($row = $result->fetch_assoc()) {
        $queues[] = [
            'vn' => $row['vn'],
            'hn' => $row['hn'],
            'cur_dep' => $row['cur_dep'],
            'cur_time' => $row['cur_time'],
            'name' => $row['name'],
            'called_time' => $row['called_time']
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
    if (isset($dept_stmt)) {
        $dept_stmt->close();
    }
}
?>