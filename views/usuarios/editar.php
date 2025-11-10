<?php
require_once '../../controllers/AuthController.php';
AuthController::verificarAutenticacao();
AuthController::verificarPermissao('GERENTE');

$usuarioLogado = AuthController::getUsuarioLogado();

if(!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$usuario_id = (int)$_GET['id'];

require_once '../../config/database.php';
require_once '../../models/Usuario.php';
require_once '../../models/Setor.php';

// Inicializar variáveis
$error = '';
$success = '';

// Buscar dados do usuário
try {
    $db = DatabaseConfig::getConnection();
    $usuario = new Usuario($db);
    $usuario_data = $usuario->buscarPorId($usuario_id);
    
    if(!$usuario_data) {
        header("Location: listar.php?error=usuario_nao_encontrado");
        exit;
    }
    
} catch(Exception $e) {
    die("Erro: " . $e->getMessage());
}

// Buscar setores para o select
try {
    $setor = new Setor($db);
    $stmt_setores = $setor->listar();
    $setores = $stmt_setores->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $error = "Erro ao carregar setores: " . $e->getMessage();
    $setores = [];
}

// Processar formulário
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validar dados do formulário
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $cargo = $_POST['cargo'] ?? '';
        $setor_id = $_POST['setor_id'] ?? '';
        
        // Validações
        if(empty($nome) || empty($email) || empty($cargo)) {
            throw new Exception("Todos os campos obrigatórios devem ser preenchidos");
        }
        
        // Verificar se email já existe (excluindo o próprio usuário)
        if($email != $usuario_data['email'] && $usuario->emailExiste($email)) {
            throw new Exception("Este email já está cadastrado no sistema");
        }
        
        // Atribuir valores ao objeto
        $usuario->id = $usuario_id;
        $usuario->nome = $nome;
        $usuario->email = $email;
        $usuario->telefone = $telefone;
        $usuario->cargo = $cargo;
        $usuario->setor_id = $setor_id ?: null;
        
        // Tentar atualizar o usuário
        if($usuario->atualizar()) {
            $success = "Usuário atualizado com sucesso!";
            // Atualizar dados locais
            $usuario_data = $usuario->buscarPorId($usuario_id);
        } else {
            throw new Exception("Não foi possível atualizar o usuário");
        }
        
    } catch(Exception $e) {
        $error = "Erro: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Sistema de Manutenção</title>
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
                    <li><a href="listar.php">Usuários</a></li>
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
            <h2>Editar Usuário</h2>
            <a href="listar.php" class="btn btn-secondary">← Voltar</a>
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

        <form method="POST" class="form-container">
            <div class="form-section">
                <h3>Informações Pessoais</h3>
                
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" 
                           value="<?php echo htmlspecialchars($usuario_data['nome']); ?>" 
                           required placeholder="Digite o nome completo">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($usuario_data['email']); ?>" 
                           required placeholder="exemplo@empresa.com">
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" 
                           value="<?php echo htmlspecialchars($usuario_data['telefone']); ?>" 
                           placeholder="(11) 99999-9999">
                </div>
            </div>

            <div class="form-section">
                <h3>Permissões</h3>
                
                <div class="form-group">
                    <label for="cargo">Cargo *</label>
                    <select id="cargo" name="cargo" required>
                        <option value="">Selecione o cargo</option>
                        <option value="OPERACIONAL" <?php echo ($usuario_data['cargo'] == 'OPERACIONAL') ? 'selected' : ''; ?>>Operacional</option>
                        <option value="GERENTE" <?php echo ($usuario_data['cargo'] == 'GERENTE') ? 'selected' : ''; ?>>Gerente</option>
                    </select>
                    <small class="form-help">
                        <strong>Operacional:</strong> Acesso básico ao sistema<br>
                        <strong>Gerente:</strong> Acesso completo, incluindo gerenciamento de usuários
                    </small>
                </div>

                <div class="form-group">
                    <label for="setor_id">Setor</label>
                    <select id="setor_id" name="setor_id">
                        <option value="">Selecione um setor (opcional)</option>
                        <?php foreach($setores as $setor): ?>
                            <option value="<?php echo $setor['id']; ?>" 
                                <?php echo ($usuario_data['setor_id'] == $setor['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($setor['nome_setor']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-info">
                    <h4>Informações do Usuário</h4>
                    <p><strong>ID:</strong> <?php echo $usuario_data['id']; ?></p>
                    <p><strong>Data de Cadastro:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario_data['created_at'])); ?></p>
                    <?php if($usuario_data['id'] == $usuarioLogado['id']): ?>
                        <div class="alert alert-info">
                            <strong>Observação:</strong> Você está editando seu próprio usuário.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Atualizar Usuário</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });
    </script>
</body>
</html>