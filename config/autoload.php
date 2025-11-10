<?php
// Autoload para carregar classes automaticamente
spl_autoload_register(function ($class_name) {
    $directories = [
        'controllers/',
        'models/',
        'config/'
    ];
    
    foreach ($directories as $directory) {
        $file = __DIR__ . '/../' . $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Função helper para verificar autenticação
function verificarAutenticacao() {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

// Função helper para verificar permissões
function verificarPermissao($cargoPermitido) {
    verificarAutenticacao();
    if ($_SESSION['usuario_cargo'] != $cargoPermitido) {
        header("Location: ../views/dashboard.php");
        exit;
    }
}
?>