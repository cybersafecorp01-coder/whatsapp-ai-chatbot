<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Monã Hotel</title>
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
        <div class="auth-box auth-box-large">
            <h2>Crie sua Conta</h2>
            <p>Junte-se ao Monã Hotel</p>

            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                switch($_GET['error']) {
                    case 'exists': echo 'Este email já está cadastrado'; break;
                    case 'invalid': echo 'Dados inválidos'; break;
                    case 'required': echo 'Preencha todos os campos'; break;
                    default: echo 'Erro ao cadastrar';
                }
                ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="../api/do-register.php">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nome Completo</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> CPF</label>
                        <input type="text" name="cpf" placeholder="000.000.000-00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Telefone</label>
                        <input type="tel" name="telefone" placeholder="(00) 00000-0000" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Senha</label>
                        <input type="password" name="senha" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Confirmar Senha</label>
                        <input type="password" name="confirma_senha" required>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="termos" id="termos" required>
                    <label for="termos">
                        Concordo com os <a href="termos.php" target="_blank">termos e condições</a> e <a href="lgpd.php" target="_blank">LGPD</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
            </form>

            <p class="auth-footer">
                Já tem uma conta? <a href="login.php">Faça login aqui</a>
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
