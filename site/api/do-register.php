<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/registro.php');
    exit;
}

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$cpf = $_POST['cpf'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$senha = $_POST['senha'] ?? '';
$confirma_senha = $_POST['confirma_senha'] ?? '';

// Validações
if (empty($nome) || empty($email) || empty($senha) || empty($cpf)) {
    header('Location: ../pages/registro.php?error=required');
    exit;
}

if ($senha !== $confirma_senha) {
    header('Location: ../pages/registro.php?error=senhas');
    exit;
}

if (strlen($senha) < 6) {
    header('Location: ../pages/registro.php?error=senha_curta');
    exit;
}

// Verificar se email já existe
$existe = $db->fetch("SELECT id FROM usuarios WHERE email = ?", [$email]);
if ($existe) {
    header('Location: ../pages/registro.php?error=exists');
    exit;
}

// Hash da senha
$senha_hash = password_hash($senha, PASSWORD_BCRYPT);

// Limpar CPF (remover caracteres especiais)
$cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);

try {
    $db->insert('usuarios', [
        'nome' => $nome,
        'email' => $email,
        'cpf' => $cpf_limpo,
        'telefone' => $telefone,
        'senha' => $senha_hash,
        'tipo' => 'cliente'
    ]);

    // Auto-login
    $user = $db->fetch("SELECT id FROM usuarios WHERE email = ?", [$email]);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nome'] = $nome;
    $_SESSION['user_tipo'] = 'cliente';

    header('Location: ../pages/home.php?success=cadastro');
} catch (Exception $e) {
    header('Location: ../pages/registro.php?error=invalid');
}
exit;
