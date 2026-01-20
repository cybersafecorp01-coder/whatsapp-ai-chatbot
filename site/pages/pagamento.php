<?php
session_start();
require_once '../includes/db.php';

// Verificar se há dados de reserva na sessão
if (!isset($_SESSION['dados_reserva'])) {
    header('Location: ../pages/reserva.php?error=sessao');
    exit;
}

$reserva = $_SESSION['dados_reserva'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Monã Hotel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1><a href="home.php">Monã <span>Hotel</span></a></h1>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Finalizar Pagamento</h1>
            <p>Confirme o pagamento para completar sua reserva</p>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-bottom: 40px;">
            <!-- FORMULÁRIO DE PAGAMENTO -->
            <div style="background: #f8f9fa; padding: 30px; border-radius: 10px;">
                <h2 style="color: #1a3a2f; margin-bottom: 30px;">Dados do Cartão</h2>

                <form method="POST" action="../api/process-payment.php" id="form-pagamento">

                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nome do Titular</label>
                        <input type="text" name="titular_nome" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-credit-card"></i> Número do Cartão</label>
                        <input type="text" name="cartao_numero" placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Validade</label>
                            <input type="text" name="cartao_validade" placeholder="MM/AA" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> CVV</label>
                            <input type="text" name="cartao_cvv" placeholder="000" maxlength="4" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Banco</label>
                        <input type="text" name="banco" placeholder="Digite seu banco">
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="termos_pagamento" id="termos" required>
                        <label for="termos">
                            Concordo com os <a href="termos.php">termos de pagamento</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-lock"></i> Confirmar Pagamento id="btn-confirmar"
                    </button>
                </form>

                <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-left: 4px solid #1976d2; border-radius: 5px;">
                    <p style="margin: 0; color: #1976d2;">
                        <i class="fas fa-lock-alt"></i>
                        <strong>Seus dados são protegidos por SSL/TLS</strong>
                    </p>
                </div>
            </div>

            <!-- RESUMO DA RESERVA -->
            <div style="background: #f8f9fa; padding: 30px; border-radius: 10px; height: fit-content;">
                <h3 style="color: #1a3a2f; margin-bottom: 20px;">Resumo</h3>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 10px 0; color: #999;">Quarto</p>
                    <p style="margin: 0; color: #333; font-weight: bold; font-size: 16px;">
                        <?php echo htmlspecialchars($reserva['quarto_nome']); ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;">Check-in</p>
                    <p style="margin: 0; color: #333; font-weight: bold;">
                        <?php echo date('d/m/Y', strtotime($reserva['data_checkin'])); ?>
                    </p>
                    <p style="margin: 5px 0 0 0; color: #999; font-size: 13px;">Check-out</p>
                    <p style="margin: 0; color: #333; font-weight: bold;">
                        <?php echo date('d/m/Y', strtotime($reserva['data_checkout'])); ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 10px 0; color: #999;">Hóspedes</p>
                    <p style="margin: 0; color: #333; font-weight: bold; font-size: 16px;">
                        <?php echo $reserva['quantidade_hospedes']; ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 10px 0; color: #999;">Cliente</p>
                    <p style="margin: 0; color: #333; font-weight: bold;">
                        <?php echo htmlspecialchars($reserva['nome_cliente']); ?>
                    </p>
                    <p style="margin: 5px 0 0 0; color: #999; font-size: 13px;">
                        <?php echo htmlspecialchars($reserva['email_cliente']); ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #1a3a2f;">
                    <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;">Valor Total</p>
                    <p style="margin: 0; color: #1a3a2f; font-weight: bold; font-size: 28px;">
                        R$ <?php echo number_format($reserva['valor_total'], 2, ',', '.'); ?>
                    </p>
                </div>

                <div style="background: #e8f5e9; padding: 15px; border-radius: 5px;">
                    <p style="margin: 0; color: #388e3c; font-size: 14px;">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Pagamento seguro via ASAAS</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Monã Hotel. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        // Formatar número de cartão
        document.querySelector('[name="cartao_numero"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formatted = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = formatted;
        });

        // Formatar validade
        document.querySelector('[name="cartao_validade"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });

        // Apenas números no CVV
        document.querySelector('[name="cartao_cvv"]')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Desabilitar botão durante envio
        document.getElementById('form-pagamento').addEventListener('submit', function(e) {
            document.getElementById('btn-confirmar').disabled = true;
            document.getElementById('btn-confirmar').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        });
    </script>
</body>
</html>
