<?php
/**
 * Classe para integração com Resend API
 * Envio de emails
 */

class ResendEmail {
    private $apiKey;
    private $apiUrl = 'https://api.resend.com/emails';
    private $fromEmail = 'noreply@monã.com';

    public function __construct() {
        $this->apiKey = getenv('RESEND_API_KEY');
    }

    /**
     * Enviar email de confirmação de reserva
     */
    public function enviarConfirmacaoReserva($email_cliente, $nome_cliente, $data_checkin, $data_checkout, $quarto_nome, $valor_total, $reserva_id) {
        $dataCheckin = date('d/m/Y', strtotime($data_checkin));
        $dataCheckout = date('d/m/Y', strtotime($data_checkout));
        $valor = number_format($valor_total, 2, ',', '.');

        $html = "
            <h2 style='color: #1a3a2f;'>✅ Confirmação de Reserva</h2>
            <p>Olá $nome_cliente,</p>
            <p>Sua reserva foi confirmada com sucesso! Aqui estão os detalhes:</p>
            
            <h3 style='color: #1a3a2f; margin-top: 20px;'>Detalhes da Reserva</h3>
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <tr style='background: #f5f5f5;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Número da Reserva</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>#$reserva_id</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Quarto</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$quarto_nome</td>
                </tr>
                <tr style='background: #f5f5f5;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Check-in</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$dataCheckin (14:00)</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Check-out</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$dataCheckout (11:00)</td>
                </tr>
                <tr style='background: #e8f5e9;'>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>Valor Total</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd; color: #27ae60; font-weight: bold;'>R$ $valor</td>
                </tr>
            </table>

            <h3 style='color: #1a3a2f;'>Informações Importantes</h3>
            <ul>
                <li>Horário de check-in: 14:00</li>
                <li>Horário de check-out: 11:00</li>
                <li>Chegue com 15 minutos de antecedência</li>
                <li>Leve um documento de identidade</li>
            </ul>

            <p style='margin-top: 30px; color: #666;'>
                Se tiver dúvidas, entre em contato conosco:<br>
                📞 <strong>" . getenv('HOTEL_PHONE') . "</strong><br>
                📧 <strong>" . getenv('HOTEL_EMAIL') . "</strong>
            </p>

            <p style='margin-top: 30px; color: #999; font-size: 12px;'>
                Obrigado por escolher o Monã Hotel! 🌟
            </p>
        ";

        return $this->enviar(
            $email_cliente,
            'Confirmação de Reserva #' . $reserva_id . ' - Monã Hotel',
            $html
        );
    }

    /**
     * Enviar email de recuperação de senha
     */
    public function enviarRecuperacaoSenha($email, $token) {
        $link = getenv('APP_URL') . '/reset-password.php?token=' . $token;
        $html = "
            <h2>Recuperação de Senha</h2>
            <p>Clique no link abaixo para redefinir sua senha:</p>
            <p><a href='$link' style='background: #1a3a2f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Redefinir Senha</a></p>
            <p>Este link expira em 24 horas.</p>
        ";

        return $this->enviar(
            $email,
            'Recuperação de Senha - Monã Hotel',
            $html
        );
    }

    /**
     * Enviar email de bem-vindo
     */
    public function enviarBoasVindas($usuario) {
        $html = "
            <h2>Bem-vindo ao Monã Hotel!</h2>
            <p>Olá {$usuario['nome']},</p>
            <p>Sua conta foi criada com sucesso!</p>
            <p>Agora você pode fazer reservas e acompanhar suas hospedagens.</p>
            <p><a href='" . getenv('APP_URL') . "/pages/reserva.php' style='background: #1a3a2f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Fazer Reserva</a></p>
        ";

        return $this->enviar(
            $usuario['email'],
            'Bem-vindo ao Monã Hotel!',
            $html
        );
    }

    /**
     * Enviar email de pagamento confirmado
     */
    public function enviarPagamentoConfirmado($usuario, $reserva) {
        $html = "
            <h2>Pagamento Confirmado!</h2>
            <p>Olá {$usuario['nome']},</p>
            <p>Seu pagamento foi recebido com sucesso.</p>
            <p><strong>Valor:</strong> R$ " . number_format($reserva['valor_total'], 2, ',', '.') . "</p>
            <p><strong>Referência:</strong> #{$reserva['id']}</p>
            <p>Sua reserva foi confirmada e você receberá um email com os detalhes da hospedagem.</p>
        ";

        return $this->enviar(
            $usuario['email'],
            'Pagamento Confirmado - Monã Hotel',
            $html
        );
    }

    /**
     * Template de confirmação de reserva
     */
    private function templateConfirmacaoReserva($usuario, $reserva, $quarto) {
        $dataCheckin = date('d/m/Y', strtotime($reserva['data_checkin']));
        $dataCheckout = date('d/m/Y', strtotime($reserva['data_checkout']));
        $valor = number_format($reserva['valor_total'], 2, ',', '.');

        return "
            <h2>Confirmação de Reserva</h2>
            <p>Olá {$usuario['nome']},</p>
            <p>Sua reserva foi confirmada! Aqui estão os detalhes:</p>
            
            <h3>Detalhes da Reserva</h3>
            <ul>
                <li><strong>Reserva #:</strong> {$reserva['id']}</li>
                <li><strong>Quarto:</strong> {$quarto['nome']}</li>
                <li><strong>Check-in:</strong> $dataCheckin</li>
                <li><strong>Check-out:</strong> $dataCheckout</li>
                <li><strong>Hóspedes:</strong> {$reserva['quantidade_hospedes']}</li>
                <li><strong>Valor Total:</strong> R\$ $valor</li>
            </ul>
            
            <h3>Informações Importantes</h3>
            <ul>
                <li>Check-in a partir das 14h00</li>
                <li>Check-out até as 11h00</li>
                <li>Documentação: RG + CPF (original)</li>
                <li>Atendimento 24h disponível</li>
            </ul>
            
            <p>Obrigado por escolher o Monã Hotel!</p>
            <p>Qualquer dúvida, entre em contato através de contato@monã.com</p>
        ";
    }

    /**
     * Enviar email genérico
     */
    public function enviar($para, $assunto, $html) {
        $payload = [
            'from' => $this->fromEmail,
            'to' => $para,
            'subject' => $assunto,
            'html' => $html
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'code' => $httpCode,
            'data' => $result
        ];
    }
}
