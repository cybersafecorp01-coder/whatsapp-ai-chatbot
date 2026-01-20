<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/payment.php';
require_once '../includes/email.php';
require_once '../includes/whatsapp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/home.php');
    exit;
}

// Verificar se há dados de reserva na sessão
if (!isset($_SESSION['dados_reserva'])) {
    header('Location: ../pages/reserva.php?error=sessao');
    exit;
}

$titular_nome = $_POST['titular_nome'] ?? '';
$cartao_numero = preg_replace('/\D/', '', $_POST['cartao_numero'] ?? '');
$cartao_validade = $_POST['cartao_validade'] ?? '';
$cartao_cvv = $_POST['cartao_cvv'] ?? '';

// Validações básicas
if (empty($cartao_numero) || empty($cartao_cvv) || empty($titular_nome)) {
    header('Location: ../pages/pagamento.php?error=required');
    exit;
}

// Dados da reserva armazenados na sessão
$dados_reserva = $_SESSION['dados_reserva'];

// Processar pagamento via ASAAS
$payment = new AsaasPayment();
$email_sender = new ResendEmail();
$whatsapp_sender = new WhatsAppMessage();

$dados_pagamento = [
    'email' => $dados_reserva['email_cliente'],
    'nome' => $dados_reserva['nome_cliente'],
    'cpf' => $dados_reserva['cpf_cliente'],
    'valor' => $dados_reserva['valor_total'],
    'data_vencimento' => date('Y-m-d'),
    'descricao' => 'Reserva de hospedagem - Quarto #' . $dados_reserva['quarto_id'],
    'cartao' => [
        'holderName' => $titular_nome,
        'number' => $cartao_numero,
        'expiryMonth' => substr($cartao_validade, 0, 2),
        'expiryYear' => '20' . substr($cartao_validade, 3, 2),
        'ccv' => $cartao_cvv
    ],
    'titular' => [
        'name' => $titular_nome,
        'cpf' => $dados_reserva['cpf_cliente']
    ]
];

$resultado = $payment->criarCobranca($dados_pagamento);

if ($resultado['success']) {
    // PAGAMENTO CONFIRMADO - Criar a reserva no banco de dados AGORA
    try {
        $db->insert('reservas', [
            'quarto_id' => $dados_reserva['quarto_id'],
            'nome_cliente' => $dados_reserva['nome_cliente'],
            'email_cliente' => $dados_reserva['email_cliente'],
            'telefone_cliente' => $dados_reserva['telefone_cliente'],
            'cpf_cliente' => $dados_reserva['cpf_cliente'],
            'data_checkin' => $dados_reserva['data_checkin'],
            'data_checkout' => $dados_reserva['data_checkout'],
            'quantidade_hospedes' => $dados_reserva['quantidade_hospedes'],
            'valor_total' => $dados_reserva['valor_total'],
            'notas' => $dados_reserva['notas'],
            'status' => 'confirmada',
            'metodo_pagamento' => 'asaas',
            'referencia_pagamento' => $resultado['data']['id'] ?? null
        ]);

        $reserva_id = $db->lastInsertId();

        // Buscar dados completos da reserva criada
        $reserva_criada = $db->fetch("
            SELECT r.*, q.nome as quarto_nome, q.preco_diaria 
            FROM reservas r 
            JOIN quartos q ON r.quarto_id = q.id 
            WHERE r.id = ?
        ", [$reserva_id]);

        // Enviar email de confirmação
        $email_sender->enviarConfirmacaoReserva(
            $reserva_criada['email_cliente'],
            $reserva_criada['nome_cliente'],
            $reserva_criada['data_checkin'],
            $reserva_criada['data_checkout'],
            $reserva_criada['quarto_nome'],
            $reserva_criada['valor_total'],
            $reserva_criada['id']
        );

        // Enviar mensagem WhatsApp de confirmação
        $dados_whatsapp = array_merge($reserva_criada, ['id' => $reserva_id]);
        $whatsapp_sender->enviarConfirmacaoReserva(
            $reserva_criada['telefone_cliente'],
            $dados_whatsapp
        );

        // Limpar dados da sessão
        unset($_SESSION['dados_reserva']);

        // Redirecionar para página de sucesso
        header('Location: ../pages/sucesso.php?reserva_id=' . $reserva_id);
        exit;

    } catch (Exception $e) {
        // Erro ao criar reserva no BD, mas pagamento foi confirmado
        // Log do erro para análise
        error_log('Erro ao criar reserva no BD após pagamento confirmado: ' . $e->getMessage());
        
        header('Location: ../pages/home.php?error=database&reserva_id=' . ($reserva_id ?? 'unknown'));
        exit;
    }
} else {
    // Pagamento falhou - NÃO criar reserva
    error_log('Pagamento falhou para cliente ' . $dados_reserva['email_cliente'] . ': ' . ($resultado['error'] ?? 'Erro desconhecido'));
    
    header('Location: ../pages/pagamento.php?error=payment_failed&msg=' . urlencode($resultado['error'] ?? 'Falha no processamento'));
    exit;
}
