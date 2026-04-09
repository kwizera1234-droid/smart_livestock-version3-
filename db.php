<?php
// db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_livestock');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'DB Connection Failed']));
    }
    return $conn;
}

$conn = getDB(); // Iyi $conn niyo izakoreshwa mu zindi files