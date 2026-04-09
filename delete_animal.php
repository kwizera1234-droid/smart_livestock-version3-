<?php
ob_start();
// ══════════════════════════════════════
//  delete_animal.php
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

$data  = json_decode(file_get_contents('php://input'), true);
$tagId = trim($data['tagId'] ?? '');

if ($tagId === '') {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'tagId is required']); exit();
}

$check = $conn->prepare('SELECT tagId FROM animals WHERE tagId = ? LIMIT 1');
$check->bind_param('s', $tagId);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Animal not found: ' . $tagId]); exit();
}
$check->close();

$stmt = $conn->prepare('DELETE FROM animals WHERE tagId = ?');
$stmt->bind_param('s', $tagId);

if ($stmt->execute()) {
    ob_clean(); echo json_encode(['success' => true, 'message' => 'Animal deleted', 'tagId' => $tagId]);
} else {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $conn->error]);
}
$stmt->close();
