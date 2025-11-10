<?php
require_once '../../controllers/AuthController.php';
AuthController::verificarAutenticacao();
AuthController::verificarPermissao('GERENTE');

$usuarioLogado = AuthController::getUsuarioLogado();

require_once '../../config/database.php';
require_once '../../models/Usuario.php';
require_once '../../models/Setor.php';

// Inicializar variáveis
$error = '';
$success = '';
$usuarios = [];

// Mensagens de sucesso
if(isset($_GET['success'])) {
    $success = "Usuário " . $_GET['success'] . " com sucesso!";
}

try {
    $db = DatabaseConfig::getConnection();
    $usuario = new Usuario($db);
    $stmt = $usuario->listar();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $error = "Erro ao carregar usuários: " . $e->getMessage();
}

// Processar exclusão se solicitada
if(isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    
    // Não permitir excluir a si mesmo
    if($id_excluir == $usuarioLogado['id']) {
        $error = "Você não pode excluir seu próprio usuário!";
    } else {
        try {
            if($usuario->excluir($id_excluir)) {
                $success = "Usuário excluído com sucesso!";
                // Recarregar a lista
                $stmt = $usuario->listar();
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch(Exception $e) {
            $error = "Erro ao excluir usuário: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Sistema de Manutenção</title>
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
                    <li><a href="listar.php" class="active">Usuários</a></li>
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
            <h2>Gerenciar Usuários</h2>
            <a href="cadastrar.php" class="btn btn-primary">+ Novo Usuário</a>
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
                <span class="summary-number"><?php echo count($usuarios); ?></span>
                <span class="summary-label">Total de Usuários</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($usuarios, function($u) { return $u['cargo'] == 'GERENTE'; })); ?></span>
                <span class="summary-label">Gerentes</span>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo count(array_filter($usuarios, function($u) { return $u['cargo'] == 'OPERACIONAL'; })); ?></span>
                <span class="summary-label">Operacionais</span>
            </div>
        </div>

        <!-- Tabela de Usuários -->
        <div class="table-container">
            <?php if(count($usuarios) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Cargo</th>
                            <th>Setor</th>
                            <th>Data de Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usuarios as $user): ?>
                            <tr class="cargo-<?php echo strtolower($user['cargo']); ?>">
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['nome']); ?></strong>
                                    <?php if($user['id'] == $usuarioLogado['id']): ?>
                                        <span class="badge-you">(Você)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['telefone']); ?></td>
                                <td>
                                    <span class="cargo-badge cargo-<?php echo strtolower($user['cargo']); ?>">
                                        <?php echo $user['cargo']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(!empty($user['nome_setor'])): ?>
                                        <?php echo htmlspecialchars($user['nome_setor']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                                </td>
                                <td class="actions">
                                    <a href="editar.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                        ✏️ Editar
                                    </a>
                                    <?php if($user['id'] != $usuarioLogado['id']): ?>
                                        <a href="listar.php?excluir=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Excluir"
                                           onclick="return confirm('Tem certeza que deseja excluir o usuário <?php echo addslashes($user['nome']); ?>? Esta ação não pode ser desfeita.')">
                                            🗑️ Excluir
                                        </a>
                                    <?php else: ?>
                                        <span class="btn btn-sm btn-disabled" title="Não é possível excluir seu próprio usuário">
                                            🔒 Você
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">👥</div>
                    <h3>Nenhum usuário cadastrado</h3>
                    <p>Comece cadastrando o primeiro usuário do sistema</p>
                    <a href="cadastrar.php" class="btn btn-primary">Cadastrar Primeiro Usuário</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>