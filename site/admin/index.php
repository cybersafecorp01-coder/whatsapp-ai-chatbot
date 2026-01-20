<?php
session_start();
require_once '../includes/db.php';

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user = $db->fetch("SELECT * FROM usuarios WHERE id = ? AND tipo = 'admin'", [$_SESSION['user_id']]);
if (!$user) {
    session_destroy();
    header('Location: login.php?error=unauthorized');
    exit;
}

// Buscar estatísticas
$total_reservas = $db->fetch("SELECT COUNT(*) as total FROM reservas")['total'];
$reservas_pendentes = $db->fetch("SELECT COUNT(*) as total FROM reservas WHERE status = 'pendente'")['total'];
$total_clientes = $db->fetch("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'cliente'")['total'];
$total_quartos = $db->fetch("SELECT COUNT(*) as total FROM quartos")['total'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Monã Hotel</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="logo">
                <h2>Monã <span>Admin</span></h2>
            </div>
            <nav class="sidebar-menu">
                <a href="index.php" class="menu-item active">
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

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <header class="top-bar">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user['nome']); ?></span>
                    <img src="https://via.placeholder.com/40" alt="Avatar">
                </div>
            </header>

            <div class="content">
                <!-- STATS CARDS -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #1a3a2f;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_reservas; ?></h3>
                            <p>Total de Reservas</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #2d5f54;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $reservas_pendentes; ?></h3>
                            <p>Reservas Pendentes</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #1a3a2f;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_clientes; ?></h3>
                            <p>Clientes Registrados</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #2d5f54;">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_quartos; ?></h3>
                            <p>Quartos Disponíveis</p>
                        </div>
                    </div>
                </div>

                <!-- RECENT RESERVATIONS -->
                <div class="section">
                    <h2>Últimas Reservas</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Quarto</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $reservas = $db->fetchAll("
                                SELECT r.*, u.nome as cliente_nome, q.nome as quarto_nome 
                                FROM reservas r 
                                JOIN usuarios u ON r.usuario_id = u.id 
                                JOIN quartos q ON r.quarto_id = q.id 
                                ORDER BY r.created_at DESC 
                                LIMIT 5
                            ");
                            foreach($reservas as $res):
                            ?>
                            <tr>
                                <td>#<?php echo $res['id']; ?></td>
                                <td><?php echo htmlspecialchars($res['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($res['quarto_nome']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['data_checkin'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['data_checkout'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $res['status']; ?>">
                                        <?php echo ucfirst($res['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="reservas.php" class="btn-small">Ver</a>
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
</body>
</html>
