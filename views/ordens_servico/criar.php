<?php
// Verificação manual de login - SEM AuthController
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_cargo'] != 'GERENTE') {
    header("Location: ../../login.php");
    exit;
}

$usuarioLogado = [
    'id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['usuario_nome'],
    'cargo' => $_SESSION['usuario_cargo'],
    'setor_id' => $_SESSION['usuario_setor']
];

require_once '../../config/database.php';
require_once '../../models/OrdemServico.php';
require_once '../../models/Setor.php';
// ... resto do código permanece igual
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
        $ordemServico = new OrdemServico($db);
        
        // Validar dados do formulário
        $tipo_manutencao = $_POST['tipo_manutencao'] ?? '';
        $equipamento = $_POST['equipamento'] ?? '';
        $area = $_POST['area'] ?? '';
        $setor_id = $_POST['setor_id'] ?? '';
        $descricao_defeito = $_POST['descricao_defeito'] ?? '';
        $causa_defeito = $_POST['causa_defeito'] ?? '';
        $acao_corretiva = $_POST['acao_corretiva'] ?? '';
        $entrada_preventiva = $_POST['entrada_preventiva'] ?? 'NAO';
        $data_programada = $_POST['data_programada'] ?? '';
        $prioridade = $_POST['prioridade'] ?? 'MEDIA';
        $observacoes = $_POST['observacoes'] ?? '';
        
        // Validações
        if(empty($tipo_manutencao) || empty($equipamento) || empty($area) || empty($setor_id) || empty($descricao_defeito)) {
            throw new Exception("Todos os campos obrigatórios devem ser preenchidos");
        }
        
        // Atribuir valores ao objeto
        $ordemServico->tipo_manutencao = $tipo_manutencao;
        $ordemServico->equipamento = $equipamento;
        $ordemServico->area = $area;
        $ordemServico->setor_id = $setor_id;
        $ordemServico->descricao_defeito = $descricao_defeito;
        $ordemServico->causa_defeito = $causa_defeito;
        $ordemServico->acao_corretiva = $acao_corretiva;
        $ordemServico->entrada_preventiva = $entrada_preventiva;
        $ordemServico->data_programada = $data_programada ?: null;
        $ordemServico->status = 'ABERTA';
        $ordemServico->prioridade = $prioridade;
        $ordemServico->solicitado_por = $usuarioLogado['id'];
        $ordemServico->observacoes = $observacoes;
        
        // Tentar criar a O.S.
        if($ordemServico->criar()) {
            header("Location: listar.php?success=criada");
            exit;
        } else {
            throw new Exception("Não foi possível criar a Ordem de Serviço");
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
    <title>Nova Ordem de Serviço - Sistema de Manutenção</title>
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
                    <li><a href="../usuarios/listar.php">Usuários</a></li>
                    <li><a href="listar.php">O.S.</a></li>
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
            <h2>Nova Ordem de Serviço</h2>
            <a href="listar.php" class="btn btn-secondary">← Voltar</a>
        </div>

        <?php if($error): ?>
            <div class="alert alert-error">
                <strong>Erro:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <div class="form-section">
                <h3>Informações da Ordem de Serviço</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_manutencao">Tipo de Manutenção *</label>
                        <select id="tipo_manutencao" name="tipo_manutencao" required>
                            <option value="">Selecione o tipo</option>
                            <option value="CORRETIVA" <?php echo (isset($_POST['tipo_manutencao']) && $_POST['tipo_manutencao'] == 'CORRETIVA') ? 'selected' : ''; ?>>Corretiva</option>
                            <option value="PREVENTIVA" <?php echo (isset($_POST['tipo_manutencao']) && $_POST['tipo_manutencao'] == 'PREVENTIVA') ? 'selected' : ''; ?>>Preventiva</option>
                            <option value="PREDITIVA" <?php echo (isset($_POST['tipo_manutencao']) && $_POST['tipo_manutencao'] == 'PREDITIVA') ? 'selected' : ''; ?>>Preditiva</option>
                            <option value="MELHORIAS" <?php echo (isset($_POST['tipo_manutencao']) && $_POST['tipo_manutencao'] == 'MELHORIAS') ? 'selected' : ''; ?>>Melhorias</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="prioridade">Prioridade *</label>
                        <select id="prioridade" name="prioridade" required>
                            <option value="MEDIA" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] == 'MEDIA') ? 'selected' : ''; ?>>Média</option>
                            <option value="BAIXA" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] == 'BAIXA') ? 'selected' : ''; ?>>Baixa</option>
                            <option value="ALTA" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] == 'ALTA') ? 'selected' : ''; ?>>Alta</option>
                            <option value="URGENTE" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] == 'URGENTE') ? 'selected' : ''; ?>>Urgente</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="equipamento">Equipamento *</label>
                    <input type="text" id="equipamento" name="equipamento" 
                           value="<?php echo isset($_POST['equipamento']) ? htmlspecialchars($_POST['equipamento']) : ''; ?>" 
                           required placeholder="Ex: Kettens, Raschel, Circular...">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="area">Área *</label>
                        <select id="area" name="area" required>
                            <option value="">Selecione a área</option>
                            <option value="ELETRICA" <?php echo (isset($_POST['area']) && $_POST['area'] == 'ELETRICA') ? 'selected' : ''; ?>>Elétrica</option>
                            <option value="MECANICA" <?php echo (isset($_POST['area']) && $_POST['area'] == 'MECANICA') ? 'selected' : ''; ?>>Mecânica</option>
                            <option value="MARCENARIA" <?php echo (isset($_POST['area']) && $_POST['area'] == 'MARCENARIA') ? 'selected' : ''; ?>>Marcenaria</option>
                            <option value="FUNILARIA" <?php echo (isset($_POST['area']) && $_POST['area'] == 'FUNILARIA') ? 'selected' : ''; ?>>Funilaria</option>
                            <option value="TORNEARIA" <?php echo (isset($_POST['area']) && $_POST['area'] == 'TORNEARIA') ? 'selected' : ''; ?>>Tornearia</option>
                            <option value="PREDIAL" <?php echo (isset($_POST['area']) && $_POST['area'] == 'PREDIAL') ? 'selected' : ''; ?>>Predial</option>
                            <option value="OUTROS" <?php echo (isset($_POST['area']) && $_POST['area'] == 'OUTROS') ? 'selected' : ''; ?>>Outros</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="setor_id">Setor *</label>
                        <select id="setor_id" name="setor_id" required>
                            <option value="">Selecione o setor</option>
                            <?php foreach($setores as $setor): ?>
                                <option value="<?php echo $setor['id']; ?>" 
                                    <?php echo (isset($_POST['setor_id']) && $_POST['setor_id'] == $setor['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($setor['nome_setor']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="data_programada">Data Programada</label>
                    <input type="date" id="data_programada" name="data_programada" 
                           value="<?php echo isset($_POST['data_programada']) ? htmlspecialchars($_POST['data_programada']) : ''; ?>">
                </div>
            </div>

            <div class="form-section">
                <h3>Descrição do Problema</h3>
                
                <div class="form-group">
                    <label for="descricao_defeito">Descrição do Defeito *</label>
                    <textarea id="descricao_defeito" name="descricao_defeito" rows="4" required 
                              placeholder="Descreva detalhadamente o problema ou serviço necessário..."><?php echo isset($_POST['descricao_defeito']) ? htmlspecialchars($_POST['descricao_defeito']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="causa_defeito">Causa do Defeito (Porque ocorreu?)</label>
                    <textarea id="causa_defeito" name="causa_defeito" rows="3" 
                              placeholder="Descreva a causa raiz do problema..."><?php echo isset($_POST['causa_defeito']) ? htmlspecialchars($_POST['causa_defeito']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="acao_corretiva">Ação Corretiva (O que será feito?)</label>
                    <textarea id="acao_corretiva" name="acao_corretiva" rows="3" 
                              placeholder="Descreva a ação corretiva planejada..."><?php echo isset($_POST['acao_corretiva']) ? htmlspecialchars($_POST['acao_corretiva']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Esta ação deverá entrar para próxima preventiva?</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="entrada_preventiva" value="SIM" 
                                <?php echo (isset($_POST['entrada_preventiva']) && $_POST['entrada_preventiva'] == 'SIM') ? 'checked' : ''; ?>>
                            <span>Sim</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="entrada_preventiva" value="NAO" 
                                <?php echo (!isset($_POST['entrada_preventiva']) || $_POST['entrada_preventiva'] == 'NAO') ? 'checked' : ''; ?>>
                            <span>Não</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Observações Adicionais</h3>
                
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3" 
                              placeholder="Observações adicionais, informações complementares..."><?php echo isset($_POST['observacoes']) ? htmlspecialchars($_POST['observacoes']) : ''; ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Criar Ordem de Serviço</button>
                <button type="reset" class="btn btn-secondary">Limpar Formulário</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        // Validação do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const descricao = document.getElementById('descricao_defeito').value;
            
            if (!descricao.trim()) {
                e.preventDefault();
                alert('A descrição do defeito é obrigatória.');
                document.getElementById('descricao_defeito').focus();
                return false;
            }
        });

        // Data mínima para hoje
        document.getElementById('data_programada').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>