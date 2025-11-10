<?php
require_once 'config/database.php';
require_once 'models/OrdemServico.php';

try {
    $database = new DatabaseConfig();
    $db = $database->getConnection();
    $os = new OrdemServico($db);
    
    echo "<h3>Teste Final - Sistema O.S.</h3>";
    
    // Testar listagem
    $stmt = $os->listar();
    $ordens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Total de O.S. encontradas: " . count($ordens) . "<br><br>";
    
    foreach($ordens as $os_item) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
        echo "<strong>OS:</strong> " . $os_item['numero_os'] . "<br>";
        echo "<strong>Equipamento:</strong> " . $os_item['equipamento'] . "<br>";
        echo "<strong>Status:</strong> " . $os_item['status'] . "<br>";
        echo "<strong>Setor:</strong> " . ($os_item['nome_setor'] ?? 'N/A') . "<br>";
        echo "</div>";
    }
    
} catch(Exception $e) {
    echo "❌ ERRO: " . $e->getMessage();
}
?>