<?php
ob_start();
// ══════════════════════════════════════
//  delete_scanlog.php — Delete single scan log
// ══════════════════════════════════════

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit();
}

require_once __DIR__ . '/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id   = intval($data['id'] ?? 0);

if ($id <= 0) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Valid id is required']); exit();
}

$check = $conn->prepare('SELECT id FROM scan_logs WHERE id = ? LIMIT 1');
$check->bind_param('i', $id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Log not found (id=' . $id . ')']); exit();
}
$check->close();

$stmt = $conn->prepare('DELETE FROM scan_logs WHERE id = ?');
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    ob_clean(); echo json_encode(['success' => true, 'message' => 'Scan log deleted', 'id' => $id]);
} else {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $conn->error]);
}
$stmt->close();
