<?php
session_start();
echo "<h3>Debug - Sessão e Redirecionamento</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "usuario_id na sessão: " . ($_SESSION['usuario_id'] ?? 'NÃO EXISTE') . "<br>";
echo "usuario_cargo na sessão: " . ($_SESSION['usuario_cargo'] ?? 'NÃO EXISTE') . "<br>";
echo "Script atual: " . $_SERVER['PHP_SELF'] . "<br>";

// Testar redirecionamento
echo "<h4>Teste de redirecionamento:</h4>";
echo "<a href='views/ordens_servico/listar.php'>Acessar Lista O.S. (pode causar redirect)</a><br>";
echo "<a href='debug_redirect2.php'>Teste 2</a>";
?>