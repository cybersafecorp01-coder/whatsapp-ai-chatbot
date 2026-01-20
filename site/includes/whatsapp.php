<?php

class WhatsAppMessage {
    private $api_key;
    private $api_url = 'https://api.whatsapp.com/send';
    
    public function __construct($api_key = '') {
        $this->api_key = $api_key ?: getenv('WHATSAPP_API_KEY');
    }

    /**
     * Enviar mensagem de confirmação de reserva via WhatsApp
     */
    public function enviarConfirmacaoReserva($telefone, $dados_reserva) {
        if (empty($telefone) || !$this->validarTelefone($telefone)) {
            return ['success' => false, 'error' => 'Telefone inválido'];
        }

        $mensagem = $this->gerarMensagemConfirmacao($dados_reserva);
        
        return $this->enviarMensagem($telefone, $mensagem);
    }

    /**
     * Enviar mensagem de cancelamento de reserva
     */
    public function enviarCancelamento($telefone, $dados_reserva) {
        if (empty($telefone) || !$this->validarTelefone($telefone)) {
            return ['success' => false, 'error' => 'Telefone inválido'];
        }

        $mensagem = "❌ *Reserva Cancelada*\n\n";
        $mensagem .= "Olá " . $dados_reserva['nome_cliente'] . ",\n\n";
        $mensagem .= "Sua reserva #" . $dados_reserva['id'] . " foi cancelada.\n";
        $mensagem .= "Se tiver dúvidas, entre em contato conosco.\n\n";
        $mensagem .= "📞 Contato: " . getenv('HOTEL_PHONE') . "\n";
        $mensagem .= "📧 Email: " . getenv('HOTEL_EMAIL');

        return $this->enviarMensagem($telefone, $mensagem);
    }

    /**
     * Enviar lembrança de check-in
     */
    public function enviarLembrancaCheckin($telefone, $dados_reserva) {
        if (empty($telefone) || !$this->validarTelefone($telefone)) {
            return ['success' => false, 'error' => 'Telefone inválido'];
        }

        $data_checkin = date('d/m/Y', strtotime($dados_reserva['data_checkin']));
        
        $mensagem = "🏨 *Lembrança de Check-in*\n\n";
        $mensagem .= "Olá " . $dados_reserva['nome_cliente'] . ",\n\n";
        $mensagem .= "Você tem check-in amanhã (" . $data_checkin . ") no Monã Hotel.\n\n";
        $mensagem .= "Seu quarto: " . $dados_reserva['quarto_nome'] . "\n";
        $mensagem .= "Horário de check-in: 14:00\n";
        $mensagem .= "Horário de check-out: 11:00\n\n";
        $mensagem .= "Esperamos você! 🎉\n\n";
        $mensagem .= "📞 " . getenv('HOTEL_PHONE');

        return $this->enviarMensagem($telefone, $mensagem);
    }

    /**
     * Gerar mensagem formatada de confirmação
     */
    private function gerarMensagemConfirmacao($dados) {
        $data_checkin = date('d/m/Y', strtotime($dados['data_checkin']));
        $data_checkout = date('d/m/Y', strtotime($dados['data_checkout']));
        $dias = (strtotime($dados['data_checkout']) - strtotime($dados['data_checkin'])) / (60 * 60 * 24);

        $mensagem = "✅ *Reserva Confirmada!*\n\n";
        $mensagem .= "Olá " . htmlspecialchars_decode($dados['nome_cliente']) . ",\n\n";
        $mensagem .= "Sua reserva foi confirmada com sucesso! 🎉\n\n";
        
        $mensagem .= "*Detalhes da Reserva:*\n";
        $mensagem .= "🏨 Quarto: " . htmlspecialchars_decode($dados['quarto_nome']) . "\n";
        $mensagem .= "📅 Check-in: " . $data_checkin . "\n";
        $mensagem .= "📅 Check-out: " . $data_checkout . "\n";
        $mensagem .= "🛏️ Hóspedes: " . $dados['quantidade_hospedes'] . "\n";
        $mensagem .= "🌙 Noites: " . $dias . "\n";
        $mensagem .= "💰 Total: R$ " . number_format($dados['valor_total'], 2, ',', '.') . "\n\n";
        
        $mensagem .= "*Número da Reserva: #" . $dados['id'] . "*\n\n";
        
        $mensagem .= "📧 Um email de confirmação foi enviado para você.\n\n";
        
        if (!empty($dados['notas'])) {
            $mensagem .= "*Notas:*\n" . htmlspecialchars_decode($dados['notas']) . "\n\n";
        }
        
        $mensagem .= "Obrigado por escolher o Monã Hotel! 🌟\n\n";
        $mensagem .= "📞 " . getenv('HOTEL_PHONE') . "\n";
        $mensagem .= "🌐 www.monahotel.com.br";

        return $mensagem;
    }

    /**
     * Enviar mensagem genérica via WhatsApp (usando link)
     */
    private function enviarMensagem($telefone, $mensagem) {
        // Limpar telefone (remover caracteres especiais)
        $telefone_limpo = preg_replace('/\D/', '', $telefone);
        
        // Garantir código de país (55 para Brasil)
        if (strlen($telefone_limpo) === 11) {
            $telefone_limpo = '55' . $telefone_limpo;
        }
        
        // Codificar mensagem para URL
        $mensagem_codificada = urlencode($mensagem);
        
        // Gerar link do WhatsApp
        $link_whatsapp = "https://wa.me/{$telefone_limpo}?text={$mensagem_codificada}";
        
        // Retornar sucesso com link (o cliente abrirá via JavaScript)
        return [
            'success' => true,
            'link' => $link_whatsapp,
            'telefone' => $telefone_limpo,
            'mensagem' => $mensagem
        ];
    }

    /**
     * Validar formato de telefone brasileiro
     */
    private function validarTelefone($telefone) {
        // Remove caracteres especiais
        $telefone_limpo = preg_replace('/\D/', '', $telefone);
        
        // Valida se tem 10-11 dígitos (com DDD)
        return strlen($telefone_limpo) >= 10 && strlen($telefone_limpo) <= 11;
    }

    /**
     * Enviar via API (se usar serviço pago como Twilio)
     * Este é um método alternativo para integração futura
     */
    private function enviarViaAPI($telefone, $mensagem) {
        if (empty($this->api_key)) {
            return ['success' => false, 'error' => 'API key não configurada'];
        }

        $payload = json_encode([
            'to' => $telefone,
            'message' => $mensagem
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 || $http_code === 201) {
            return [
                'success' => true,
                'response' => json_decode($response, true)
            ];
        }

        return [
            'success' => false,
            'error' => 'Erro ao enviar mensagem',
            'code' => $http_code
        ];
    }
}
?>
