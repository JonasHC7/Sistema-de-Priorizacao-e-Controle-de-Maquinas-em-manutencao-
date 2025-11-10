<?php
require_once '../../controllers/AuthController.php';
AuthController::verificarAutenticacao();
AuthController::verificarPermissao('GERENTE');

$usuarioLogado = AuthController::getUsuarioLogado();

if(!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$maquina_id = (int)$_GET['id'];

require_once '../../config/database.php';
require_once '../../models/Maquina.php';

try {
    $db = DatabaseConfig::getConnection();
    $maquina = new Maquina($db);
    $maquina_data = $maquina->buscarPorId($maquina_id);
    
    if(!$maquina_data) {
        header("Location: listar.php?error=maquina_nao_encontrada");
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
    <title>Editar Máquina - Sistema de Manutenção</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">Sistema de Manutenção</h1>
            <nav class="nav">
                <ul>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li><a href="listar.php">Máquinas</a></li>
                    <li><a href="../relatorios/listar.php">Relatórios</a></li>
                    <li><a href="../usuarios/listar.php">Usuários</a></li>
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
            <h2>Editar Máquina</h2>
            <a href="listar.php" class="btn btn-secondary">← Voltar</a>
        </div>

        <div class="alert alert-info">
            <strong>Funcionalidade em desenvolvimento:</strong> A edição de máquinas estará disponível em breve.
        </div>

        <div class="machine-details">
            <h3>Informações da Máquina</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <label>ID:</label>
                    <span><?php echo $maquina_data['id']; ?></span>
                </div>
                <div class="detail-item">
                    <label>Nome:</label>
                    <span><?php echo htmlspecialchars($maquina_data['nome_maquina']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Tipo:</label>
                    <span><?php echo htmlspecialchars($maquina_data['nome_tipo']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Setor:</label>
                    <span><?php echo htmlspecialchars($maquina_data['nome_setor']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Status:</label>
                    <span class="status-badge status-<?php echo strtolower($maquina_data['status']); ?>">
                        <?php echo $maquina_data['status']; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Saúde:</label>
                    <span class="saude-badge saude-<?php echo strtolower($maquina_data['saude']); ?>">
                        <?php echo $maquina_data['saude']; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Última Atualização:</label>
                    <span><?php echo date('d/m/Y H:i', strtotime($maquina_data['updated_at'])); ?></span>
                </div>
            </div>
        </div>
    </main>
</body>
</html>