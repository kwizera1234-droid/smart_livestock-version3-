<?php
ob_start();
// ══════════════════════════════════════
//  approve_user.php
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
// ✅ $conn ishoboka kuvaho db.php — ntakenewe getDB() ino

$body      = json_decode(file_get_contents('php://input'), true);
$requestId = intval($body['requestId'] ?? 0);
$adminId   = intval($body['adminId']   ?? 0);

if (!$requestId || !$adminId) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'requestId na adminId bikenewe']); exit();
}

// Verify admin
$adminCheck = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin'");
$adminCheck->bind_param('i', $adminId);
$adminCheck->execute();
if ($adminCheck->get_result()->num_rows === 0) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Nta burenganzira bwo kwemera requests']); exit();
}
$adminCheck->close();

// Reba request
$req = $conn->prepare(
    "SELECT fullName, username, email, phone, role, password
     FROM user_requests WHERE id = ? AND status = 'PENDING'"
);
$req->bind_param('i', $requestId);
$req->execute();
$row = $req->get_result()->fetch_assoc();
$req->close();

if (!$row) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Request ntiyabonetse cyangwa isanzwe yafashwe']); exit();
}

// Reba niba username isanzwe muri users
$dup = $conn->prepare('SELECT id FROM users WHERE username = ?');
$dup->bind_param('s', $row['username']);
$dup->execute();
if ($dup->get_result()->num_rows > 0) {
    ob_clean(); echo json_encode(['success' => false, 'message' => "Username '{$row['username']}' isanzwe ikoreshwa"]); exit();
}
$dup->close();

// ✅ Shyiramo muri users
// users.status ni lowercase enum: 'pending','approved','rejected'
$statusApproved = 'approved';
$ins = $conn->prepare(
    'INSERT INTO users (fullName, username, email, phone, password, role, status, createdAt)
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
);
$ins->bind_param(
    'sssssss',
    $row['fullName'],
    $row['username'],
    $row['email'],
    $row['phone'],
    $row['password'],
    $row['role'],
    $statusApproved
);

if (!$ins->execute()) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Kubika user byahagaritse: ' . $ins->error]); exit();
}
$ins->close();

// Vugurura user_requests → APPROVED
$upd = $conn->prepare(
    "UPDATE user_requests SET status = 'APPROVED', reviewedBy = ?, reviewedAt = NOW() WHERE id = ?"
);
$upd->bind_param('ii', $adminId, $requestId);
$upd->execute();
$upd->close();

ob_clean(); echo json_encode(['success' => true, 'message' => "Konti ya {$row['fullName']} yaremwe neza!"]);
