<?php
ob_start();
// Fungura amakosa by'agateganyo (Debug)
error_reporting(E_ALL);
ini_set('display_errors', 1); 

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/db.php';

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

// Niba data ari null, bivuze ko JSON yaje nabi
if (!$data) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
if ($username === '' || $password === '') {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Username and password required']); exit();
}

$stmt = $conn->prepare('SELECT id, fullName, username, role, password, status FROM users WHERE username = ? LIMIT 1');
if (!$stmt) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Server error']); exit();
}

$stmt->bind_param('s', $username);
$stmt->execute();
$res  = $stmt->get_result();

if ($res->num_rows === 0) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Invalid username or password']); exit();
}

$user = $res->fetch_assoc();
$stmt->close();

// ✅ users.status ni lowercase enum: 'pending','approved','rejected'
if ($user['status'] !== 'approved') {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Account not approved yet']); exit();
}

if (!password_verify($password, $user['password'])) {
    ob_clean(); echo json_encode(['success' => false, 'message' => 'Invalid username or password']); exit();
}

ob_clean(); echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user'    => [
        'id'       => (int)$user['id'],
        'fullName' => $user['fullName'],
        'username' => $user['username'],
        'role'     => $user['role']
    ]
]);
