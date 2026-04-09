<?php
// 1. Kuraho kwerekana amakosa kuri screen
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

require_once __DIR__ . '/db.php';

$body      = json_decode(file_get_contents('php://input'), true);
$requestId = intval($body['requestId'] ?? 0);
$adminId   = intval($body['adminId']   ?? 0);

if (!$requestId || !$adminId) {
    echo json_encode(['success' => false, 'message' => 'requestId na adminId bikenewe']); 
    exit();
}

// 1. Verify admin (Genzura niba ukora reject ari admin koko)
$adminCheck = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
$adminCheck->bind_param('i', $adminId);
$adminCheck->execute();
if ($adminCheck->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Nta burenganzira bwa Admin ufite']); 
    exit();
}
$adminCheck->close();

// 2. Fata izina ry'uwo ugiye kwanga (kugira ngo uze kuryerekana muri message)
$req = $conn->prepare("SELECT fullName FROM user_requests WHERE id = ? AND status = 'PENDING'");
$req->bind_param('i', $requestId);
$req->execute();
$row = $req->get_result()->fetch_assoc();
$req->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Ubusabe ntiyabonetse']); 
    exit();
}

// 3. Vugurura status ikaba 'REJECTED'
// Niba table yawe idafite reviewedBy, koresha iyi mivugururire yoroheje:
$upd = $conn->prepare("UPDATE user_requests SET status = 'REJECTED' WHERE id = ?");
$upd->bind_param('i', $requestId);

if ($upd->execute()) {
    echo json_encode(['success' => true, 'message' => "Ubusabe bwa {$row['fullName']} bwanze!"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Byahagaritse muri Database: ' . $conn->error]);
}

$conn->close();
?>