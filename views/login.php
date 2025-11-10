<?php
session_start();
if(isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Manutenção - Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <h1>Sistema de Controle de Manutenção</h1>
            <p>Faça login para acessar o sistema</p>
        </div>
        
        <form method="POST" action="../controllers/login_process.php" class="login-form">
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    $errors = [
                        '1' => 'Email ou senha incorretos.',
                        '2' => 'Por favor, preencha todos os campos.'
                    ];
                    echo $errors[$_GET['error']] ?? 'Erro desconhecido.';
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
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