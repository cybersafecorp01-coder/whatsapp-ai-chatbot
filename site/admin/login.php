<?php
session_start();
require_once '../includes/db.php';

// Se já está logado, redireciona para dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $error = 'Email e senha são obrigatórios';
    } else {
        // Buscar usuário admin
        $user = $db->fetch(
            "SELECT * FROM usuarios WHERE email = ? AND tipo = 'admin'",
            [$email]
        );

        if ($user && password_verify($senha, $user['senha'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email ou senha incorretos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Monã Hotel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a3a2f 0%, #2d5f55 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #1a3a2f;
            font-size: 32px;
            margin: 0;
        }

        .login-header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }

        .login-header span {
            color: #c4b5a0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1a3a2f;
            box-shadow: 0 0 0 3px rgba(26, 58, 47, 0.1);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c33;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1a3a2f 0%, #2d5f55 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(26, 58, 47, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
        }

        .login-footer a {
            color: #1a3a2f;
            text-decoration: none;
            font-size: 14px;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background: #f0f8f6;
            border: 1px solid #1a3a2f;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            font-size: 13px;
            text-align: center;
        }

        .demo-credentials h4 {
            color: #1a3a2f;
            margin: 0 0 10px 0;
        }

        .demo-credentials p {
            margin: 5px 0;
            color: #333;
        }

        .demo-credentials code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #c33;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Monã <span>Admin</span></h1>
            <p>Painel de Administração</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="admin@monã.com" 
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="senha">
                    <i class="fas fa-lock"></i> Senha
                </label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    placeholder="Sua senha" 
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Entrar no Admin
            </button>
        </form>

        <div class="demo-credentials">
            <h4>🔐 Credenciais Demo</h4>
            <p>Email: <code>admin@monã.com</code></p>
            <p>Senha: <code>admin123</code></p>
        </div>

        <div class="login-footer">
            <a href="../pages/home.php">
                <i class="fas fa-home"></i> Voltar ao Site
            </a>
        </div>
    </div>
</body>
</html>
