<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - Monã Hotel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1><a href="home.php">Monã <span>Hotel</span></a></h1>
            </div>
            <nav class="navbar-menu">
                <a href="home.php">Voltar</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="api/logout.php" class="btn-logout">Sair</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Entre em Contato</h1>
            <p>Estamos aqui para ajudar com suas dúvidas</p>
        </div>

        <div class="contact-container">
            <form method="POST" action="../api/send-contact.php" class="contact-form">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Telefone (opcional)</label>
                    <input type="tel" name="telefone" placeholder="(00) 0000-0000">
                </div>

                <div class="form-group">
                    <label>Assunto</label>
                    <select name="assunto" required>
                        <option value="">Selecione um assunto</option>
                        <option value="reserva">Dúvida sobre Reserva</option>
                        <option value="pagamento">Dúvida sobre Pagamento</option>
                        <option value="hospedagem">Informações sobre Hospedagem</option>
                        <option value="sugestao">Sugestão</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mensagem</label>
                    <textarea name="mensagem" rows="6" required placeholder="Sua mensagem aqui..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
            </form>

            <div class="contact-info">
                <h3>Informações de Contato</h3>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Telefone</h4>
                        <p>+55 (00) 0000-0000</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p>contato@monã.com</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Endereço</h4>
                        <p>Rua Principal, 123<br>Cidade - Estado</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Horário de Funcionamento</h4>
                        <p>24 horas, 7 dias por semana</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Monã Hotel. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
