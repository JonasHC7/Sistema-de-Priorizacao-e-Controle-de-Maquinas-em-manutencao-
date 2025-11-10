<?php
require_once '../models/Usuario.php';
require_once '../controllers/AuthController.php';

// Iniciar sessão
AuthController::iniciarSessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    
    // Validar inputs
    if (empty($email) || empty($senha)) {
        $_SESSION['erro_login'] = 'Por favor, preencha todos os campos';
        header('Location: ../login.php');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro_login'] = 'E-mail inválido';
        header('Location: ../login.php');
        exit;
    }
    
    try {
        // ✅ CORREÇÃO: Criar conexão PDO diretamente
        $host = 'localhost';
        $dbname = 'seu_banco'; // substitua pelo nome do seu banco
        $username = 'root';    // substitua pelo seu usuário
        $password = '';        // substitua pela sua senha
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Buscar usuário no banco de dados
        $usuarioModel = new Usuario($pdo);
        $usuario = $usuarioModel->buscarPorEmail($email);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            AuthController::login(
                $usuario['id'],
                $usuario['nome'],
                $usuario['cargo'],
                $usuario['setor_id'],
                $usuario['email']
            );
            
        } else {
            $_SESSION['erro_login'] = 'E-mail ou senha incorretos';
            header('Location: ../login.php');
            exit;
        }
        
    } catch (Exception $e) {
        $_SESSION['erro_login'] = 'Erro ao processar login: ' . $e->getMessage();
        header('Location: ../login.php');
        exit;
    }
    
} else {
    header('Location: ../login.php');
    exit;
}
?>