<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monã Hotel - Hospedagem de Luxo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- HEADER -->
    <header class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1>Monã <span>Hotel</span></h1>
            </div>
            <nav class="navbar-menu">
                <a href="#home">Início</a>
                <a href="#quartos">Quartos</a>
                <a href="reserva.php">Reservar</a>
                <a href="contato.php">Contato</a>
            </nav>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h2>Bem-vindo ao Monã Hotel</h2>
            <p>Experiência de luxo e conforto em um único lugar</p>
            <a href="reserva.php" class="btn btn-primary">Faça sua Reserva</a>
        </div>
    </section>

    <!-- DESTAQUES -->
    <section class="features">
        <div class="container">
            <div class="feature-box">
                <i class="fas fa-crown"></i>
                <h3>Luxo Garantido</h3>
                <p>Ambiente sofisticado com acabamento premium</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-concierge-bell"></i>
                <h3>Atendimento 24h</h3>
                <p>Nossa equipe sempre pronta para ajudar</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-wifi"></i>
                <h3>WiFi Premium</h3>
                <p>Conexão de alta velocidade em todos os ambientes</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-utensils"></i>
                <h3>Gastronomia</h3>
                <p>Restaurante com chefs renomados</p>
            </div>
        </div>
    </section>

    <!-- QUARTOS -->
    <section id="quartos" class="rooms">
        <div class="container">
            <h2>Nossos Quartos</h2>
            <div class="rooms-grid">
                <div class="room-card">
                    <div class="room-image" style="background: linear-gradient(135deg, #1a3a2f 0%, #2d5f54 100%);"></div>
                    <h3>Suíte Luxo Vista Mar</h3>
                    <p>Capacidade: 2 hóspedes</p>
                    <p class="price">R$ 500,00 <span>/noite</span></p>
                    <a href="reserva.php" class="btn btn-secondary">Reservar</a>
                </div>
                <div class="room-card">
                    <div class="room-image" style="background: linear-gradient(135deg, #2d5f54 0%, #1a3a2f 100%);"></div>
                    <h3>Quarto Duplo Executivo</h3>
                    <p>Capacidade: 2 hóspedes</p>
                    <p class="price">R$ 300,00 <span>/noite</span></p>
                    <a href="reserva.php" class="btn btn-secondary">Reservar</a>
                </div>
                <div class="room-card">
                    <div class="room-image" style="background: linear-gradient(135deg, #1a3a2f 0%, #206d54 100%);"></div>
                    <h3>Quarto Simples</h3>
                    <p>Capacidade: 1 hóspede</p>
                    <p class="price">R$ 150,00 <span>/noite</span></p>
                    <a href="reserva.php" class="btn btn-secondary">Reservar</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
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

    <script src="assets/js/main.js"></script>
</body>
</html>
