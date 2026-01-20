<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    header('Location: ../pages/login.php?error=required');
    exit;
}

$user = $db->fetch("SELECT * FROM usuarios WHERE email = ?", [$email]);

if (!$user || !password_verify($senha, $user['senha'])) {
    header('Location: ../pages/login.php?error=invalid');
    exit;
}

if (!$user['ativo']) {
    header('Location: ../pages/login.php?error=inactive');
    exit;
}

// Iniciar sessão
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_nome'] = $user['nome'];
$_SESSION['user_tipo'] = $user['tipo'];

// Definir cookie se "lembrar" foi marcado
if (isset($_POST['lembrar'])) {
    setcookie('user_id', $user['id'], time() + (30 * 24 * 60 * 60), '/');
}

// Redirecionar baseado no tipo
if ($user['tipo'] === 'admin') {
    header('Location: ../admin/');
} else {
    header('Location: ../pages/home.php');
}
exit;
