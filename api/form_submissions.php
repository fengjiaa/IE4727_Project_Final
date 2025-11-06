<?php
header('Content-Type: application/json');

// Database config
$host = 'localhost';
$db   = 'miramoo';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'DB Connection Failed: ' . $e->getMessage()]);
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $msg = trim($_POST['msg'] ?? '');

    if ($type === 'contact' && $name && $email && $msg) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $msg]);
            echo json_encode(['ok' => true]);
        } catch (PDOException $e) {
            echo json_encode(['ok' => false, 'error' => 'Insert failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['ok' => false, 'error' => 'Please fill all fields']);
    }
} else {
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
}
