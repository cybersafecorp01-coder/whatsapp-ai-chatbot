<?php
session_start();
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazer Reserva - Monã Hotel</title>
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
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Faça sua Reserva</h1>
            <p>Selecione as datas disponíveis e complete sua reserva</p>
        </div>

        <div class="reservation-form">
            <form method="POST" action="../api/do-reserve.php">
                <!-- SELEÇÃO DE QUARTO -->
                <div class="form-group">
                    <label>Quarto</label>
                    <select name="quarto_id" id="quarto_id" required onchange="carregarDatasDisponiveis()">
                        <option value="">Selecione um quarto</option>
                        <?php
                        $quartos = $db->fetchAll("SELECT * FROM quartos WHERE ativo = 1");
                        foreach($quartos as $quarto) {
                            echo "<option value='{$quarto['id']}'>{$quarto['nome']} - R$ " . number_format($quarto['preco_diaria'], 2, ',', '.') . "/noite (Capacidade: {$quarto['capacidade']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- SELEÇÃO DE DATAS DISPONÍVEIS -->
                <div id="datas-disponiveis" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Data de Check-in *</label>
                            <select name="data_checkin" id="data_checkin" required>
                                <option value="">Selecione uma data</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Data de Check-out *</label>
                            <select name="data_checkout" id="data_checkout" required>
                                <option value="">Selecione uma data</option>
                            </select>
                        </div>
                    </div>

                    <!-- RESUMO DE PREÇO -->
                    <div class="price-summary" id="price-summary" style="display: none;">
                        <h3>Resumo da Reserva</h3>
                        <div class="summary-row">
                            <span>Quarto:</span>
                            <span id="summary-quarto">-</span>
                        </div>
                        <div class="summary-row">
                            <span>Check-in:</span>
                            <span id="summary-checkin">-</span>
                        </div>
                        <div class="summary-row">
                            <span>Check-out:</span>
                            <span id="summary-checkout">-</span>
                        </div>
                        <div class="summary-row">
                            <span>Noites:</span>
                            <span id="summary-noites">-</span>
                        </div>
                        <div class="summary-row">
                            <span>Preço/noite:</span>
                            <span id="summary-preco">-</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="summary-total">R$ 0,00</span>
                        </div>
                    </div>
                </div>

                <!-- INFORMAÇÕES PESSOAIS -->
                <div class="divider" style="margin: 40px 0; border-top: 1px solid #ddd;"></div>
                <h3 style="margin-bottom: 20px;">Informações Pessoais</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nome Completo *</label>
                        <input type="text" name="nome" required placeholder="Seu nome">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required placeholder="seu@email.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Telefone *</label>
                        <input type="tel" name="telefone" required placeholder="(11) 99999-9999" class="mascara-telefone" maxlength="15">
                    </div>
                    <div class="form-group">
                        <label>CPF *</label>
                        <input type="text" name="cpf" required placeholder="000.000.000-00" class="mascara-cpf" maxlength="14">
                    </div>
                </div>

                <div class="form-group">
                    <label>Quantidade de Hóspedes *</label>
                    <input type="number" name="quantidade_hospedes" min="1" max="4" value="1" required>
                </div>

                <div class="form-group">
                    <label>Notas Especiais (opcional)</label>
                    <textarea name="notas" rows="4" placeholder="Informações adicionais (alergias, preferências, etc)..."></textarea>
                </div>

                <!-- ACEITAR TERMOS -->
                <div class="checkbox-group" style="margin: 20px 0;">
                    <label>
                        <input type="checkbox" name="aceitar_termos" required>
                        Concordo com os <a href="termos.php" target="_blank">Termos e Condições</a> e <a href="politica-privacidade.php" target="_blank">Política de Privacidade</a>
                    </label>
                </div>

                <!-- BOTÃO SUBMIT -->
                <button type="submit" class="btn btn-primary btn-large" id="btn-reservar" disabled>
                    <i class="fas fa-check"></i> Confirmar Reserva
                </button>
            </form>

            <!-- MENSAGEM SE NÃO HOUVER DATAS -->
            <div id="sem-datas" style="display: none; margin-top: 30px;">
                <div class="alert alert-warning">
                    <i class="fas fa-calendar-times"></i>
                    <p>Este quarto não possui datas disponíveis no momento. Tente outro quarto ou volte mais tarde.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-section">
                <h4>Monã Hotel</h4>
                <p>Luxury Hotel Experience</p>
            </div>
            <div class="footer-section">
                <h4>Links Rápidos</h4>
                <ul>
                    <li><a href="termos.php">Termos e Condições</a></li>
                    <li><a href="lgpd.php">LGPD</a></li>
                    <li><a href="politica-privacidade.php">Política de Privacidade</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contato</h4>
                <p>Email: contato@monã.com</p>
                <p>Telefone: +55 (00) 0000-0000</p>
            </div>
            <div class="footer-section">
                <h4>Redes Sociais</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Monã Hotel. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
        // Dados de preço dos quartos
        const quartoPrecosMap = {
            <?php 
            foreach($quartos as $q) {
                echo "'{$q['id']}': {preco: {$q['preco_diaria']}, nome: '{$q['nome']}'}, ";
            }
            ?>
        };

        // Dados de disponibilidades (carregado via AJAX)
        let datasDisponiveis = [];

        function carregarDatasDisponiveis() {
            const quartoId = document.getElementById('quarto_id').value;
            
            if (!quartoId) {
                document.getElementById('datas-disponiveis').style.display = 'none';
                document.getElementById('sem-datas').style.display = 'none';
                document.getElementById('btn-reservar').disabled = true;
                return;
            }

            // Buscar datas disponíveis via AJAX
            fetch('../api/get-datas-disponiveis.php?quarto_id=' + quartoId)
                .then(response => response.json())
                .then(data => {
                    datasDisponiveis = data.datas || [];
                    
                    if (datasDisponiveis.length === 0) {
                        document.getElementById('sem-datas').style.display = 'block';
                        document.getElementById('datas-disponiveis').style.display = 'none';
                        document.getElementById('btn-reservar').disabled = true;
                    } else {
                        document.getElementById('sem-datas').style.display = 'none';
                        document.getElementById('datas-disponiveis').style.display = 'block';
                        preencherSelectDatas();
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar datas:', error);
                    alert('Erro ao carregar datas disponíveis');
                });
        }

        function preencherSelectDatas() {
            const selectCheckin = document.getElementById('data_checkin');
            const selectCheckout = document.getElementById('data_checkout');
            
            // Limpar opções anteriores
            selectCheckin.innerHTML = '<option value="">Selecione uma data de check-in</option>';
            selectCheckout.innerHTML = '<option value="">Selecione uma data de check-out</option>';
            
            // Adicionar datas disponíveis
            datasDisponiveis.forEach(data => {
                const option1 = document.createElement('option');
                option1.value = data;
                option1.textContent = formatarData(data);
                selectCheckin.appendChild(option1);
                
                const option2 = document.createElement('option');
                option2.value = data;
                option2.textContent = formatarData(data);
                selectCheckout.appendChild(option2);
            });

            // Adicionar listeners
            selectCheckin.addEventListener('change', validarDatas);
            selectCheckout.addEventListener('change', validarDatas);
        }

        function validarDatas() {
            const checkin = document.getElementById('data_checkin').value;
            const checkout = document.getElementById('data_checkout').value;
            const priceSection = document.getElementById('price-summary');
            const btnReservar = document.getElementById('btn-reservar');

            if (!checkin || !checkout) {
                priceSection.style.display = 'none';
                btnReservar.disabled = true;
                return;
            }

            // Validar se checkout é depois de checkin
            if (checkout <= checkin) {
                alert('A data de check-out deve ser após a data de check-in');
                document.getElementById('data_checkout').value = '';
                priceSection.style.display = 'none';
                btnReservar.disabled = true;
                return;
            }

            // Validar se ambas datas estão disponíveis
            if (!datasDisponiveis.includes(checkin) || !datasDisponiveis.includes(checkout)) {
                alert('Uma ou ambas as datas selecionadas não estão disponíveis');
                priceSection.style.display = 'none';
                btnReservar.disabled = true;
                return;
            }

            // Calcular noites e total
            const dataCheckin = new Date(checkin);
            const dataCheckout = new Date(checkout);
            const noites = Math.ceil((dataCheckout - dataCheckin) / (1000 * 60 * 60 * 24));
            const quartoId = document.getElementById('quarto_id').value;
            const precoNoite = quartoPrecosMap[quartoId].preco;
            const total = noites * precoNoite;

            // Atualizar resumo
            document.getElementById('summary-quarto').textContent = quartoPrecosMap[quartoId].nome;
            document.getElementById('summary-checkin').textContent = formatarData(checkin);
            document.getElementById('summary-checkout').textContent = formatarData(checkout);
            document.getElementById('summary-noites').textContent = noites;
            document.getElementById('summary-preco').textContent = 'R$ ' + precoNoite.toFixed(2).replace('.', ',');
            document.getElementById('summary-total').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');

            priceSection.style.display = 'block';
            btnReservar.disabled = false;
        }

        function formatarData(data) {
            const [ano, mes, dia] = data.split('-');
            return `${dia}/${mes}/${ano}`;
        }

        // Aplicar máscaras ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara CPF: 000.000.000-00
            document.querySelectorAll('.mascara-cpf').forEach(el => {
                el.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.slice(0, 11);
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    e.target.value = value;
                });
            });

            // Máscara Telefone: (00) 9 0000-0000 ou (00) 0000-0000
            document.querySelectorAll('.mascara-telefone').forEach(el => {
                el.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.slice(0, 11);
                    
                    // Formatar com base no número de dígitos
                    if (value.length <= 2) {
                        value = value.replace(/(\d{0,2})/, '($1');
                    } else if (value.length <= 6) {
                        value = value.replace(/(\d{2})(\d{0,4})/, '($1) $2');
                    } else if (value.length <= 10) {
                        value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                    } else {
                        value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                    }
                    
                    e.target.value = value;
                });
            });
        });
    </script>

    <style>
        .price-summary {
            background: #f5f5f5;
            border: 2px solid #1a3a2f;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }

        .price-summary h3 {
            color: #1a3a2f;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
        }

        .summary-row.total {
            border: none;
            border-top: 2px solid #1a3a2f;
            font-weight: bold;
            font-size: 18px;
            color: #1a3a2f;
            margin-top: 10px;
            padding-top: 15px;
        }

        .checkbox-group {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #1a3a2f;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            margin: 0;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-group a {
            color: #1a3a2f;
            text-decoration: underline;
        }

        .btn-large {
            min-height: 50px;
            font-size: 18px;
            font-weight: bold;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</body>
</html>
