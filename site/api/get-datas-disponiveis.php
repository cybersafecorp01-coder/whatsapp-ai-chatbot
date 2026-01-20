<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

$quarto_id = $_GET['quarto_id'] ?? null;

if (!$quarto_id) {
    echo json_encode(['datas' => []]);
    exit;
}

try {
    // Buscar datas disponíveis para o quarto
    $datas = $db->fetchAll(
        "SELECT data FROM disponibilidades WHERE quarto_id = ? AND data >= CURDATE() ORDER BY data ASC",
        [$quarto_id]
    );
    
    // Formatar datas em array
    $diasDisponiveis = array_map(fn($row) => $row['data'], $datas);
    
    echo json_encode([
        'success' => true,
        'datas' => $diasDisponiveis
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar datas disponíveis',
        'datas' => []
    ]);
}
?>
