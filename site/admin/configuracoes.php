<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user = $db->fetch("SELECT * FROM usuarios WHERE id = ? AND tipo = 'admin'", [$_SESSION['user_id']]);
if (!$user) {
    die('Acesso negado');
}

$config = $db->fetch("SELECT * FROM configuracoes LIMIT 1");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Monã Hotel</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">
                <h2>Monã <span>Admin</span></h2>
            </div>
            <nav class="sidebar-menu">
                <a href="index.php" class="menu-item">
                    <i class="fas fa-dashboard"></i> Dashboard
                </a>
                <a href="reservas.php" class="menu-item">
                    <i class="fas fa-calendar"></i> Reservas
                </a>
                <a href="quartos.php" class="menu-item">
                    <i class="fas fa-door-open"></i> Quartos
                </a>
                <a href="clientes.php" class="menu-item">
                    <i class="fas fa-users"></i> Clientes
                </a>
                <a href="mensagens.php" class="menu-item">
                    <i class="fas fa-envelope"></i> Mensagens
                </a>
                <a href="pagamentos.php" class="menu-item">
                    <i class="fas fa-credit-card"></i> Pagamentos
                </a>
                <a href="configuracoes.php" class="menu-item active">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="../../api/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Configurações do Hotel</h1>
            </header>

            <div class="content">
                <div class="section">
                    <h2>Informações do Hotel</h2>
                    <form method="POST" action="../../api/update-config.php">
                        <div class="form-group">
                            <label>Nome do Hotel</label>
                            <input type="text" name="nome_hotel" value="<?php echo htmlspecialchars($config['nome_hotel'] ?? ''); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($config['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Telefone</label>
                                <input type="tel" name="telefone" value="<?php echo htmlspecialchars($config['telefone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Endereço</label>
                            <input type="text" name="endereco" value="<?php echo htmlspecialchars($config['endereco'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Descrição</label>
                            <textarea name="descricao" rows="4"><?php echo htmlspecialchars($config['descricao'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Taxa de Serviço (%)</label>
                            <input type="number" name="taxa_servico" step="0.01" value="<?php echo $config['taxa_servico'] ?? 0; ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                    </form>
                </div>

                <div class="section">
                    <h2>Integrações</h2>
                    <div class="integration-boxes">
                        <div class="integration-box">
                            <h3><i class="fas fa-credit-card"></i> ASAAS</h3>
                            <p>Sistema de Pagamento</p>
                            <button class="btn btn-small" onclick="alert('Configurar ASAAS')">Configurar</button>
                        </div>
                        <div class="integration-box">
                            <h3><i class="fas fa-envelope"></i> Resend API</h3>
                            <p>Envio de Emails</p>
                            <button class="btn btn-small" onclick="alert('Configurar Resend')">Configurar</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin.js"></script>
</body>
</html>
