<?php
// search_queue.php - ค้นหาคิวจากฐานข้อมูล
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8");

    $selectedDepartment = isset($_POST['department']) ? $_POST['department'] : '';
    $searchTerm = isset($_POST['search']) ? trim($_POST['search']) : '';
    
    if (empty($selectedDepartment) || empty($searchTerm)) {
        throw new Exception("Department and search term are required");
    }

    // ค้นหาลำดับคิวทั้งหมดของแผนก (แยกตามหมอ)
    $sql_all_waiting = "
        SELECT vn, cur_dep_time, doctor, doctor_name
        FROM view_ovst_doctor 
        WHERE cur_dep = ? 
        AND vn NOT IN (SELECT vn FROM queue_called WHERE DATE(cur_time) = CURDATE() AND cur_dep = ?)
        AND cur_dep_time IS NOT NULL
        ORDER BY cur_dep_time ASC
    ";

    // ค้นหาในคิวรอ
    $sql_waiting = "
        SELECT 
            'waiting' as status,
            vn, hn, oqueue, name, cur_dep_time, doctor, doctor_name
        FROM view_ovst_doctor 
        WHERE cur_dep = ? 
        AND vn NOT IN (SELECT vn FROM queue_called WHERE DATE(cur_time) = CURDATE() AND cur_dep = ?)
        AND cur_dep_time IS NOT NULL
        AND (name LIKE ? OR hn LIKE ?)
        ORDER BY cur_dep_time ASC
    ";

    // ค้นหาในคิวที่เรียกแล้ว
    $sql_called = "
        SELECT 
            'called' as status,
            qc.vn, qc.hn, NULL as oqueue, qc.name, NULL as cur_dep_time, 
            NULL as doctor, NULL as doctor_name, qc.called_time
        FROM queue_called qc
        WHERE qc.cur_dep = ?
        AND DATE(qc.called_time) = CURDATE()
        AND (qc.name LIKE ? OR qc.hn LIKE ?)
        ORDER BY qc.called_time DESC
    ";

    $searchPattern = "%{$searchTerm}%";
    $results = [];

    // ดึงข้อมูลคิวทั้งหมดเพื่อหาลำดับที่ถูกต้อง (แยกตามหมอ)
    $stmt_all = $mysqli->prepare($sql_all_waiting);
    $stmt_all->bind_param("ss", $selectedDepartment, $selectedDepartment);
    $stmt_all->execute();
    $result_all = $stmt_all->get_result();
    
    $all_queue_vns = [];
    $doctor_positions = [];
    $overall_position = 1;
    
    while ($row = $result_all->fetch_assoc()) {
        $doctor_key = $row['doctor'] ?? 'no_doctor';
        
        // นับลำดับรวมของแผนก
        $all_queue_vns[$row['vn']] = [
            'overall_position' => $overall_position++,
            'doctor' => $row['doctor'],
            'doctor_name' => $row['doctor_name']
        ];
        
        // นับลำดับแยกตามหมอ
        if (!isset($doctor_positions[$doctor_key])) {
            $doctor_positions[$doctor_key] = 0;
        }
        $doctor_positions[$doctor_key]++;
        $all_queue_vns[$row['vn']]['doctor_position'] = $doctor_positions[$doctor_key];
    }

    // ค้นหาในคิวรอ
    $stmt_waiting = $mysqli->prepare($sql_waiting);
    $stmt_waiting->bind_param("ssss", $selectedDepartment, $selectedDepartment, $searchPattern, $searchPattern);
    $stmt_waiting->execute();
    $result_waiting = $stmt_waiting->get_result();
    
    while ($row = $result_waiting->fetch_assoc()) {
        $queue_info = isset($all_queue_vns[$row['vn']]) ? $all_queue_vns[$row['vn']] : null;
        
        $overall_position = $queue_info ? $queue_info['overall_position'] : '?';
        $doctor_position = $queue_info ? $queue_info['doctor_position'] : '?';
        
        $results[] = [
            'status' => 'waiting',
            'vn' => $row['vn'],
            'hn' => $row['hn'],
            'name' => $row['name'],
            'position' => $overall_position,
            'doctor_position' => $doctor_position,
            'time' => date('H:i', strtotime($row['cur_dep_time'])),
            'doctor' => $row['doctor'],
            'doctor_name' => $row['doctor_name']
        ];
    }

    // ค้นหาในคิวที่เรียกแล้ว
    $stmt_called = $mysqli->prepare($sql_called);
    $stmt_called->bind_param("sss", $selectedDepartment, $searchPattern, $searchPattern);
    $stmt_called->execute();
    $result_called = $stmt_called->get_result();
    
    while ($row = $result_called->fetch_assoc()) {
        $results[] = [
            'status' => 'called',
            'vn' => $row['vn'],
            'hn' => $row['hn'],
            'name' => $row['name'],
            'position' => null,
            'time' => date('H:i d/m/Y', strtotime($row['called_time'])),
            'doctor' => null,
            'doctor_name' => null
        ];
    }

    echo json_encode([
        'success' => true,
        'results' => $results,
        'search_term' => $searchTerm,
        'department' => $selectedDepartment,
        'total_found' => count($results),
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
    if (isset($stmt_waiting)) {
        $stmt_waiting->close();
    }
    if (isset($stmt_all)) {
        $stmt_all->close();
    }
    if (isset($stmt_called)) {
        $stmt_called->close();
    }
}
?>