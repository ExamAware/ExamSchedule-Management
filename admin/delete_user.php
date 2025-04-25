<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}
$db = new SQLite3(__DIR__ . '/../data/exam.db');
$stmt = $db->prepare('DELETE FROM users WHERE id = :id AND username != "admin"');
$stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
$stmt->execute();
header('Location: manage_users.php');
exit;
