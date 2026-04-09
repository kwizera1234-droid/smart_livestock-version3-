<?php
ob_start();
// ══════════════════════════════════════
//  animal.php — Scan tag, get animal info
// ══════════════════════════════════════

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . '/db.php';

if (!isset($_GET['tagId']) || trim($_GET['tagId']) === '') {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'tagId is required']); exit();
}

$tagId = trim($_GET['tagId']);

// Fetch animal profile
$stmt = $conn->prepare('SELECT * FROM animals WHERE tagId = ? LIMIT 1');
$stmt->bind_param('s', $tagId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Log: NOT_FOUND
    $log = $conn->prepare("INSERT INTO scan_logs (tagId, status) VALUES (?, 'NOT_FOUND')");
    $log->bind_param('s', $tagId);
    $log->execute();
    $log->close();

    ob_clean(); echo json_encode(['success' => false, 'message' => 'Tag not registered', 'tagId' => $tagId]); exit();
}

$animal = $result->fetch_assoc();
$stmt->close();

// Fetch latest health record
$hStmt = $conn->prepare(
    'SELECT type, startDate, endDate, nextEventDate, notes, vetName, vetContact, createdAt
     FROM health_records WHERE tagId = ? ORDER BY createdAt DESC LIMIT 1'
);
$hStmt->bind_param('s', $tagId);
$hStmt->execute();
$hResult     = $hStmt->get_result();
$latestHealth = $hResult->num_rows > 0 ? $hResult->fetch_assoc() : null;
$hStmt->close();

// Log: FOUND
$log = $conn->prepare("INSERT INTO scan_logs (tagId, status) VALUES (?, 'FOUND')");
$log->bind_param('s', $tagId);
$log->execute();
$log->close();

ob_clean(); echo json_encode([
    'success'      => true,
    'tagId'        => $animal['tagId'],
    'name'         => $animal['name'],
    'animalType'   => $animal['animalType'],
    'sex'          => $animal['sex'],
    'breed'        => $animal['breed'],
    'birthdate'    => $animal['birthdate'],
    'isPregnant'   => (int)$animal['isPregnant'],
    'isSick'       => (int)$animal['isSick'],
    'ownerContact' => $animal['ownerContact'],
    'ownerName'    => $animal['ownerName'],
    'latestHealth' => $latestHealth
]);
