<?php
require_once 'config/database.php';

try {
    $database = new DatabaseConfig();
    $db = $database->getConnection();
    
    echo "<h3>Debug - Ordens de Serviço</h3>";
    
    // Testar conexão
    echo "✅ Conexão com banco: OK<br>";
    
    // Testar se tabela existe
    $stmt = $db->query("SHOW TABLES LIKE 'ordens_servico'");
    if($stmt->rowCount() > 0) {
        echo "✅ Tabela ordens_servico: EXISTE<br>";
    } else {
        echo "❌ Tabela ordens_servico: NÃO EXISTE<br>";
        exit;
    }
    
    // Contar O.S.
    $stmt = $db->query("SELECT COUNT(*) as total FROM ordens_servico");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Total de O.S. na tabela: " . $row['total'] . "<br>";
    
    // Listar O.S.
    $stmt = $db->query("SELECT * FROM ordens_servico");
    $ordens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Lista de O.S.:<br>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Número</th><th>Equipamento</th><th>Status</th><th>Prioridade</th></tr>";
    foreach($ordens as $os) {
        echo "<tr>";
        echo "<td>{$os['numero_os']}</td>";
        echo "<td>{$os['equipamento']}</td>";
        echo "<td>{$os['status']}</td>";
        echo "<td>{$os['prioridade']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(Exception $e) {
    echo "❌ ERRO: " . $e->getMessage();
}
?>