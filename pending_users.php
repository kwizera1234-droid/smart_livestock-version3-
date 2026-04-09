<?php
ob_start();
// ══════════════════════════════════════
//  pending_users.php
// ══════════════════════════════════════

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit();
}

require_once __DIR__ . '/db.php';

$result = $conn->query(
    "SELECT id, fullName, username, email, phone, role, reason, createdAt
     FROM user_requests
     WHERE status = 'PENDING'
     ORDER BY createdAt DESC"
);

if (!$result) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]); exit();
}

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

ob_clean(); echo json_encode(['success' => true, 'requests' => $requests]);
