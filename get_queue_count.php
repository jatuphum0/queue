<?php
// get_queue_count.php - ดึงจำนวนคิวทั้งหมดของแผนก
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

    // นับจำนวนคิวทั้งหมดของแผนก
    $sql_total = "
        SELECT COUNT(*) as total_queue
        FROM view_ovst_doctor 
        WHERE cur_dep = ? 
        AND cur_dep_time IS NOT NULL
    ";

    // นับจำนวนคิวที่รอ
    $sql_waiting = "
        SELECT COUNT(*) as waiting_queue
        FROM view_ovst_doctor 
        WHERE cur_dep = ? 
        AND vn NOT IN (SELECT vn FROM queue_called WHERE DATE(cur_time) = CURDATE() AND cur_dep = ?)
        AND cur_dep_time IS NOT NULL
    ";

    // นับจำนวนคิวที่เรียกแล้ว
    $sql_called = "
        SELECT COUNT(*) as called_queue
        FROM queue_called 
        WHERE cur_dep = ?
        AND DATE(called_time) = CURDATE()
    ";

    // ดึงจำนวนคิวทั้งหมด
    $stmt_total = $mysqli->prepare($sql_total);
    $stmt_total->bind_param("s", $selectedDepartment);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_count = $result_total->fetch_assoc()['total_queue'];

    // ดึงจำนวนคิวที่รอ
    $stmt_waiting = $mysqli->prepare($sql_waiting);
    $stmt_waiting->bind_param("ss", $selectedDepartment, $selectedDepartment);
    $stmt_waiting->execute();
    $result_waiting = $stmt_waiting->get_result();
    $waiting_count = $result_waiting->fetch_assoc()['waiting_queue'];

    // ดึงจำนวนคิวที่เรียกแล้ว
    $stmt_called = $mysqli->prepare($sql_called);
    $stmt_called->bind_param("s", $selectedDepartment);
    $stmt_called->execute();
    $result_called = $stmt_called->get_result();
    $called_count = $result_called->fetch_assoc()['called_queue'];

    echo json_encode([
        'success' => true,
        'counts' => [
            'total' => (int)$total_count,
            'waiting' => (int)$waiting_count,
            'called' => (int)$called_count
        ],
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
    if (isset($stmt_total)) {
        $stmt_total->close();
    }
    if (isset($stmt_waiting)) {
        $stmt_waiting->close();
    }
    if (isset($stmt_called)) {
        $stmt_called->close();
    }
}
?>