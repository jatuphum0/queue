<?php
// call_queue.php - บันทึกการเรียกคิว
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8");

    // รับข้อมูลจาก POST
    $vn = isset($_POST['vn']) ? $_POST['vn'] : '';
    $hn = isset($_POST['hn']) ? $_POST['hn'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $cur_dep = isset($_POST['cur_dep']) ? $_POST['cur_dep'] : '';
    $oqueue = isset($_POST['oqueue']) ? $_POST['oqueue'] : 0;

    if (empty($vn) || empty($hn) || empty($name)) {
        throw new Exception("Missing required data");
    }


    // ตรวจสอบว่าเคยบันทึกแล้วหรือไม่
    $check_sql = "SELECT id FROM queue_called WHERE vn = ? AND DATE(called_time) = CURDATE()";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("s", $vn);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        throw new Exception("Queue already called today" . $vn);
    }

    $insert_sql = "
        INSERT INTO queue_called (vn, hn, cur_dep, cur_time, name, called_time) 
        VALUES (?, ?, ?, NOW(), ?, NOW())
    ";
    
    $insert_stmt = $mysqli->prepare($insert_sql);
    if (!$insert_stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    
    $insert_stmt->bind_param("ssss", $vn, $hn, $cur_dep, $name);
    
    if (!$insert_stmt->execute()) {
        throw new Exception("Execute failed: " . $insert_stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Queue called successfully',
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
    if (isset($dept_stmt)) {
        $dept_stmt->close();
    }
    if (isset($check_stmt)) {
        $check_stmt->close();
    }
    if (isset($insert_stmt)) {
        $insert_stmt->close();
    }
}
?>