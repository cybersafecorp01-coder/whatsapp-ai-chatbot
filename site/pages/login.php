<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monã Hotel</title>
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

    <div class="auth-container">
        <div class="auth-box">
            <h2>Bem-vindo de Volta</h2>
            <p>Acesse sua conta Monã</p>

            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                switch($_GET['error']) {
                    case 'invalid': echo 'Email ou senha incorretos'; break;
                    case 'required': echo 'Preencha todos os campos'; break;
                    default: echo 'Erro ao fazer login';
                }
                ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="../api/do-login.php">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" name="senha" required>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="lembrar" id="lembrar">
                    <label for="lembrar">Lembrar-me</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>

            <p class="auth-footer">
                Não tem uma conta? <a href="registro.php">Cadastre-se aqui</a>
            </p>
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
