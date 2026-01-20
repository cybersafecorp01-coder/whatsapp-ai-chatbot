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
    <title>Gerenciar Quartos - Monã Hotel</title>
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
                <a href="quartos.php" class="menu-item active">
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
                <h1>Gerenciar Quartos</h1>
                <button class="btn btn-primary" onclick="abrirAdicionarQuarto()">
                    <i class="fas fa-plus"></i> Adicionar Quarto
                </button>
            </header>

            <div class="content">
                <div class="section">
                    <h2>Quartos do Hotel</h2>
                    <div class="rooms-grid">
                        <?php
                        $quartos = $db->fetchAll("SELECT * FROM quartos");
                        foreach($quartos as $quarto):
                        ?>
                        <div class="room-card-admin">
                            <div class="room-header">
                                <h3><?php echo htmlspecialchars($quarto['nome']); ?></h3>
                                <span class="badge badge-<?php echo $quarto['ativo'] ? 'success' : 'danger'; ?>">
                                    <?php echo $quarto['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </div>
                            <div class="room-details">
                                <p><strong>Tipo:</strong> <?php echo ucfirst($quarto['tipo']); ?></p>
                                <p><strong>Capacidade:</strong> <?php echo $quarto['capacidade']; ?> pessoas</p>
                                <p><strong>Preço:</strong> R$ <?php echo number_format($quarto['preco_diaria'], 2, ',', '.'); ?>/noite</p>
                                <p><strong>Quantidade:</strong> <?php echo $quarto['quantidade']; ?></p>
                            </div>
                            <div class="room-actions">
                                <button class="btn-small btn-primary" onclick="editarQuarto(<?php echo $quarto['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn-small btn-danger" onclick="deletarQuarto(<?php echo $quarto['id']; ?>)">
                                    <i class="fas fa-trash"></i> Deletar
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- GERENCIAR DISPONIBILIDADES -->
                <div class="section">
                    <h2>Adicionar Datas Disponíveis</h2>
                    <form class="form-inline">
                        <div class="form-group">
                            <label>Quarto</label>
                            <select id="quarto-disponibilidade">
                                <option value="">Selecione...</option>
                                <?php foreach($quartos as $q): ?>
                                <option value="<?php echo $q['id']; ?>"><?php echo htmlspecialchars($q['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Data</label>
                            <input type="date" id="data-disponibilidade">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="adicionarDisponibilidade()">
                            Adicionar
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin.js"></script>
    <script>
        function abrirAdicionarQuarto() {
            alert('Adicionar novo quarto');
        }
        function editarQuarto(id) {
            alert('Editar quarto #' + id);
        }
        function deletarQuarto(id) {
            if(confirm('Deletar este quarto?')) {
                alert('Quarto deletado!');
            }
        }
        function adicionarDisponibilidade() {
            alert('Data adicionada como disponível!');
        }
    </script>
</body>
</html>
