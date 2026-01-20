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
    <title>Gerenciar Reservas - Monã Hotel</title>
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
                <a href="reservas.php" class="menu-item active">
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

        <main class="main-content">
            <header class="top-bar">
                <h1>Gerenciar Reservas</h1>
            </header>

            <div class="content">
                <div class="section">
                    <div class="section-header">
                        <h2>Todas as Reservas</h2>
                        <div class="filters">
                            <select id="filtro-status">
                                <option value="">Todos os Status</option>
                                <option value="pendente">Pendentes</option>
                                <option value="confirmada">Confirmadas</option>
                                <option value="cancelada">Canceladas</option>
                            </select>
                        </div>
                    </div>

                    <table class="table table-large">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Quarto</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Valor</th>
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
                            ");
                            foreach($reservas as $res):
                            ?>
                            <tr>
                                <td><strong>#<?php echo $res['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($res['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($res['quarto_nome']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['data_checkin'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['data_checkout'])); ?></td>
                                <td>R$ <?php echo number_format($res['valor_total'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $res['status']; ?>">
                                        <?php echo ucfirst($res['status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="btn-small btn-info" onclick="verReserva(<?php echo $res['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($res['status'] === 'pendente'): ?>
                                    <button class="btn-small btn-success" onclick="confirmarReserva(<?php echo $res['id']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn-small btn-danger" onclick="cancelarReserva(<?php echo $res['id']; ?>)">
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
        function verReserva(id) {
            alert('Detalhes da reserva #' + id);
        }
        function confirmarReserva(id) {
            if(confirm('Confirmar esta reserva?')) {
                alert('Reserva confirmada!');
            }
        }
        function cancelarReserva(id) {
            if(confirm('Cancelar esta reserva?')) {
                alert('Reserva cancelada!');
            }
        }
    </script>
</body>
</html>
