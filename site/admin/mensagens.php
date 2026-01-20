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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens - Monã Hotel</title>
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
                <a href="mensagens.php" class="menu-item active">
                    <i class="fas fa-envelope"></i> Mensagens
                </a>
                <a href="pagamentos.php" class="menu-item">
                    <i class="fas fa-credit-card"></i> Pagamentos
                </a>
                <a href="configuracoes.php" class="menu-item">
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
                <h1>Mensagens de Contato</h1>
            </header>

            <div class="content">
                <div class="section">
                    <h2>Mensagens Recebidas</h2>
                    <table class="table table-large">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Assunto</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $mensagens = $db->fetchAll("SELECT * FROM mensagens ORDER BY created_at DESC");
                            foreach($mensagens as $msg):
                            ?>
                            <tr class="<?php echo !$msg['lida'] ? 'unread' : ''; ?>">
                                <td><strong>#<?php echo $msg['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($msg['nome']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo htmlspecialchars($msg['assunto']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $msg['lida'] ? 'success' : 'warning'; ?>">
                                        <?php echo $msg['lida'] ? 'Lida' : 'Não Lida'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="btn-small btn-info" onclick="lerMensagem(<?php echo $msg['id']; ?>)">
                                        <i class="fas fa-envelope-open"></i>
                                    </button>
                                    <button class="btn-small btn-danger" onclick="deletarMensagem(<?php echo $msg['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin.js"></script>
    <script>
        function lerMensagem(id) {
            alert('Lendo mensagem #' + id);
        }
        function deletarMensagem(id) {
            if(confirm('Deletar esta mensagem?')) {
                alert('Mensagem deletada!');
            }
        }
    </script>
</body>
</html>
