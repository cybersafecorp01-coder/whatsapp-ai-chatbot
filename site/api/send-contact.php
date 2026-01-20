<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/contato.php');
    exit;
}

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$assunto = $_POST['assunto'] ?? '';
$mensagem = $_POST['mensagem'] ?? '';

if (empty($nome) || empty($email) || empty($mensagem)) {
    header('Location: ../pages/contato.php?error=required');
    exit;
}

try {
    // Salvar no banco
    $db->insert('mensagens', [
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'assunto' => $assunto,
        'mensagem' => $mensagem
    ]);

    // Enviar email via Resend API
    $resend_key = getenv('RESEND_API_KEY');
    if ($resend_key) {
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'from' => 'noreply@monã.com',
                'to' => 'admin@monã.com',
                'subject' => "Nova mensagem de contato: $assunto",
                'html' => "<h2>$nome</h2>
                          <p><strong>Email:</strong> $email</p>
                          <p><strong>Telefone:</strong> $telefone</p>
                          <p><strong>Mensagem:</strong></p>
                          <p>" . nl2br(htmlspecialchars($mensagem)) . "</p>"
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $resend_key,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    header('Location: ../pages/contato.php?success=true');
} catch (Exception $e) {
    header('Location: ../pages/contato.php?error=server');
}
exit;
