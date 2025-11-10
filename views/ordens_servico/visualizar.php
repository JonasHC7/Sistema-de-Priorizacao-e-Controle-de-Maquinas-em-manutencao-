<?php
require_once '../../controllers/AuthController.php';
AuthController::verificarAutenticacao();

$usuarioLogado = AuthController::getUsuarioLogado();

if(!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$os_id = (int)$_GET['id'];

require_once '../../config/database.php';
require_once '../../models/OrdemServico.php';

try {
    $db = DatabaseConfig::getConnection();
    $ordemServico = new OrdemServico($db);
    $os_data = $ordemServico->buscarPorId($os_id);
    
    if(!$os_data) {
        header("Location: listar.php?error=os_nao_encontrada");
        exit;
    }
    
} catch(Exception $e) {
    die("Erro: " . $e->getMessage());
}

// Mapeamentos para exibição
$tipos_manutencao = [
    'CORRETIVA' => 'Corretiva',
    'PREVENTIVA' => 'Preventiva', 
    'PREDITIVA' => 'Preditiva',
    'MELHORIAS' => 'Melhorias'
];

$areas = [
    'ELETRICA' => 'Elétrica',
    'MECANICA' => 'Mecânica',
    'MARCENARIA' => 'Marcenaria',
    'FUNILARIA' => 'Funilaria',
    'TORNEARIA' => 'Tornearia',
    'PREDIAL' => 'Predial',
    'OUTROS' => 'Outros'
];

$status_text = [
    'ABERTA' => 'Aberta',
    'EM_ANDAMENTO' => 'Em Andamento',
    'CONCLUIDA' => 'Concluída',
    'CANCELADA' => 'Cancelada'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar O.S. - Sistema de Manutenção</title>
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
                    <li><a href="listar.php">O.S.</a></li>
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
            <h2>Ordem de Serviço - <?php echo htmlspecialchars($os_data['numero_os']); ?></h2>
            <div class="action-buttons">
                <a href="listar.php" class="btn btn-secondary">← Voltar</a>
                <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
            </div>
        </div>

        <div class="os-view-container">
            <!-- Cabeçalho da O.S. -->
            <div class="os-view-header">
                <div class="os-view-info">
                    <h1>ORDEM DE SERVIÇO</h1>
                    <p><strong>Número:</strong> <?php echo htmlspecialchars($os_data['numero_os']); ?></p>
                    <p><strong>Data de Abertura:</strong> <?php echo date('d/m/Y H:i', strtotime($os_data['data_abertura'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge status-<?php echo strtolower($os_data['status']); ?>">
                            <?php echo $status_text[$os_data['status']]; ?>
                        </span>
                    </p>
                    <p><strong>Prioridade:</strong> 
                        <span class="priority-badge priority-<?php echo strtolower($os_data['prioridade']); ?>">
                            <?php echo ucfirst(strtolower($os_data['prioridade'])); ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Informações Principais -->
            <div class="os-view-section">
                <h3>Informações do Serviço</h3>
                <div class="os-view-grid">
                    <div class="os-view-item">
                        <label>Tipo de Manutenção:</label>
                        <span><?php echo $tipos_manutencao[$os_data['tipo_manutencao']]; ?></span>
                    </div>
                    <div class="os-view-item">
                        <label>Equipamento:</label>
                        <span><?php echo htmlspecialchars($os_data['equipamento']); ?></span>
                    </div>
                    <div class="os-view-item">
                        <label>Área:</label>
                        <span><?php echo $areas[$os_data['area']]; ?></span>
                    </div>
                    <div class="os-view-item">
                        <label>Setor:</label>
                        <span><?php echo htmlspecialchars($os_data['nome_setor']); ?></span>
                    </div>
                    <?php if($os_data['data_programada']): ?>
                        <div class="os-view-item">
                            <label>Data Programada:</label>
                            <span><?php echo date('d/m/Y', strtotime($os_data['data_programada'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Descrição do Problema -->
            <div class="os-view-section">
                <h3>Descrição do Problema</h3>
                <div class="os-view-content">
                    <?php echo nl2br(htmlspecialchars($os_data['descricao_defeito'])); ?>
                </div>
            </div>

            <!-- Causa e Ação Corretiva -->
            <?php if($os_data['causa_defeito'] || $os_data['acao_corretiva']): ?>
                <div class="os-view-section">
                    <h3>Análise e Correção</h3>
                    <?php if($os_data['causa_defeito']): ?>
                        <div class="os-view-subsection">
                            <h4>Causa do Defeito:</h4>
                            <div class="os-view-content">
                                <?php echo nl2br(htmlspecialchars($os_data['causa_defeito'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($os_data['acao_corretiva']): ?>
                        <div class="os-view-subsection">
                            <h4>Ação Corretiva:</h4>
                            <div class="os-view-content">
                                <?php echo nl2br(htmlspecialchars($os_data['acao_corretiva'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="os-view-subsection">
                        <h4>Entrada para Preventiva:</h4>
                        <span class="status-badge <?php echo $os_data['entrada_preventiva'] == 'SIM' ? 'status-concluida' : 'status-cancelada'; ?>">
                            <?php echo $os_data['entrada_preventiva'] == 'SIM' ? 'SIM' : 'NÃO'; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Responsáveis -->
            <div class="os-view-section">
                <h3>Responsáveis</h3>
                <div class="os-view-grid">
                    <div class="os-view-item">
                        <label>Solicitado por:</label>
                        <span><?php echo htmlspecialchars($os_data['solicitante_nome']); ?></span>
                        <small><?php echo date('d/m/Y H:i', strtotime($os_data['data_abertura'])); ?></small>
                    </div>
                    
                    <?php if($os_data['recebedor_nome']): ?>
                        <div class="os-view-item">
                            <label>Recebido por:</label>
                            <span><?php echo htmlspecialchars($os_data['recebedor_nome']); ?></span>
                            <small><?php echo date('d/m/Y H:i', strtotime($os_data['data_recebimento'])); ?></small>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($os_data['aceitador_nome']): ?>
                        <div class="os-view-item">
                            <label>Aceito por:</label>
                            <span><?php echo htmlspecialchars($os_data['aceitador_nome']); ?></span>
                            <small><?php echo date('d/m/Y H:i', strtotime($os_data['data_aceite'])); ?></small>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($os_data['analisador_nome']): ?>
                        <div class="os-view-item">
                            <label>Analisado por:</label>
                            <span><?php echo htmlspecialchars($os_data['analisador_nome']); ?></span>
                            <small><?php echo date('d/m/Y H:i', strtotime($os_data['data_analise'])); ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Observações -->
            <?php if($os_data['observacoes']): ?>
                <div class="os-view-section">
                    <h3>Observações</h3>
                    <div class="os-view-content">
                        <?php echo nl2br(htmlspecialchars($os_data['observacoes'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>