<?php
ob_start();
// ══════════════════════════════════════
//  clear_scanlogs.php
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

if ($conn->query('DELETE FROM scan_logs')) {
    ob_clean(); echo json_encode(['success' => true, 'message' => 'All scan logs cleared']);
} else {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Failed: ' . $conn->error]);
}
