<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !preg_match('/^[a-zA-Z0-9_\-]+$/', $_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => '参数错误']);
    exit;
}
$configId = $_GET['id'];

$db = new SQLite3(__DIR__ . '/../data/exam.db');
$stmt = $db->prepare('SELECT content FROM configs WHERE id = :id');
$stmt->bindValue(':id', $configId, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => '未找到该配置']);
    exit;
}

echo $row['content'];
