<?php
// ═══════════════════════════════════════════════════
//  register.php — NFC tag registration (FIXED)
// ═══════════════════════════════════════════════════

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    http_response_code(200); 
    exit(); 
}

require_once __DIR__ . '/db.php';

// Check database connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB Connection failed: ' . $conn->connect_error]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// ── POST: Save tag DIRECTLY into animals table ──
if ($method === 'POST') {
    $data  = json_decode(file_get_contents('php://input'), true);
    $tagId = trim($data['tagId'] ?? '');

    if ($tagId === '') {
        echo json_encode(['success' => false, 'message' => 'tagId required']); 
        exit();
    }

    // Check register mode
    $modeResult = $conn->query('SELECT registerMode FROM system_mode WHERE id = 1 LIMIT 1');
    $modeRow    = $modeResult ? $modeResult->fetch_assoc() : null;

    if (!$modeRow || (int)$modeRow['registerMode'] !== 1) {
        echo json_encode(['success' => false, 'message' => 'Register mode is OFF']); 
        exit();
    }

    // 🔥 HINDUWE: Kugenzura niba Tag isanzwe irimo (ukoresheje tagId)
    $check = $conn->prepare('SELECT tagId FROM animals WHERE tagId = ? LIMIT 1');
    $check->bind_param('s', $tagId);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Tag already exists',
            'tagId' => $tagId
        ]); 
        exit();
    }
    $check->close();

    // 🔥 KWINJIZA MURI DATABASE (Hakoreshejwe tagId nka Primary Key)
    $stmt = $conn->prepare('INSERT INTO animals (tagId, name, animalType, sex, ownerContact, isSick, createdAt) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $defaultName = 'New Animal';
    $defaultType = 'Unknown';
    $defaultSex = 'Unknown';
    $defaultContact = '0000000000';
    $defaultSick = 0;
    
    $stmt->bind_param('sssssi', $tagId, $defaultName, $defaultType, $defaultSex, $defaultContact, $defaultSick);
    
    if ($stmt->execute()) {
        // Insert into pending_tags for Dashboard polling
        $pending = $conn->prepare('INSERT INTO pending_tags (tagId) VALUES (?)');
        $pending->bind_param('s', $tagId);
        $pending->execute();
        $pending->close();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Tag registered successfully',
            'tagId' => $tagId
        ]); 
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $conn->error
        ]); 
    }
    
    $stmt->close();
    exit();
}

// ── GET: fetch latest pending tag (for dashboard polling) ──
if ($method === 'GET') {
    if (($_GET['action'] ?? '') !== 'fetch') {
        echo json_encode(['success' => false, 'message' => 'Invalid action']); 
        exit();
    }

    $result = $conn->query('SELECT tagId FROM pending_tags ORDER BY createdAt DESC LIMIT 1');
    if ($result && $row = $result->fetch_assoc()) {
        $tagId = $row['tagId'];

        // Delete from pending_tags
        $del = $conn->prepare('DELETE FROM pending_tags WHERE tagId = ?');
        $del->bind_param('s', $tagId);
        $del->execute();
        $del->close();

        echo json_encode(['success' => true, 'tagId' => $tagId]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Method not allowed']);