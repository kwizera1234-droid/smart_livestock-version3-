<?php
ob_start();
// ══════════════════════════════════════
//  mode.php — GET & POST register mode
// ══════════════════════════════════════

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $conn->query('SELECT registerMode FROM system_mode WHERE id = 1 LIMIT 1');
    if (!$result) {
        ob_clean(); echo json_encode(['success' => false, 'message' => 'Query failed']); exit();
    }
    $row = $result->fetch_assoc();
    ob_clean(); echo json_encode(['success' => true, 'registerMode' => (int)$row['registerMode']]); exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['registerMode'])) {
        ob_clean(); echo json_encode(['success' => false, 'message' => 'registerMode is required']); exit();
    }

    $mode = (int)$data['registerMode'];
    $stmt = $conn->prepare('UPDATE system_mode SET registerMode = ? WHERE id = 1');
    $stmt->bind_param('i', $mode);

    if ($stmt->execute()) {
        ob_clean(); echo json_encode(['success' => true, 'message' => 'Mode updated', 'registerMode' => $mode]);
    } else {
        ob_clean(); echo json_encode(['success' => false, 'message' => 'Failed: ' . $conn->error]);
    }
    $stmt->close(); exit();
}

ob_clean(); echo json_encode(['success' => false, 'message' => 'Method not allowed']);
