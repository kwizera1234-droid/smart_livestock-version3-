<?php
ob_start();
// ══════════════════════════════════════
//  save_animal.php — Add or Update animal
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

foreach (['tagId', 'name', 'animalType', 'sex', 'ownerContact'] as $field) {
    if (empty($data[$field])) {
        ob_clean(); echo json_encode(['success' => false, 'message' => "$field is required"]); exit();
    }
}

$tagId        = trim($data['tagId']);
$name         = trim($data['name']);
$animalType   = trim($data['animalType']);
$sex          = trim($data['sex']);
$breed        = isset($data['breed'])     ? trim($data['breed'])     : null;
$birthdate    = isset($data['birthdate']) ? $data['birthdate']       : null;
$isPregnant   = isset($data['isPregnant']) ? (int)$data['isPregnant'] : 0;
$isSick       = isset($data['isSick'])     ? (int)$data['isSick']     : 0;
$ownerContact = trim($data['ownerContact']);
$ownerName    = isset($data['ownerName']) ? trim($data['ownerName']) : null;

$stmt = $conn->prepare(
    'INSERT INTO animals (tagId, name, animalType, sex, breed, birthdate, isPregnant, isSick, ownerContact, ownerName)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
         name         = VALUES(name),
         animalType   = VALUES(animalType),
         sex          = VALUES(sex),
         breed        = VALUES(breed),
         birthdate    = VALUES(birthdate),
         isPregnant   = VALUES(isPregnant),
         isSick       = VALUES(isSick),
         ownerContact = VALUES(ownerContact),
         ownerName    = VALUES(ownerName)'
);

$stmt->bind_param('ssssssiiss',
    $tagId, $name, $animalType, $sex, $breed,
    $birthdate, $isPregnant, $isSick, $ownerContact, $ownerName
);

if ($stmt->execute()) {
    ob_clean(); echo json_encode(['success' => true, 'message' => 'Animal saved', 'tagId' => $tagId]);
} else {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Failed: ' . $conn->error]);
}
$stmt->close();
