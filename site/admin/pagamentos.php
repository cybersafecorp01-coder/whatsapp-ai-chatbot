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
    <title>Pagamentos - Monã Hotel</title>
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
                <a href="pagamentos.php" class="menu-item active">
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
                <h1>Histórico de Pagamentos</h1>
            </header>

            <div class="content">
                <div class="section">
                    <h2>Pagamentos Processados</h2>
                    <table class="table table-large">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Método</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pagamentos = $db->fetchAll("
                                SELECT r.*, u.nome as cliente_nome 
                                FROM reservas r 
                                JOIN usuarios u ON r.usuario_id = u.id 
                                WHERE r.metodo_pagamento IS NOT NULL 
                                ORDER BY r.updated_at DESC
                            ");
                            foreach($pagamentos as $pag):
                            ?>
                            <tr>
                                <td><strong>#<?php echo $pag['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($pag['cliente_nome']); ?></td>
                                <td>R$ <?php echo number_format($pag['valor_total'], 2, ',', '.'); ?></td>
                                <td><?php echo ucfirst($pag['metodo_pagamento']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $pag['status']; ?>">
                                        <?php echo ucfirst($pag['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pag['updated_at'])); ?></td>
                                <td class="actions">
                                    <button class="btn-small btn-info" onclick="verPagamento(<?php echo $pag['id']; ?>)">
                                        <i class="fas fa-eye"></i>
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
        function verPagamento(id) {
            alert('Detalhes do pagamento #' + id);
        }
    </script>
</body>
</html>
