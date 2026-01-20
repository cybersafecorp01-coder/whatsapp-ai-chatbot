<?php
require_once 'db.php';

if (!isset($_GET['migrate'])) {
    die("Acesso negado");
}

try {
    $db->query("DROP TABLE IF EXISTS disponibilidades");
    $db->query("DROP TABLE IF EXISTS reservas");
    $db->query("DROP TABLE IF EXISTS quartos");
    $db->query("DROP TABLE IF EXISTS usuarios");
    $db->query("DROP TABLE IF EXISTS configuracoes");
    $db->query("DROP TABLE IF EXISTS mensagens");

    // Tabela de Configurações
    $db->query("CREATE TABLE configuracoes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome_hotel VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        telefone VARCHAR(20),
        endereco TEXT,
        descricao TEXT,
        taxa_servico DECIMAL(10, 2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabela de Usuários
    $db->query("CREATE TABLE usuarios (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        cpf VARCHAR(11) UNIQUE,
        telefone VARCHAR(20),
        senha VARCHAR(255) NOT NULL,
        tipo ENUM('cliente', 'admin') DEFAULT 'cliente',
        ativo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Tabela de Quartos
    $db->query("CREATE TABLE quartos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        tipo ENUM('simples', 'duplo', 'suite', 'suite_luxo') DEFAULT 'duplo',
        capacidade INT DEFAULT 2,
        preco_diaria DECIMAL(10, 2) NOT NULL,
        descricao TEXT,
        amenidades JSON,
        quantidade INT DEFAULT 1,
        ativo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabela de Disponibilidades
    $db->query("CREATE TABLE disponibilidades (
        id INT PRIMARY KEY AUTO_INCREMENT,
        quarto_id INT NOT NULL,
        data DATE NOT NULL,
        disponivel TINYINT(1) DEFAULT 1,
        FOREIGN KEY (quarto_id) REFERENCES quartos(id) ON DELETE CASCADE,
        UNIQUE KEY unique_quarto_data (quarto_id, data)
    )");

    // Tabela de Reservas
    $db->query("CREATE TABLE reservas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        quarto_id INT NOT NULL,
        nome_cliente VARCHAR(255) NOT NULL,
        email_cliente VARCHAR(255) NOT NULL,
        telefone_cliente VARCHAR(20),
        cpf_cliente VARCHAR(20),
        data_checkin DATE NOT NULL,
        data_checkout DATE NOT NULL,
        quantidade_hospedes INT DEFAULT 1,
        valor_total DECIMAL(10, 2) NOT NULL,
        status ENUM('pendente', 'confirmada', 'cancelada') DEFAULT 'pendente',
        metodo_pagamento VARCHAR(50),
        referencia_pagamento VARCHAR(255),
        notas TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (quarto_id) REFERENCES quartos(id) ON DELETE CASCADE
    )");

    // Tabela de Mensagens de Contato
    $db->query("CREATE TABLE mensagens (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        telefone VARCHAR(20),
        assunto VARCHAR(255),
        mensagem TEXT NOT NULL,
        lida TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Inserir Admin Padrão
    $admin_senha = password_hash('admin123', PASSWORD_BCRYPT);
    $db->insert('usuarios', [
        'nome' => 'Administrador Monã',
        'email' => 'admin@monã.com',
        'cpf' => '00000000000',
        'telefone' => '+55 (00) 0000-0000',
        'senha' => $admin_senha,
        'tipo' => 'admin'
    ]);

    // Inserir Configurações Padrão
    $db->insert('configuracoes', [
        'nome_hotel' => 'Monã Hotel de Luxo',
        'email' => 'contato@monã.com',
        'telefone' => '+55 (00) 0000-0000',
        'endereco' => 'Rua Principal, 123 - Cidade',
        'descricao' => 'Hotel de luxo com excelente localização e serviços premium',
        'taxa_servico' => 10.00
    ]);

    // Inserir Quartos de Exemplo
    $quartos = [
        ['nome' => 'Suíte Luxo Vista Mar', 'tipo' => 'suite_luxo', 'capacidade' => 2, 'preco_diaria' => 500.00],
        ['nome' => 'Quarto Duplo Executivo', 'tipo' => 'duplo', 'capacidade' => 2, 'preco_diaria' => 300.00],
        ['nome' => 'Quarto Simples', 'tipo' => 'simples', 'capacidade' => 1, 'preco_diaria' => 150.00],
    ];

    foreach ($quartos as $quarto) {
        $db->insert('quartos', $quarto);
    }

    echo "<div style='font-family: Arial; padding: 20px; background: #f0f0f0;'>";
    echo "<h2 style='color: #1a3a2f;'>✓ Banco de dados criado com sucesso!</h2>";
    echo "<p><strong>Admin padrão:</strong> admin@monã.com / admin123</p>";
    echo "<p><strong>3 quartos de exemplo criados</strong></p>";
    echo "<p><a href='../../index.php' style='color: #1a3a2f; text-decoration: none;'>← Voltar ao site</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='font-family: Arial; padding: 20px; background: #ffe0e0;'>";
    echo "<h2 style='color: red;'>✗ Erro ao criar banco de dados</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
