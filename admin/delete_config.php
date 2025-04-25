<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}
$db = new SQLite3(__DIR__ . '/../data/exam.db');
$stmt = $db->prepare('DELETE FROM configs WHERE id = :id');
$stmt->bindValue(':id', $_GET['id'], SQLITE3_TEXT);
$stmt->execute();
header('Location: dashboard.php');
exit;
