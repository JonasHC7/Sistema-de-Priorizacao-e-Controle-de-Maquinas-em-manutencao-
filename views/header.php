<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Manutenção</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">Sistema de Manutenção</h1>
            <nav class="nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="maquinas/listar.php">Máquinas</a></li>
                    <li><a href="relatorios/listar.php">Relatórios</a></li>
                    <?php if($_SESSION['usuario_cargo'] == 'GERENTE'): ?>
                        <li><a href="usuarios/listar.php">Usuários</a></li>
                    <?php endif; ?>
                    <li><a href="../logout.php" class="logout-btn">Sair</a></li>
                </ul>
            </nav>
            <div class="user-info">
                <span>Olá, <?php echo $_SESSION['usuario_nome']; ?> (<?php echo $_SESSION['usuario_cargo']; ?>)</span>
            </div>
        </div>
    </header>
    <main class="main-content"></main>