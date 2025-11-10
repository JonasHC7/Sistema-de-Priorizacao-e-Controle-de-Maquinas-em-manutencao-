<?php
session_start();

// Verificar se já está logado
if(isset($_SESSION['usuario_id'])) {
    header("Location: views/dashboard.php");
    exit;
}

// Processar o formulário de login
$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    
    if(empty($email) || empty($senha)) {
        $error = "Por favor, preencha todos os campos";
    } else {
        try {
            // Incluir e usar a configuração do banco
            require_once 'config/database.php';
            require_once 'models/Usuario.php';
            
            // Obter conexão
            $database = new DatabaseConfig();
            $db = $database->getConnection();
            
            // Verificar login
            $usuario = new Usuario($db);
            
            if($usuario->login($email, $senha)) {
                // Login bem-sucedido
                $_SESSION['usuario_id'] = $usuario->id;
                $_SESSION['usuario_nome'] = $usuario->nome;
                $_SESSION['usuario_cargo'] = $usuario->cargo;
                $_SESSION['usuario_setor'] = $usuario->setor_id;
                
                header("Location: views/dashboard.php");
                exit;
            } else {
                $error = "Email ou senha incorretos";
            }
            
        } catch(Exception $e) {
            $error = "Erro no sistema: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Manutenção - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <h1>Sistema de Controle de Manutenção</h1>
            <p>Faça login para acessar o sistema</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
        
        <div class="login-demo">
            <h3>Credenciais de Teste:</h3>
            <p><strong>Gerente:</strong> gerente@example.com / 123</p>
            <p><strong>Operador:</strong> operador@example.com / 321</p>
        </div>
    </div>
</body>
</html>