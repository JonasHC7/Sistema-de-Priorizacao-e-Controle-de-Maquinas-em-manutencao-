<?php
class AuthController {
    
    private static $paginasPublicas = [
        'login.php', 
        'logout.php', 
        'cadastro.php',
        'recuperar-senha.php',
        'nova-senha.php'
    ];
    
    public static function iniciarSessao() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function verificarAutenticacao() {
        self::iniciarSessao();
        
        $paginaAtual = basename($_SERVER['PHP_SELF']);
        
        // Se é uma página pública, não verifica autenticação
        if (in_array($paginaAtual, self::$paginasPublicas)) {
            return true;
        }
        
        // Se não está logado, redireciona para login
        if (!isset($_SESSION['usuario_id'])) {
            self::redirecionarParaLogin();
        }
        
        return true;
    }
    
    public static function redirecionarParaLogin() {
        $urlLogin = 'login.php';
        
        // Adiciona a página atual como parâmetro para redirecionamento pós-login
        $paginaAtual = basename($_SERVER['PHP_SELF']);
        if (!in_array($paginaAtual, self::$paginasPublicas)) {
            $urlLogin .= '?redirect=' . urlencode($paginaAtual);
        }
        
        if (!headers_sent()) {
            header('Location: ' . $urlLogin);
            exit;
        } else {
            echo '<script>window.location.href = "' . $urlLogin . '";</script>';
            exit;
        }
    }
    
    public static function getUsuarioLogado() {
        self::iniciarSessao();
        
        if (isset($_SESSION['usuario_id'])) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nome' => $_SESSION['usuario_nome'],
                'cargo' => $_SESSION['usuario_cargo'],
                'setor_id' => $_SESSION['usuario_setor'],
                'email' => $_SESSION['usuario_email'] ?? null
            ];
        }
        return null;
    }
    
    public static function estaLogado() {
        self::iniciarSessao();
        return isset($_SESSION['usuario_id']);
    }
    
    // ✅ MÉTODOS DO GERENTE ADICIONADOS AQUI
    public static function isGerente() {
        $usuario = self::getUsuarioLogado();
        return $usuario && $usuario['cargo'] === 'gerente';
    }
    
    public static function verificarGerente() {
        self::iniciarSessao();
        
        if (!self::isGerente()) {
            $_SESSION['erro'] = 'Acesso negado. Apenas gerentes podem realizar esta ação.';
            header('Location: index.php');
            exit;
        }
    }
    
    public static function login($usuarioId, $usuarioNome, $usuarioCargo, $usuarioSetor, $usuarioEmail = null) {
        self::iniciarSessao();
        
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_nome'] = $usuarioNome;
        $_SESSION['usuario_cargo'] = $usuarioCargo;
        $_SESSION['usuario_setor'] = $usuarioSetor;
        
        if ($usuarioEmail) {
            $_SESSION['usuario_email'] = $usuarioEmail;
        }
        
        // Regenera o ID da sessão para prevenir fixation attacks
        session_regenerate_id(true);
        
        // Verifica se há redirecionamento pendente
        if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
            $paginaRedirect = urldecode($_GET['redirect']);
            // Valida se a página é segura para redirecionamento
            if (self::validarPaginaRedirect($paginaRedirect)) {
                self::redirecionar($paginaRedirect);
            }
        }
        
        // Redireciona padrão para a página inicial
        self::redirecionar('index.php');
    }
    
    public static function logout() {
        self::iniciarSessao();
        
        // Limpa todas as variáveis de sessão
        $_SESSION = [];
        
        // Destrói a sessão
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params['path'], 
                $params['domain'], 
                $params['secure'], 
                $params['httponly']
            );
        }
        
        session_destroy();
        
        // Redireciona para a página de login
        self::redirecionar('login.php');
    }
    
    public static function verificarPermissao($cargosPermitidos) {
        $usuario = self::getUsuarioLogado();
        
        if ($usuario === null) {
            return false;
        }
        
        if (is_array($cargosPermitidos)) {
            return in_array($usuario['cargo'], $cargosPermitidos);
        }
        
        return $usuario['cargo'] === $cargosPermitidos;
    }
    
    public static function requererPermissao($cargosPermitidos, $paginaErro = 'acesso-negado.php') {
        if (!self::verificarPermissao($cargosPermitidos)) {
            self::redirecionar($paginaErro);
        }
    }
    
    public static function redirecionar($pagina) {
        if (!headers_sent()) {
            header('Location: ' . $pagina);
            exit;
        } else {
            echo '<script>window.location.href = "' . $pagina . '";</script>';
            exit;
        }
    }
    
    private static function validarPaginaRedirect($pagina) {
        // Lista de páginas não permitidas para redirecionamento
        $paginasInvalidas = [
            'login.php',
            'logout.php',
            'cadastro.php'
        ];
        
        $pagina = basename($pagina);
        
        // Verifica se a página não está na lista de inválidas
        return !in_array($pagina, $paginasInvalidas);
    }
    
    public static function adicionarPaginaPublica($pagina) {
        if (!in_array($pagina, self::$paginasPublicas)) {
            self::$paginasPublicas[] = $pagina;
        }
    }
    
    public static function getPaginasPublicas() {
        return self::$paginasPublicas;
    }
}
?>