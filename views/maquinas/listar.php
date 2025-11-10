<?php
require_once '../../controllers/AuthController.php';
AuthController::verificarAutenticacao();

$usuarioLogado = AuthController::getUsuarioLogado();

require_once '../../config/database.php';
require_once '../../models/Maquina.php';

// Inicializar variáveis
$error = '';
$maquinas = [];

try {
    $db = DatabaseConfig::getConnection();
    $maquina = new Maquina($db);
    $stmt = $maquina->listar();
    $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $error = "Erro ao carregar máquinas: " . $e->getMessage();
}

// Processar filtros se existirem
$filtro_status = $_GET['status'] ?? '';
$filtro_saude = $_GET['saude'] ?? '';

if ($filtro_status || $filtro_saude) {
    $maquinas = array_filter($maquinas, function($maq) use ($filtro_status, $filtro_saude) {
        $status_match = !$filtro_status || $maq['status'] == $filtro_status;
        $saude_match = !$filtro_saude || $maq['saude'] == $filtro_saude;
        return $status_match && $saude_match;
    });
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Máquinas - Sistema de Manutenção</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">Sistema de Manutenção</h1>
            <nav class="nav">
                <ul>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li><a href="listar.php" class="active">Máquinas</a></li>
                    <li><a href="../relatorios/listar.php">Relatórios</a></li>
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
            <h2>Gerenciar Máquinas</h2>
            <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                <a href="cadastrar.php" class="btn btn-primary">+ Nova Máquina</a>
            <?php endif; ?>
        </div>

        <?php if($error): ?>
            <div class="alert alert-error">
                <strong>Erro:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="">Todos os Status</option>
                        <option value="ATIVA" <?php echo $filtro_status == 'ATIVA' ? 'selected' : ''; ?>>Ativa</option>
                        <option value="MANUTENCAO" <?php echo $filtro_status == 'MANUTENCAO' ? 'selected' : ''; ?>>Manutenção</option>
                        <option value="PARADA" <?php echo $filtro_status == 'PARADA' ? 'selected' : ''; ?>>Parada</option>
                        <option value="DESLIGADA" <?php echo $filtro_status == 'DESLIGADA' ? 'selected' : ''; ?>>Desligada</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="saude">Saúde:</label>
                    <select id="saude" name="saude">
                        <option value="">Todas as Saúdes</option>
                        <option value="OPERACIONAL" <?php echo $filtro_saude == 'OPERACIONAL' ? 'selected' : ''; ?>>Operacional</option>
                        <option value="INTERMEDIARIA" <?php echo $filtro_saude == 'INTERMEDIARIA' ? 'selected' : ''; ?>>Intermediária</option>
                        <option value="CRITICA" <?php echo $filtro_saude == 'CRITICA' ? 'selected' : ''; ?>>Crítica</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">Filtrar</button>
                    <a href="listar.php" class="btn btn-outline">Limpar</a>
                </div>
            </form>
        </div>

        <!-- Resumo -->
        <div class="summary-cards">
            <div class="summary-card">
                <span class="summary-number"><?php echo count($maquinas); ?></span>
                <span class="summary-label">Total de Máquinas</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($maquinas, function($m) { return $m['status'] == 'ATIVA'; })); ?></span>
                <span class="summary-label">Ativas</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($maquinas, function($m) { return $m['status'] == 'MANUTENCAO'; })); ?></span>
                <span class="summary-label">Em Manutenção</span>
            </div>
            <div class="summary-card critical">
                <span class="summary-number"><?php echo count(array_filter($maquinas, function($m) { return $m['saude'] == 'CRITICA'; })); ?></span>
                <span class="summary-label">Críticas</span>
            </div>
        </div>

        <!-- Tabela de Máquinas -->
        <div class="table-container">
            <?php if(count($maquinas) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Setor</th>
                            <th>Status</th>
                            <th>Saúde</th>
                            <th>Responsável</th>
                            <th>Última Atualização</th>
                            <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($maquinas as $maq): ?>
                            <tr class="saude-<?php echo strtolower($maq['saude']); ?>">
                                <td><?php echo htmlspecialchars($maq['id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($maq['nome_maquina']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($maq['nome_tipo']); ?></td>
                                <td><?php echo htmlspecialchars($maq['nome_setor']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($maq['status']); ?>">
                                        <?php echo $maq['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="saude-badge saude-<?php echo strtolower($maq['saude']); ?>">
                                        <?php echo $maq['saude']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(!empty($maq['usuario_nome'])): ?>
                                        <?php echo htmlspecialchars($maq['usuario_nome']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($maq['updated_at'])); ?></small>
                                </td>
                                <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                                    <td class="actions">
                                        <a href="editar.php?id=<?php echo $maq['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                            ✏️ Editar
                                        </a>
                                        <a href="../../controllers/maquina_excluir.php?id=<?php echo $maq['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Excluir"
                                           onclick="return confirm('Tem certeza que deseja excluir a máquina <?php echo addslashes($maq['nome_maquina']); ?>?')">
                                            🗑️ Excluir
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">🏭</div>
                    <h3>Nenhuma máquina encontrada</h3>
                    <p><?php echo ($filtro_status || $filtro_saude) ? 'Tente ajustar os filtros ou' : ''; ?></p>
                    <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                        <a href="cadastrar.php" class="btn btn-primary">Cadastrar Primeira Máquina</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Auto-submit do formulário quando os selects mudam (opcional)
        document.getElementById('status').addEventListener('change', function() {
            if(this.value) {
                document.querySelector('.filter-form').submit();
            }
        });
        
        document.getElementById('saude').addEventListener('change', function() {
            if(this.value) {
                document.querySelector('.filter-form').submit();
            }
        });
    </script>
</body>
</html>