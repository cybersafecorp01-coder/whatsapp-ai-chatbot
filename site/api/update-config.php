<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

$user = $db->fetch("SELECT * FROM usuarios WHERE id = ? AND tipo = 'admin'", [$_SESSION['user_id']]);
if (!$user) {
    die('Acesso negado');
}

$nome_hotel = $_POST['nome_hotel'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$endereco = $_POST['endereco'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$taxa_servico = $_POST['taxa_servico'] ?? 0;

if (empty($nome_hotel) || empty($email)) {
    header('Location: ../admin/configuracoes.php?error=required');
    exit;
}

try {
    $db->update('configuracoes', [
        'nome_hotel' => $nome_hotel,
        'email' => $email,
        'telefone' => $telefone,
        'endereco' => $endereco,
        'descricao' => $descricao,
        'taxa_servico' => $taxa_servico
    ], ['id' => 1]);

    header('Location: ../admin/configuracoes.php?success=true');
} catch (Exception $e) {
    header('Location: ../admin/configuracoes.php?error=servidor');
}
exit;
