<?php
// get_departments.php - ไฟล์สำหรับดึงรายชื่อแผนก
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

    // ดึงรายชื่อแผนกทั้งหมด
    $sql = "
        SELECT cur_dep , department 
        FROM view_ovst_doctor 
        WHERE department IS NOT NULL 
        AND department != ''
        group by cur_dep ORDER BY department 
    ";

    $result = $mysqli->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $mysqli->error);
    }

    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[$row['cur_dep']] = $row['department'];
    }

    echo json_encode([
        'success' => true,
        'departments' => $departments
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
}
?>