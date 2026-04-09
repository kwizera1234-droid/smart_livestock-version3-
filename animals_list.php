<?php
ob_start();
// ══════════════════════════════════════
//  animals_list.php
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
    'SELECT tagId, name, animalType, sex, breed, birthdate,
            isPregnant, isSick, ownerContact, ownerName, createdAt
     FROM animals
     ORDER BY createdAt DESC'
);

if (!$result) {
    http_response_code(500);
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]); exit();
}

$animals = [];
while ($row = $result->fetch_assoc()) {
    $row['isPregnant'] = (int)$row['isPregnant'];
    $row['isSick']     = (int)$row['isSick'];
    $animals[]         = $row;
}

ob_clean(); echo json_encode(['success' => true, 'total' => count($animals), 'animals' => $animals]);
