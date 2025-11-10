<?php
require_once '../../controllers/AuthController.php';
AuthController::verificarAutenticacao();

$usuarioLogado = AuthController::getUsuarioLogado();

require_once '../../config/database.php';
require_once '../../models/Relatorio.php';

// Inicializar variáveis
$error = '';
$success = '';
$relatorios = [];

// Mensagens de sucesso
if(isset($_GET['success'])) {
    $success = "Relatório criado com sucesso!";
}

try {
    $db = DatabaseConfig::getConnection();
    $relatorio = new Relatorio($db);
    $stmt = $relatorio->listar();
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $error = "Erro ao carregar relatórios: " . $e->getMessage();
}

// Processar exclusão se solicitada
if(isset($_GET['excluir']) && $usuarioLogado['cargo'] == 'GERENTE') {
    $id_excluir = (int)$_GET['excluir'];
    try {
        if($relatorio->excluir($id_excluir)) {
            $success = "Relatório excluído com sucesso!";
            // Recarregar a lista
            $stmt = $relatorio->listar();
            $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(Exception $e) {
        $error = "Erro ao excluir relatório: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema de Manutenção</title>
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
                    <li><a href="listar.php" class="active">Relatórios</a></li>
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
            <h2>Relatórios de Manutenção</h2>
            <div class="action-buttons">
                <a href="criar.php" class="btn btn-primary">+ Novo Relatório</a>
                <button onclick="imprimirTodos()" class="btn btn-secondary">Imprimir Todos</button>
            </div>
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
                <span class="summary-number"><?php echo count($relatorios); ?></span>
                <span class="summary-label">Total de Relatórios</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($relatorios, function($r) { return $r['status_final'] == 'Em Análise'; })); ?></span>
                <span class="summary-label">Em Análise</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($relatorios, function($r) { return $r['status_final'] == 'Consertada'; })); ?></span>
                <span class="summary-label">Consertadas</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($relatorios, function($r) { return $r['status_final'] == 'Aguardando Peça'; })); ?></span>
                <span class="summary-label">Aguardando Peça</span>
            </div>
        </div>

        <!-- Lista de Relatórios -->
        <div class="reports-container">
            <?php if(count($relatorios) > 0): ?>
                <?php foreach($relatorios as $rel): ?>
                    <div class="report-card" id="report-<?php echo $rel['id']; ?>">
                        <div class="report-header">
                            <div class="report-info">
                                <h3>Relatório #<?php echo str_pad($rel['id'], 4, '0', STR_PAD_LEFT); ?></h3>
                                <p class="report-machine">
                                    <strong>Máquina:</strong> <?php echo htmlspecialchars($rel['nome_maquina']); ?> 
                                    - <?php echo htmlspecialchars($rel['nome_setor']); ?>
                                </p>
                                <p class="report-date">
                                    <strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($rel['criado_em'])); ?>
                                </p>
                                <p class="report-author">
                                    <strong>Técnico:</strong> <?php echo htmlspecialchars($rel['usuario_nome']); ?>
                                </p>
                            </div>
                            <div class="report-status">
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $rel['status_final'])); ?>">
                                    <?php echo $rel['status_final']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="report-content">
                            <div class="report-checklist">
                                <h4>Checklist:</h4>
                                <?php 
                                $checklist = json_decode($rel['checklist'], true);
                                if($checklist): 
                                ?>
                                    <div class="checklist-items">
                                        <?php foreach($checklist as $item => $value): ?>
                                            <div class="checklist-item">
                                                <span class="checklist-label"><?php echo ucfirst($item); ?>:</span>
                                                <span class="checklist-value <?php echo $value ? 'check-ok' : 'check-fail'; ?>">
                                                    <?php echo $value ? '✓ OK' : '✗ PROBLEMA'; ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Nenhum checklist disponível</p>
                                <?php endif; ?>
                            </div>

                            <div class="report-description">
                                <h4>Descrição:</h4>
                                <div class="description-text">
                                    <?php echo nl2br(htmlspecialchars($rel['descricao'])); ?>
                                </div>
                            </div>
                        </div>

                        <div class="report-actions">
                            <button onclick="imprimirRelatorio(<?php echo $rel['id']; ?>)" class="btn btn-primary">
                                🖨️ Imprimir
                            </button>
                            <a href="visualizar.php?id=<?php echo $rel['id']; ?>" class="btn btn-secondary">
                                👁️ Visualizar
                            </a>
                            <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                                <a href="editar.php?id=<?php echo $rel['id']; ?>" class="btn btn-warning">
                                    ✏️ Editar
                                </a>
                                <a href="listar.php?excluir=<?php echo $rel['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir este relatório?')">
                                    🗑️ Excluir
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Versão para impressão (oculta na tela) -->
                        <div class="report-print-version" id="print-<?php echo $rel['id']; ?>" style="display: none;">
                            <?php include 'relatorio_print_template.php'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📋</div>
                    <h3>Nenhum relatório encontrado</h3>
                    <p>Comece criando o primeiro relatório de manutenção</p>
                    <a href="criar.php" class="btn btn-primary">Criar Primeiro Relatório</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function imprimirRelatorio(id) {
            // Salvar o conteúdo original do body
            const originalContent = document.body.innerHTML;
            
            // Obter o conteúdo do relatório específico para impressão
            const printContent = document.getElementById('print-' + id).innerHTML;
            
            // Substituir o body pelo conteúdo de impressão
            document.body.innerHTML = printContent;
            
            // Imprimir
            window.print();
            
            // Restaurar o conteúdo original
            document.body.innerHTML = originalContent;
            
            // Recarregar event listeners
            window.location.reload();
        }

        function imprimirTodos() {
            window.print();
        }

        // Auto-print quando houver parâmetro na URL
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('print')) {
            const id = urlParams.get('print');
            setTimeout(() => imprimirRelatorio(id), 500);
        }
    </script>
</body>
</html>