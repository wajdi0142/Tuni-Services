<?php
require '../db/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE status = 'en-attente'");
$result = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode(['count' => $result['count']]);
?>