<?php
ob_start();
// ══════════════════════════════════════
//  scan_log.php — Get recent scan logs
// ══════════════════════════════════════

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . '/db.php';

$result = $conn->query(
    'SELECT s.id, s.tagId, s.scannedAt, s.status, a.name, a.animalType
     FROM scan_logs s
     LEFT JOIN animals a ON s.tagId = a.tagId
     ORDER BY s.scannedAt DESC
     LIMIT 100'
);

if (!$result) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]); exit();
}

$logs = [];
while ($row = $result->fetch_assoc()) { $logs[] = $row; }

ob_clean(); echo json_encode(['success' => true, 'logs' => $logs]);
