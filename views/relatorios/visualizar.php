<?php
require_once '../../controllers/AuthController.php';
AuthController::verificarAutenticacao();

$usuarioLogado = AuthController::getUsuarioLogado();

if(!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$relatorio_id = (int)$_GET['id'];

require_once '../../config/database.php';
require_once '../../models/Relatorio.php';

try {
    $db = DatabaseConfig::getConnection();
    $relatorio = new Relatorio($db);
    $relatorio_data = $relatorio->buscarPorId($relatorio_id);
    
    if(!$relatorio_data) {
        header("Location: listar.php?error=relatorio_nao_encontrado");
        exit;
    }
    
} catch(Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Relatório - Sistema de Manutenção</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/print.css" media="print">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">Sistema de Manutenção</h1>
            <nav class="nav">
                <ul>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li><a href="../maquinas/listar.php">Máquinas</a></li>
                    <li><a href="listar.php">Relatórios</a></li>
                    <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                        <li><a href="../usuarios/listar.php">Usuários</a></li>
                    <?php endif; ?>
                    <li><a href="../../logout.php" class="logout-btn">Sair</a></li>
                </ul>
            </nav>
            <div class="user-info">
                <span>Olá, <?php echo htmlspecialchars($usuarioLogado['nome']); ?> (<?php echo $usuarioLogado['cargo']; ?>)</span>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="page-header">
            <h2>Visualizar Relatório</h2>
            <div class="action-buttons">
                <a href="listar.php" class="btn btn-secondary">← Voltar</a>
                <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
            </div>
        </div>

        <!-- Relatório para visualização e impressão -->
        <div class="report-view-container">
            <?php 
            // Usar o mesmo template de impressão, mas mostrar na tela
            $rel = $relatorio_data;
            include 'relatorio_print_template.php'; 
            ?>
        </div>
    </main>
</body>
</html>