<?php
require_once '../controllers/AuthController.php';
AuthController::verificarAutenticacao();

$usuarioLogado = AuthController::getUsuarioLogado();

require_once '../config/database.php';
require_once '../models/Maquina.php';
require_once '../models/Relatorio.php';

try {
    $db = DatabaseConfig::getConnection();
    
    // Carregar dados das máquinas
    $maquina = new Maquina($db);
    $stmt_maquinas = $maquina->listar();
    $maquinas = $stmt_maquinas->fetchAll(PDO::FETCH_ASSOC);
    
    // Carregar dados dos relatórios
    $relatorio = new Relatorio($db);
    $stmt_relatorios = $relatorio->listar();
    $relatorios = $stmt_relatorios->fetchAll(PDO::FETCH_ASSOC);
    
    // Estatísticas das máquinas
    $total_maquinas = $maquina->contarTotal();
    $maquinas_ativas = $maquina->contarPorStatus('ATIVA');
    $maquinas_manutencao = $maquina->contarPorStatus('MANUTENCAO');
    $maquinas_criticas = $maquina->contarPorSaude('CRITICA');
    
    // Estatísticas dos relatórios
    $total_relatorios = $relatorio->contarTotal();
    
} catch(Exception $e) {
    // Em caso de erro, mostrar mensagem amigável
    $error = "Erro ao carregar dados: " . $e->getMessage();
    $maquinas = [];
    $relatorios = [];
    $total_maquinas = 0;
    $maquinas_ativas = 0;
    $maquinas_manutencao = 0;
    $maquinas_criticas = 0;
    $total_relatorios = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Manutenção</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">Sistema de Manutenção</h1>
            <nav class="nav">
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="maquinas/listar.php">Máquinas</a></li>
        <li><a href="relatorios/listar.php">Relatórios</a></li>
        <?php if($_SESSION['usuario_cargo'] == 'GERENTE'): ?>
            <li><a href="usuarios/listar.php">Usuários</a></li>
        <?php endif; ?>
        <!-- ADICIONE ESTA LINHA -->
        <li><a href="ordens_servico/listar.php">O.S.</a></li>
        <!-- FIM DA ADIÇÃO -->
        <li><a href="../logout.php" class="logout-btn">Sair</a></li>
    </ul>
</nav>
            <div class="user-info">
                <span>Olá, <?php echo htmlspecialchars($usuarioLogado['nome']); ?> (<?php echo $usuarioLogado['cargo']; ?>)</span>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard">
            <h2>Dashboard</h2>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Estatísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total de Máquinas</h3>
                    <span class="stat-number"><?php echo $total_maquinas; ?></span>
                </div>
                <div class="stat-card">
                    <h3>Máquinas Ativas</h3>
                    <span class="stat-number"><?php echo $maquinas_ativas; ?></span>
                </div>
                <div class="stat-card">
                    <h3>Em Manutenção</h3>
                    <span class="stat-number"><?php echo $maquinas_manutencao; ?></span>
                </div>
                <div class="stat-card critical">
                    <h3>Máquinas Críticas</h3>
                    <span class="stat-number"><?php echo $maquinas_criticas; ?></span>
                </div>
                <div class="stat-card">
                    <h3>Total de Relatórios</h3>
                    <span class="stat-number"><?php echo $total_relatorios; ?></span>
                </div>
            </div>

            <!-- Seção de Máquinas -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Status das Máquinas</h3>
                    <a href="maquinas/listar.php" class="btn btn-sm btn-primary">Ver Todas</a>
                </div>
                
                <?php if(count($maquinas) > 0): ?>
                    <div class="machines-grid">
                        <?php foreach($maquinas as $maq): ?>
                            <div class="machine-card status-<?php echo strtolower($maq['status']); ?> saude-<?php echo strtolower($maq['saude']); ?>">
                                <h4><?php echo htmlspecialchars($maq['nome_maquina']); ?></h4>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($maq['nome_tipo']); ?></p>
                                <p><strong>Setor:</strong> <?php echo htmlspecialchars($maq['nome_setor']); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="status-badge status-<?php echo strtolower($maq['status']); ?>">
                                        <?php echo $maq['status']; ?>
                                    </span>
                                </p>
                                <p><strong>Saúde:</strong> 
                                    <span class="saude-badge saude-<?php echo strtolower($maq['saude']); ?>">
                                        <?php echo $maq['saude']; ?>
                                    </span>
                                </p>
                                <?php if(!empty($maq['usuario_nome'])): ?>
                                    <p><strong>Responsável:</strong> <?php echo htmlspecialchars($maq['usuario_nome']); ?></p>
                                <?php endif; ?>
                                <p class="update-time"><small>Atualizado: <?php echo date('d/m/Y H:i', strtotime($maq['updated_at'])); ?></small></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Nenhuma máquina cadastrada</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Seção de Relatórios Recentes -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Relatórios Recentes</h3>
                    <a href="relatorios/listar.php" class="btn btn-sm btn-primary">Ver Todos</a>
                </div>
                
                <?php if(count($relatorios) > 0): ?>
                    <div class="reports-list">
                        <?php 
                        // Mostrar apenas os 5 relatórios mais recentes
                        $recentes = array_slice($relatorios, 0, 5);
                        foreach($recentes as $rel): 
                        ?>
                            <div class="report-item">
                                <div class="report-info">
                                    <h4><?php echo htmlspecialchars($rel['nome_maquina']); ?></h4>
                                    <p><strong>Setor:</strong> <?php echo htmlspecialchars($rel['nome_setor']); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $rel['status_final'])); ?>">
                                            <?php echo $rel['status_final']; ?>
                                        </span>
                                    </p>
                                    <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($rel['criado_em'])); ?></p>
                                </div>
                                <div class="report-actions">
                                    <a href="relatorios/visualizar.php?id=<?php echo $rel['id']; ?>" class="btn btn-sm btn-secondary">Ver</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Nenhum relatório encontrado</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>