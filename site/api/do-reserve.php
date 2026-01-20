<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/reserva.php');
    exit;
}

// Não requer login - cliente pode fazer reserva sem conta
$quarto_id = $_POST['quarto_id'] ?? '';
$data_checkin = $_POST['data_checkin'] ?? '';
$data_checkout = $_POST['data_checkout'] ?? '';
$quantidade_hospedes = $_POST['quantidade_hospedes'] ?? 1;
$notas = $_POST['notas'] ?? '';

// Informações pessoais do cliente
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$cpf = $_POST['cpf'] ?? '';

// Validações
if (empty($quarto_id) || empty($data_checkin) || empty($data_checkout) || empty($nome) || empty($email) || empty($telefone) || empty($cpf)) {
    header('Location: ../pages/reserva.php?error=required');
    exit;
}

// Validar datas
$checkin = strtotime($data_checkin);
$checkout = strtotime($data_checkout);
if ($checkout <= $checkin) {
    header('Location: ../pages/reserva.php?error=datas');
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../pages/reserva.php?error=email');
    exit;
}

// Validar telefone (10-11 dígitos brasileiros)
$telefone_limpo = preg_replace('/\D/', '', $telefone);
if (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
    header('Location: ../pages/reserva.php?error=telefone');
    exit;
}

// Validar CPF (11 dígitos)
$cpf_limpo = preg_replace('/\D/', '', $cpf);
if (strlen($cpf_limpo) != 11 || !validarCPF($cpf_limpo)) {
    header('Location: ../pages/reserva.php?error=cpf');
    exit;
}

// Buscar quarto
$quarto = $db->fetch("SELECT * FROM quartos WHERE id = ?", [$quarto_id]);
if (!$quarto) {
    header('Location: ../pages/reserva.php?error=quarto');
    exit;
}

// Validar se as datas estão disponíveis
$datasCheckIn = $db->fetch(
    "SELECT COUNT(*) as total FROM disponibilidades WHERE quarto_id = ? AND data = ?",
    [$quarto_id, $data_checkin]
);
$datasCheckOut = $db->fetch(
    "SELECT COUNT(*) as total FROM disponibilidades WHERE quarto_id = ? AND data = ?",
    [$quarto_id, $data_checkout]
);

if ($datasCheckIn['total'] == 0 || $datasCheckOut['total'] == 0) {
    header('Location: ../pages/reserva.php?error=datas_indisponiveis');
    exit;
}

// Calcular valor total
$dias = ($checkout - $checkin) / (60 * 60 * 24);
$valor_total = $dias * $quarto['preco_diaria'];

// IMPORTANTE: Não criar reserva ainda!
// Armazenar dados na sessão para usar após pagamento confirmado
$_SESSION['dados_reserva'] = [
    'quarto_id' => $quarto_id,
    'nome_cliente' => $nome,
    'email_cliente' => $email,
    'telefone_cliente' => $telefone_limpo,
    'cpf_cliente' => $cpf_limpo,
    'data_checkin' => $data_checkin,
    'data_checkout' => $data_checkout,
    'quantidade_hospedes' => $quantidade_hospedes,
    'valor_total' => $valor_total,
    'notas' => $notas,
    'status' => 'pendente',
    'quarto_nome' => $quarto['nome'],
    'quarto_preco' => $quarto['preco_diaria']
];

// Redirecionar para pagamento (sem criar reserva no BD)
header('Location: ../pages/pagamento.php');
exit;

// Função para validar CPF
function validarCPF($cpf) {
    // Remover caracteres especiais
    $cpf = preg_replace('/\D/', '', $cpf);
    
    // Verificar se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verificar se não é uma sequência de números iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Calcular dígitos verificadores
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : (11 - $resto);
    
    if ($cpf[9] != $digito1) {
        return false;
    }
    
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : (11 - $resto);
    
    if ($cpf[10] != $digito2) {
        return false;
    }
    
    return true;
}
