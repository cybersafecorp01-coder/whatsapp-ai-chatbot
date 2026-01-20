<?php
session_start();
require_once '../includes/db.php';

$reserva_id = $_GET['reserva_id'] ?? null;

if (!$reserva_id) {
    header('Location: ../pages/home.php');
    exit;
}

// Buscar dados da reserva criada
$reserva = $db->fetch(
    "SELECT r.*, q.nome as quarto_nome FROM reservas r JOIN quartos q ON r.quarto_id = q.id WHERE r.id = ?",
    [$reserva_id]
);

if (!$reserva) {
    header('Location: ../pages/home.php?error=notfound');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Confirmada - Monã Hotel</title>
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
        <div style="max-width: 600px; margin: 60px auto; text-align: center;">
            
            <!-- ÍCONE DE SUCESSO -->
            <div style="margin-bottom: 30px;">
                <i class="fas fa-check-circle" style="font-size: 80px; color: #27ae60;"></i>
            </div>

            <!-- MENSAGEM DE SUCESSO -->
            <h1 style="color: #1a3a2f; margin-bottom: 15px;">Reserva Confirmada! 🎉</h1>
            <p style="font-size: 18px; color: #666; margin-bottom: 40px;">
                Sua reserva foi criada e o pagamento foi processado com sucesso.
            </p>

            <!-- DADOS DA RESERVA -->
            <div style="background: #f8f9fa; padding: 30px; border-radius: 10px; margin-bottom: 30px; text-align: left;">
                <h2 style="color: #1a3a2f; margin-bottom: 20px; text-align: center;">Detalhes da Reserva</h2>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;"><strong>Número da Reserva</strong></p>
                    <p style="margin: 0; color: #1a3a2f; font-weight: bold; font-size: 20px; font-family: monospace;">
                        #<?php echo str_pad($reserva['id'], 6, '0', STR_PAD_LEFT); ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;"><strong>Quarto</strong></p>
                    <p style="margin: 0; color: #333; font-weight: bold; font-size: 16px;">
                        <?php echo htmlspecialchars($reserva['quarto_nome']); ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;"><strong>Data de Check-in</strong></p>
                    <p style="margin: 0; color: #333; font-weight: bold;">
                        <?php echo date('d/m/Y', strtotime($reserva['data_checkin'])); ?> (14:00)
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;"><strong>Data de Check-out</strong></p>
                    <p style="margin: 0; color: #333; font-weight: bold;">
                        <?php echo date('d/m/Y', strtotime($reserva['data_checkout'])); ?> (11:00)
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;"><strong>Hóspedes</strong></p>
                    <p style="margin: 0; color: #333; font-weight: bold;">
                        <?php echo $reserva['quantidade_hospedes']; ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                    <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;"><strong>Valor Total</strong></p>
                    <p style="margin: 0; color: #1a3a2f; font-weight: bold; font-size: 24px;">
                        R$ <?php echo number_format($reserva['valor_total'], 2, ',', '.'); ?>
                    </p>
                </div>

                <div style="background: #e8f5e9; padding: 15px; border-radius: 5px; border-left: 4px solid #27ae60;">
                    <p style="margin: 0; color: #27ae60; font-weight: bold;">
                        <i class="fas fa-check"></i> Pagamento Confirmado
                    </p>
                </div>
            </div>

            <!-- PRÓXIMOS PASSOS -->
            <div style="background: #e3f2fd; padding: 25px; border-radius: 10px; margin-bottom: 30px; text-align: left;">
                <h3 style="color: #1976d2; margin-bottom: 15px;">
                    <i class="fas fa-info-circle"></i> Próximas Etapas
                </h3>
                <ul style="margin: 0; padding-left: 20px; color: #333;">
                    <li style="margin-bottom: 10px;">Um email de confirmação foi enviado para <strong><?php echo htmlspecialchars($reserva['email_cliente']); ?></strong></li>
                    <li style="margin-bottom: 10px;">Você receberá uma mensagem de confirmação no WhatsApp</li>
                    <li style="margin-bottom: 10px;">Chegue com 15-30 minutos de antecedência no check-in</li>
                    <li>Leve um documento de identidade e uma forma de pagamento</li>
                </ul>
            </div>

            <!-- INFORMAÇÕES DE CONTATO -->
            <div style="background: #fff3e0; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                <h4 style="color: #f57c00; margin: 0 0 10px 0;">
                    <i class="fas fa-headset"></i> Dúvidas?
                </h4>
                <p style="margin: 0; color: #666;">
                    Entre em contato conosco:<br>
                    <strong style="color: #333;">📞 Telefone:</strong> (11) 3000-0000<br>
                    <strong style="color: #333;">📧 Email:</strong> reservas@monã.com
                </p>
            </div>

            <!-- BOTÕES -->
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="home.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">
                    <i class="fas fa-home"></i> Voltar ao Início
                </a>
                <a href="<?php echo 'https://wa.me/55' . preg_replace('/\D/', '', $reserva['telefone_cliente']) . '?text='; ?>" class="btn btn-secondary" style="text-decoration: none; display: inline-block; background: #25d366;">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-section">
                <h4>Monã Hotel</h4>
                <p>Luxury Hotel Experience</p>
            </div>
            <div class="footer-section">
                <h4>Links Rápidos</h4>
                <ul>
                    <li><a href="termos.php">Termos e Condições</a></li>
                    <li><a href="lgpd.php">LGPD</a></li>
                    <li><a href="politica-privacidade.php">Política de Privacidade</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contato</h4>
                <p>Email: contato@monã.com</p>
                <p>Telefone: +55 (00) 0000-0000</p>
            </div>
            <div class="footer-section">
                <h4>Redes Sociais</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Monã Hotel. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
