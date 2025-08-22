<?php
// remove_called_queue.php - ลบรายการที่เรียกแล้วออกจาก queue_called
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8");

    $vn = isset($_POST['vn']) ? $_POST['vn'] : '';
    $hn = isset($_POST['hn']) ? $_POST['hn'] : '';
    $cur_dep = isset($_POST['cur_dep']) ? $_POST['cur_dep'] : '';
    
    if (empty($vn) || empty($hn) || empty($cur_dep)) {
        throw new Exception("VN, HN and department are required");
    }

    // ลบรายการจาก queue_called
    $sql = "DELETE FROM queue_called WHERE vn = ? AND hn = ? AND cur_dep = ? AND DATE(called_time) = CURDATE()";
    
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    
    $stmt->bind_param("sss", $vn, $hn, $cur_dep);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $mysqli->error);
    }

    $affected_rows = $stmt->affected_rows;
    
    if ($affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'ลบรายการเรียบร้อยแล้ว',
            'affected_rows' => $affected_rows,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบรายการที่ต้องการลบ',
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    }

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