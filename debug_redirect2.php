<?php
echo "<h3>Debug 2 - Antes do AuthController</h3>";
require_once 'controllers/AuthController.php';
echo "✅ AuthController carregado<br>";

AuthController::verificarAutenticacao();
echo "✅ verificarAutenticacao passou<br>";

echo "Fim do script";
?>