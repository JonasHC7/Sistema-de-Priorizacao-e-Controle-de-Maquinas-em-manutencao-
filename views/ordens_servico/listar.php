<?php
// Verificação manual de login - SEM AuthController
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit;
}

$usuarioLogado = [
    'id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['usuario_nome'],
    'cargo' => $_SESSION['usuario_cargo'],
    'setor_id' => $_SESSION['usuario_setor']
];

require_once '../../config/database.php';
require_once '../../models/OrdemServico.php';

// Inicializar variáveis
$error = '';
$success = '';
$ordens = [];

// Mensagens de sucesso
if(isset($_GET['success'])) {
    $success = "Ordem de Serviço " . $_GET['success'] . " com sucesso!";
}

try {
    $db = DatabaseConfig::getConnection();
    $ordemServico = new OrdemServico($db);
    $stmt = $ordemServico->listar();
    $ordens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $error = "Erro ao carregar ordens de serviço: " . $e->getMessage();
}

// Processar atualização de status se solicitada
if(isset($_GET['atualizar_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $novo_status = $_GET['atualizar_status'];
    
    try {
        if($ordemServico->atualizarStatus($id, $novo_status, $usuarioLogado['id'])) {
            $success = "Status da O.S. atualizado com sucesso!";
            // Recarregar a lista
            $stmt = $ordemServico->listar();
            $ordens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(Exception $e) {
        $error = "Erro ao atualizar status: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordens de Serviço - Sistema de Manutenção</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">Sistema de Manutenção</h1>
            <nav class="nav">
                <ul>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li><a href="../maquinas/listar.php">Máquinas</a></li>
                    <li><a href="../relatorios/listar.php">Relatórios</a></li>
                    <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                        <li><a href="../usuarios/listar.php">Usuários</a></li>
                    <?php endif; ?>
                    <li><a href="listar.php" class="active">O.S.</a></li>
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
            <h2>Ordens de Serviço</h2>
            <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                <a href="criar.php" class="btn btn-primary">+ Nova O.S.</a>
            <?php endif; ?>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-error">
                <strong>Erro:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Resumo -->
        <div class="summary-cards">
            <div class="summary-card">
                <span class="summary-number"><?php echo count($ordens); ?></span>
                <span class="summary-label">Total de O.S.</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($ordens, function($o) { return $o['status'] == 'ABERTA'; })); ?></span>
                <span class="summary-label">Abertas</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($ordens, function($o) { return $o['status'] == 'EM_ANDAMENTO'; })); ?></span>
                <span class="summary-label">Em Andamento</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($ordens, function($o) { return $o['status'] == 'CONCLUIDA'; })); ?></span>
                <span class="summary-label">Concluídas</span>
            </div>
        </div>

        <!-- Lista de Ordens de Serviço -->
        <div class="os-container">
            <?php if(count($ordens) > 0): ?>
                <?php foreach($ordens as $os): ?>
                    <div class="os-card status-<?php echo strtolower($os['status']); ?>">
                        <div class="os-header">
                            <div class="os-info">
                                <h3><?php echo htmlspecialchars($os['numero_os']); ?></h3>
                                <p class="os-equipamento">
                                    <strong>Equipamento:</strong> <?php echo htmlspecialchars($os['equipamento']); ?>
                                </p>
                                <p class="os-setor">
                                    <strong>Setor:</strong> <?php echo htmlspecialchars($os['nome_setor']); ?>
                                </p>
                                <p class="os-data">
                                    <strong>Abertura:</strong> <?php echo date('d/m/Y H:i', strtotime($os['data_abertura'])); ?>
                                </p>
                                <p class="os-solicitante">
                                    <strong>Solicitante:</strong> <?php echo htmlspecialchars($os['solicitante_nome']); ?>
                                </p>
                            </div>
                            <div class="os-status">
                                <span class="status-badge status-<?php echo strtolower($os['status']); ?>">
                                    <?php 
                                    $status_text = [
                                        'ABERTA' => 'Aberta',
                                        'EM_ANDAMENTO' => 'Em Andamento',
                                        'CONCLUIDA' => 'Concluída',
                                        'CANCELADA' => 'Cancelada'
                                    ];
                                    echo $status_text[$os['status']]; 
                                    ?>
                                </span>
                                <span class="priority-badge priority-<?php echo strtolower($os['prioridade']); ?>">
                                    <?php echo ucfirst(strtolower($os['prioridade'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="os-content">
                            <div class="os-details">
                                <p><strong>Tipo Manutenção:</strong> 
                                    <?php 
                                    $tipos = [
                                        'CORRETIVA' => 'Corretiva',
                                        'PREVENTIVA' => 'Preventiva',
                                        'PREDITIVA' => 'Preditiva',
                                        'MELHORIAS' => 'Melhorias'
                                    ];
                                    echo $tipos[$os['tipo_manutencao']]; 
                                    ?>
                                </p>
                                <p><strong>Área:</strong> 
                                    <?php 
                                    $areas = [
                                        'ELETRICA' => 'Elétrica',
                                        'MECANICA' => 'Mecânica',
                                        'MARCENARIA' => 'Marcenaria',
                                        'FUNILARIA' => 'Funilaria',
                                        'TORNEARIA' => 'Tornearia',
                                        'PREDIAL' => 'Predial',
                                        'OUTROS' => 'Outros'
                                    ];
                                    echo $areas[$os['area']]; 
                                    ?>
                                </p>
                                <p><strong>Descrição:</strong> <?php echo htmlspecialchars($os['descricao_defeito']); ?></p>
                                
                                <?php if($os['causa_defeito']): ?>
                                    <p><strong>Causa:</strong> <?php echo htmlspecialchars($os['causa_defeito']); ?></p>
                                <?php endif; ?>
                                
                                <?php if($os['acao_corretiva']): ?>
                                    <p><strong>Ação Corretiva:</strong> <?php echo htmlspecialchars($os['acao_corretiva']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="os-actions">
                            <a href="visualizar.php?id=<?php echo $os['id']; ?>" class="btn btn-secondary">
                                👁️ Visualizar
                            </a>
                            
                            <!-- Ações por status -->
                            <?php if($os['status'] == 'ABERTA' && $usuarioLogado['cargo'] == 'OPERACIONAL'): ?>
                                <a href="listar.php?atualizar_status=EM_ANDAMENTO&id=<?php echo $os['id']; ?>" 
                                   class="btn btn-warning"
                                   onclick="return confirm('Iniciar esta Ordem de Serviço?')">
                                    🛠️ Iniciar
                                </a>
                            <?php endif; ?>
                            
                            <?php if($os['status'] == 'EM_ANDAMENTO' && $usuarioLogado['cargo'] == 'OPERACIONAL'): ?>
                                <a href="listar.php?atualizar_status=CONCLUIDA&id=<?php echo $os['id']; ?>" 
                                   class="btn btn-success"
                                   onclick="return confirm('Marcar esta Ordem de Serviço como concluída?')">
                                    ✅ Concluir
                                </a>
                            <?php endif; ?>
                            
                            <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                                <a href="editar.php?id=<?php echo $os['id']; ?>" class="btn btn-warning">
                                    ✏️ Editar
                                </a>
                                <?php if($os['status'] == 'ABERTA'): ?>
                                    <a href="listar.php?atualizar_status=CANCELADA&id=<?php echo $os['id']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Cancelar esta Ordem de Serviço?')">
                                        ❌ Cancelar
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📋</div>
                    <h3>Nenhuma Ordem de Serviço encontrada</h3>
                    <p><?php echo ($usuarioLogado['cargo'] == 'GERENTE') ? 'Comece criando a primeira O.S.' : 'Aguarde o gerente criar uma O.S.'; ?></p>
                    <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                        <a href="criar.php" class="btn btn-primary">Criar Primeira O.S.</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>