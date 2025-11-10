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

// Buscar setores para o select
try {
    $db = DatabaseConfig::getConnection();
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
        $usuario = new Usuario($db);
        
        // Validar dados do formulário
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        $cargo = $_POST['cargo'] ?? '';
        $setor_id = $_POST['setor_id'] ?? '';
        
        // Validações
        if(empty($nome) || empty($email) || empty($senha) || empty($cargo)) {
            throw new Exception("Todos os campos obrigatórios devem ser preenchidos");
        }
        
        if($senha !== $confirmar_senha) {
            throw new Exception("As senhas não coincidem");
        }
        
        if(strlen($senha) < 3) {
            throw new Exception("A senha deve ter pelo menos 3 caracteres");
        }
        
        // Verificar se email já existe
        if($usuario->emailExiste($email)) {
            throw new Exception("Este email já está cadastrado no sistema");
        }
        
        // Atribuir valores ao objeto
        $usuario->nome = $nome;
        $usuario->email = $email;
        $usuario->telefone = $telefone;
        $usuario->senha = $senha; // Em produção, usar password_hash()
        $usuario->cargo = $cargo;
        $usuario->setor_id = $setor_id ?: null;
        
        // Tentar criar o usuário
        if($usuario->criar()) {
            header("Location: listar.php?success=cadastrado");
            exit;
        } else {
            throw new Exception("Não foi possível criar o usuário");
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
    <title>Cadastrar Usuário - Sistema de Manutenção</title>
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
            <h2>Cadastrar Novo Usuário</h2>
            <a href="listar.php" class="btn btn-secondary">← Voltar</a>
        </div>

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
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" 
                           required placeholder="Digite o nome completo">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required placeholder="exemplo@empresa.com">
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" 
                           value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>" 
                           placeholder="(11) 99999-9999">
                </div>
            </div>

            <div class="form-section">
                <h3>Credenciais e Permissões</h3>
                
                <div class="form-group">
                    <label for="senha">Senha *</label>
                    <input type="password" id="senha" name="senha" required 
                           placeholder="Mínimo 3 caracteres" minlength="3">
                    <small class="form-help">A senha será armazenada em texto simples. Em produção, use criptografia.</small>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha *</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required 
                           placeholder="Digite a senha novamente">
                </div>

                <div class="form-group">
                    <label for="cargo">Cargo *</label>
                    <select id="cargo" name="cargo" required>
                        <option value="">Selecione o cargo</option>
                        <option value="OPERACIONAL" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'OPERACIONAL') ? 'selected' : ''; ?>>Operacional</option>
                        <option value="GERENTE" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'GERENTE') ? 'selected' : ''; ?>>Gerente</option>
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
                                <?php echo (isset($_POST['setor_id']) && $_POST['setor_id'] == $setor['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($setor['nome_setor']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Cadastrar Usuário</button>
                <button type="reset" class="btn btn-secondary">Limpar Formulário</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        // Validação do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;
            
            if (senha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem. Por favor, verifique.');
                document.getElementById('senha').focus();
                return false;
            }
            
            if (senha.length < 3) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 3 caracteres.');
                document.getElementById('senha').focus();
                return false;
            }
        });

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