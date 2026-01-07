<?php
session_start();
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$name = trim((string)($_POST['name'] ?? ''));
$googleId = trim((string)($_POST['google_id'] ?? ''));

if (!$email || $googleId === '') {
    http_response_code(400);
    echo 'Missing required fields';
    exit;
}

$pdo = getPdo();

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    $countStmt = $pdo->query('SELECT COUNT(*) AS total FROM users');
    $totalUsers = (int)$countStmt->fetchColumn();
    $isAdmin = $totalUsers === 0 ? 1 : 0;

    $insert = $pdo->prepare(
        'INSERT INTO users (email, name, google_id, is_admin) VALUES (:email, :name, :google_id, :is_admin)'
    );
    $insert->execute([
        'email' => $email,
        'name' => $name ?: null,
        'google_id' => $googleId,
        'is_admin' => $isAdmin,
    ]);
}

$_SESSION['user_email'] = $email;
header('Location: ../admin.php');
exit;

