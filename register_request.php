<?php
// 1. Kuraho kwerekana amakosa kuri screen kuko byica JSON
error_reporting(0); 
ini_set('display_errors', 0);

// 2. Headers zigomba kuza mbere y'ikindi kintu cyose
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    http_response_code(200); 
    exit(); 
}

// Gufata amakuru yaje (JSON Input)
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

// 3. Guhuza na Database
require_once __DIR__ . '/db.php';

// Reba niba connection ya $conn ihari koko
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request or empty data']); 
    exit();
}

// Gukusanya amakuru
$fullName = trim($data['fullName'] ?? '');
$username = trim($data['username'] ?? '');
$email    = trim($data['email']    ?? '');
$phone    = trim($data['phone']    ?? '');
$role     = trim($data['role']     ?? 'owner');
$password = $data['password']      ?? '';
$reason   = trim($data['reason']   ?? '');

// 1. Validate required fields
if (!$fullName || !$username || !$email || !$phone || !$password) {
    echo json_encode(['success' => false, 'message' => 'Uzuza imyanya yose ikenewe']); 
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password igenerwa imibare 6 cyangwa irenga']); 
    exit();
}

// 2. Hash password
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// 3. Reba niba username/email isanzwe muri table ya users
$check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$check->bind_param('ss', $username, $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username cyangwa Email isanzwe ikoreshwa']); 
    exit();
}
$check->close();

// 4. Reba niba request isanzwe iri PENDING
$check2 = $conn->prepare('SELECT id FROM user_requests WHERE (username = ? OR email = ?) AND status = "PENDING" LIMIT 1');
$check2->bind_param('ss', $username, $email);
$check2->execute();
if ($check2->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Ubusabe bwawe buracyategereje kwemezwa (Pending)']); 
    exit();
}
$check2->close();

// 5. Shyiramo ubusabe bushya
$stmt = $conn->prepare(
    'INSERT INTO user_requests (fullName, username, email, phone, role, password, reason, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, "PENDING")'
);

$stmt->bind_param('sssssss', $fullName, $username, $email, $phone, $role, $passwordHash, $reason);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Ubusabe bwoherejwe! Tegereza admin abwemeze.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>