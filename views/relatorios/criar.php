<?php
require_once '../../controllers/AuthController.php';
AuthController::verificarAutenticacao();

$usuarioLogado = AuthController::getUsuarioLogado();

require_once '../../config/database.php';
require_once '../../models/Maquina.php';
require_once '../../models/Relatorio.php';

// Inicializar variáveis
$error = '';
$success = '';

// Buscar máquinas para o select
try {
    $db = DatabaseConfig::getConnection();
    $maquina = new Maquina($db);
    $stmt = $maquina->listar();
    $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $error = "Erro ao carregar máquinas: " . $e->getMessage();
    $maquinas = [];
}

// Processar formulário
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $relatorio = new Relatorio($db);
        
        // Validar dados do formulário
        $maquina_id = $_POST['maquina_id'] ?? '';
        $status_final = $_POST['status_final'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        
        if(empty($maquina_id) || empty($status_final) || empty($descricao)) {
            throw new Exception("Todos os campos obrigatórios devem ser preenchidos");
        }
        
        // Preparar checklist
        $checklist_data = [
            'power' => isset($_POST['power']) && $_POST['power'] == '1',
            'noise' => isset($_POST['noise']) && $_POST['noise'] == '1',
            'vibration' => isset($_POST['vibration']) && $_POST['vibration'] == '1'
        ];
        
        // Atribuir valores ao objeto
        $relatorio->maquina_id = $maquina_id;
        $relatorio->criado_por = $usuarioLogado['id'];
        $relatorio->checklist = $checklist_data;
        $relatorio->status_final = $status_final;
        $relatorio->descricao = $descricao;
        
        // Tentar criar o relatório
        if($relatorio->criar()) {
            $success = "Relatório criado com sucesso!";
            // Limpar formulário após sucesso
            $_POST = array();
        } else {
            throw new Exception("Não foi possível criar o relatório");
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
    <title>Novo Relatório - Sistema de Manutenção</title>
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
                    <li><a href="listar.php">Relatórios</a></li>
                    <?php if($usuarioLogado['cargo'] == 'GERENTE'): ?>
                        <li><a href="../usuarios/listar.php">Usuários</a></li>
                    <?php endif; ?>
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
            <h2>Novo Relatório de Manutenção</h2>
            <a href="listar.php" class="btn btn-secondary">← Voltar</a>
        </div>

        <?php if($error): ?>
            <div class="alert alert-error">
                <strong>Erro:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <br>
                <a href="listar.php" class="btn btn-sm btn-primary">Ver Relatórios</a>
                <a href="criar.php" class="btn btn-sm btn-secondary">Novo Relatório</a>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <div class="form-section">
                <h3>Informações da Máquina</h3>
                <div class="form-group">
                    <label for="maquina_id">Máquina *</label>
                    <select id="maquina_id" name="maquina_id" required>
                        <option value="">Selecione uma máquina</option>
                        <?php foreach($maquinas as $maq): ?>
                            <option value="<?php echo $maq['id']; ?>" 
                                <?php echo (isset($_POST['maquina_id']) && $_POST['maquina_id'] == $maq['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($maq['nome_maquina']); ?> - 
                                <?php echo htmlspecialchars($maq['nome_setor']); ?>
                                (<?php echo $maq['status']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <h3>Checklist de Verificação</h3>
                <p class="form-help">Marque os itens que foram verificados:</p>
                <div class="checklist-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="power" value="1" 
                            <?php echo (isset($_POST['power']) && $_POST['power'] == '1') ? 'checked' : ''; ?>>
                        <span>Energia/Funcionamento</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="noise" value="1"
                            <?php echo (isset($_POST['noise']) && $_POST['noise'] == '1') ? 'checked' : ''; ?>>
                        <span>Ruídos Anormais</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="vibration" value="1"
                            <?php echo (isset($_POST['vibration']) && $_POST['vibration'] == '1') ? 'checked' : ''; ?>>
                        <span>Vibração Excessiva</span>
                    </label>
                </div>
            </div>

            <div class="form-section">
                <h3>Status e Descrição</h3>
                
                <div class="form-group">
                    <label for="status_final">Status Final *</label>
                    <select id="status_final" name="status_final" required>
                        <option value="">Selecione o status</option>
                        <option value="Em Análise" <?php echo (isset($_POST['status_final']) && $_POST['status_final'] == 'Em Análise') ? 'selected' : ''; ?>>Em Análise</option>
                        <option value="Consertada" <?php echo (isset($_POST['status_final']) && $_POST['status_final'] == 'Consertada') ? 'selected' : ''; ?>>Consertada</option>
                        <option value="Aguardando Peça" <?php echo (isset($_POST['status_final']) && $_POST['status_final'] == 'Aguardando Peça') ? 'selected' : ''; ?>>Aguardando Peça</option>
                        <option value="Inutilizável" <?php echo (isset($_POST['status_final']) && $_POST['status_final'] == 'Inutilizável') ? 'selected' : ''; ?>>Inutilizável</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição do Problema/Solução *</label>
                    <textarea id="descricao" name="descricao" rows="6" required 
                        placeholder="Descreva detalhadamente o problema encontrado, as ações tomadas, peças substituídas e a solução aplicada..."><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : ''; ?></textarea>
                    <small class="form-help">Seja o mais detalhado possível para um bom histórico de manutenção.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Salvar Relatório</button>
                <button type="reset" class="btn btn-secondary">Limpar Formulário</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        // Validação simples do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const maquina = document.getElementById('maquina_id').value;
            const status = document.getElementById('status_final').value;
            const descricao = document.getElementById('descricao').value;
            
            if (!maquina || !status || !descricao.trim()) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
                return false;
            }
        });
    </script>
</body>
</html>