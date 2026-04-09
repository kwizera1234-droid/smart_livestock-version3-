<?php
ob_start();
// ══════════════════════════════════════
//  health.php — GET & POST health records
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

// ── GET: fetch health records for a tag ──
if ($method === 'GET') {
    if (empty($_GET['tagId'])) {
        ob_clean(); echo json_encode(['success' => false, 'message' => 'tagId required']); exit();
    }

    $tagId = trim($_GET['tagId']);

    // Get animal name
    $aStmt = $conn->prepare('SELECT name FROM animals WHERE tagId = ? LIMIT 1');
    $aStmt->bind_param('s', $tagId);
    $aStmt->execute();
    $aRow       = $aStmt->get_result()->fetch_assoc();
    $animalName = $aRow ? $aRow['name'] : $tagId;
    $aStmt->close();

    $stmt = $conn->prepare(
        'SELECT id, type, startDate, endDate, nextEventDate, notes, vetName, vetContact, createdAt
         FROM health_records WHERE tagId = ? ORDER BY createdAt DESC'
    );
    $stmt->bind_param('s', $tagId);
    $stmt->execute();

    $records = [];
    $res     = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $records[] = $row; }
    $stmt->close();

    ob_clean(); echo json_encode([
        'success'      => true,
        'tagId'        => $tagId,
        'animalName'   => $animalName,
        'totalRecords' => count($records),
        'records'      => $records
    ]); exit();
}

// ── POST: add health record ──
if ($method === 'POST') {
    $data  = json_decode(file_get_contents('php://input'), true);
    $tagId = trim($data['tagId'] ?? '');
    $type  = trim($data['type']  ?? '');

    if (!$tagId || !$type) {
        ob_clean(); echo json_encode(['success' => false, 'message' => 'tagId and type are required']); exit();
    }

    $startDate     = $data['startDate']     ?? null;
    $endDate       = $data['endDate']       ?? null;
    $nextEventDate = $data['nextEventDate'] ?? null;
    $notes         = $data['notes']         ?? null;
    $vetName       = $data['vetName']       ?? null;
    $vetContact    = $data['vetContact']    ?? null;

    $stmt = $conn->prepare(
        'INSERT INTO health_records (tagId, type, startDate, endDate, nextEventDate, notes, vetName, vetContact)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssssss', $tagId, $type, $startDate, $endDate, $nextEventDate, $notes, $vetName, $vetContact);

    if ($stmt->execute()) {
        ob_clean(); echo json_encode(['success' => true, 'message' => 'Health record added', 'id' => $conn->insert_id]);
    } else {
        ob_clean(); echo json_encode(['success' => false, 'message' => 'Failed: ' . $conn->error]);
    }
    $stmt->close(); exit();
}

ob_clean(); echo json_encode(['success' => false, 'message' => 'Method not allowed']);
