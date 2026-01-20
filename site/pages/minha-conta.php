<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user = $db->fetch("SELECT * FROM usuarios WHERE id = ?", [$_SESSION['user_id']]);
$reservas = $db->fetchAll("
    SELECT r.*, q.nome as quarto_nome 
    FROM reservas r 
    JOIN quartos q ON r.quarto_id = q.id 
    WHERE r.usuario_id = ? 
    ORDER BY r.created_at DESC
", [$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - Monã Hotel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1><a href="home.php">Monã <span>Hotel</span></a></h1>
            </div>
            <nav class="navbar-menu">
                <a href="home.php">Voltar</a>
                <a href="../api/logout.php" class="btn-logout">Sair</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Minha Conta</h1>
            <p>Bem-vindo, <?php echo htmlspecialchars($user['nome']); ?>!</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px; margin-bottom: 40px;">
            <!-- DADOS PESSOAIS -->
            <div style="background: #f8f9fa; padding: 30px; border-radius: 10px; height: fit-content;">
                <h3 style="color: #1a3a2f; margin-bottom: 20px;">
                    <i class="fas fa-user"></i> Meus Dados
                </h3>

                <div style="margin-bottom: 20px;">
                    <p style="color: #999; font-size: 13px; margin: 0;">Nome</p>
                    <p style="color: #333; font-weight: bold; margin: 5px 0 0 0;">
                        <?php echo htmlspecialchars($user['nome']); ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px;">
                    <p style="color: #999; font-size: 13px; margin: 0;">Email</p>
                    <p style="color: #333; font-weight: bold; margin: 5px 0 0 0;">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px;">
                    <p style="color: #999; font-size: 13px; margin: 0;">CPF</p>
                    <p style="color: #333; font-weight: bold; margin: 5px 0 0 0;">
                        <?php echo htmlspecialchars($user['cpf'] ?? 'Não informado'); ?>
                    </p>
                </div>

                <div style="margin-bottom: 20px;">
                    <p style="color: #999; font-size: 13px; margin: 0;">Telefone</p>
                    <p style="color: #333; font-weight: bold; margin: 5px 0 0 0;">
                        <?php echo htmlspecialchars($user['telefone'] ?? 'Não informado'); ?>
                    </p>
                </div>

                <div style="margin-bottom: 0;">
                    <p style="color: #999; font-size: 13px; margin: 0;">Cadastro</p>
                    <p style="color: #333; font-weight: bold; margin: 5px 0 0 0;">
                        <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>

                <button onclick="editarPerfil()" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                    <i class="fas fa-edit"></i> Editar Perfil
                </button>
                <button onclick="alterarSenha()" class="btn btn-secondary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-key"></i> Alterar Senha
                </button>
            </div>

            <!-- MINHAS RESERVAS -->
            <div style="background: #fff;">
                <h3 style="color: #1a3a2f; margin-bottom: 20px;">
                    <i class="fas fa-calendar-check"></i> Minhas Reservas
                </h3>

                <?php if (empty($reservas)): ?>
                <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 10px;">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <p style="color: #999; margin-bottom: 20px;">Você ainda não tem nenhuma reserva</p>
                    <a href="reserva.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Fazer uma Reserva
                    </a>
                </div>
                <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach($reservas as $res): ?>
                    <div style="border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">
                        <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h4 style="margin: 0; color: #1a3a2f;">
                                        <?php echo htmlspecialchars($res['quarto_nome']); ?>
                                    </h4>
                                    <p style="margin: 5px 0 0 0; color: #999; font-size: 13px;">
                                        Reserva #<?php echo $res['id']; ?>
                                    </p>
                                </div>
                                <span class="badge badge-<?php echo $res['status']; ?>">
                                    <?php echo ucfirst($res['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div style="padding: 15px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                                <div>
                                    <p style="color: #999; font-size: 12px; margin: 0;">Check-in</p>
                                    <p style="color: #333; font-weight: bold; margin: 5px 0 0 0;">
                                        <?php echo date('d/m/Y', strtotime($res['data_checkin'])); ?>
                                    </p>
                                </div>
                                <div>
                                    <p style="color: #999; font-size: 12px; margin: 0;">Check-out</p>
                                    <p style="color: #333; font-weight: bold; margin: 5px 0 0 0;">
                                        <?php echo date('d/m/Y', strtotime($res['data_checkout'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div style="padding-top: 15px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                                <p style="margin: 0; color: #1a3a2f; font-weight: bold; font-size: 18px;">
                                    R$ <?php echo number_format($res['valor_total'], 2, ',', '.'); ?>
                                </p>
                                <button onclick="verReserva(<?php echo $res['id']; ?>)" class="btn btn-small btn-info">
                                    <i class="fas fa-eye"></i> Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <a href="reserva.php" class="btn btn-primary" style="width: 100%; text-align: center; display: inline-block; margin-top: 20px;">
                    <i class="fas fa-plus"></i> Nova Reserva
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Monã Hotel. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
        function editarPerfil() {
            alert('Funcionalidade de editar perfil em desenvolvimento');
        }

        function alterarSenha() {
            alert('Funcionalidade de alterar senha em desenvolvimento');
        }

        function verReserva(id) {
            alert('Detalhes da reserva #' + id);
        }
    </script>
</body>
</html>
