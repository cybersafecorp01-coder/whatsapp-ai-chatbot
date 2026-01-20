<?php
/**
 * Classe para integração com ASAAS
 * Processamento de pagamentos
 */

class AsaasPayment {
    private $apiKey;
    private $apiUrl = 'https://api.asaas.com/v3';
    private $webhookSecret;

    public function __construct() {
        $this->apiKey = getenv('ASAAS_API_KEY');
        $this->webhookSecret = getenv('ASAAS_WEBHOOK_SECRET');
    }

    /**
     * Criar cobrança (pagamento)
     */
    public function criarCobranca($dados) {
        $payload = [
            'customer' => $dados['email'],
            'billingType' => 'CREDIT_CARD',
            'value' => $dados['valor'],
            'dueDate' => $dados['data_vencimento'],
            'description' => $dados['descricao'] ?? 'Reserva de Hospedagem',
            'externalReference' => $dados['reserva_id']
        ];

        if (isset($dados['cartao'])) {
            $payload['creditCard'] = $dados['cartao'];
            $payload['creditCardHolderInfo'] = $dados['titular'];
        }

        return $this->request('POST', '/payments', $payload);
    }

    /**
     * Obter status de cobrança
     */
    public function obterCobranca($cobrancaId) {
        return $this->request('GET', '/payments/' . $cobrancaId);
    }

    /**
     * Reembolsar cobrança
     */
    public function reembolsar($cobrancaId, $valor = null) {
        $payload = [];
        if ($valor) {
            $payload['value'] = $valor;
        }

        return $this->request('POST', '/payments/' . $cobrancaId . '/refund', $payload);
    }

    /**
     * Listar cobranças
     */
    public function listarCobrancas($filtros = []) {
        $query = http_build_query($filtros);
        $endpoint = '/payments' . ($query ? '?' . $query : '');
        return $this->request('GET', $endpoint);
    }

    /**
     * Criar cliente
     */
    public function criarCliente($dados) {
        $payload = [
            'name' => $dados['nome'],
            'email' => $dados['email'],
            'phone' => $dados['telefone'] ?? null,
            'cpf' => $dados['cpf'] ?? null,
            'address' => $dados['endereco'] ?? null,
            'city' => $dados['cidade'] ?? null,
            'state' => $dados['estado'] ?? null,
            'postalCode' => $dados['cep'] ?? null
        ];

        return $this->request('POST', '/customers', $payload);
    }

    /**
     * Validar assinatura de webhook
     */
    public function validarWebhook($signature, $body) {
        $hash = hash_hmac('sha256', $body, $this->webhookSecret);
        return hash_equals($hash, $signature);
    }

    /**
     * Requisição HTTP para API ASAAS
     */
    private function request($method, $endpoint, $dados = null) {
        $url = $this->apiUrl . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_CUSTOMREQUEST => $method
        ]);

        if ($method !== 'GET' && $dados) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        }

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

// Webhook para ASAAS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], 'webhook-asaas') !== false) {
    $payment = new AsaasPayment();
    $signature = $_SERVER['HTTP_X_ASAAS_SIGNATURE'] ?? '';
    $body = file_get_contents('php://input');

    if ($payment->validarWebhook($signature, $body)) {
        $evento = json_decode($body, true);

        if ($evento['event'] === 'payment_received') {
            // Pagamento confirmado - atualizar reserva
            require_once 'db.php';
            $reserva_id = $evento['data']['externalReference'];
            $db->update('reservas', [
                'status' => 'confirmada',
                'metodo_pagamento' => 'asaas',
                'referencia_pagamento' => $evento['data']['id']
            ], ['id' => $reserva_id]);

            http_response_code(200);
            echo json_encode(['success' => true]);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid signature']);
    }
}
