<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $password2 = isset($_POST['password']) ? $_POST['password'] : '';

    try {
        $mysqli = new mysqli($servername, $username, $password, $dbname);
        
        
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }

        $mysqli->set_charset("utf8");

        // ตรวจสอบรหัสแผนกว่าตรงกับที่ส่งมาหรือไม่
        $sql = "SELECT cur_dep, department FROM view_ovst_queue2 WHERE cur_dep = ? GROUP BY cur_dep";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($password2 === $staff_password) { // รหัสผ่านคือรหัสแผนก
                $_SESSION['department'] = $department;
                $_SESSION['department_name'] = $row['department'];
                echo json_encode([
                    'success' => true,
                    'message' => 'เข้าสู่ระบบสำเร็จ'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'รหัสผ่านไม่ถูกต้อง'
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ไม่พบแผนกที่ระบุ ?'
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
    }
}
?>