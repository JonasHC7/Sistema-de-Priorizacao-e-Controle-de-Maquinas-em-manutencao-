<?php
// Este arquivo é incluído dentro do contexto de um relatório específico
// As variáveis $rel estão disponíveis do loop principal
?>
<div class="report-print-page">
    <!-- Cabeçalho do relatório -->
    <div class="print-header">
        <div class="company-info">
            <h1>Relatório de Manutenção</h1>
            <p><strong>Empresa:</strong> Sistema de Controle de Manutenção</p>
            <p><strong>Data de Emissão:</strong> <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <div class="report-print-info">
            <p><strong>Nº do Relatório:</strong> #<?php echo str_pad($rel['id'], 4, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Status:</strong> <?php echo $rel['status_final']; ?></p>
        </div>
    </div>

    <!-- Informações da máquina -->
    <div class="print-section">
        <h2>Informações da Máquina</h2>
        <table class="print-table">
            <tr>
                <td width="30%"><strong>Máquina:</strong></td>
                <td><?php echo htmlspecialchars($rel['nome_maquina']); ?></td>
            </tr>
            <tr>
                <td><strong>Tipo:</strong></td>
                <td><?php echo htmlspecialchars($rel['nome_tipo']); ?></td>
            </tr>
            <tr>
                <td><strong>Setor:</strong></td>
                <td><?php echo htmlspecialchars($rel['nome_setor']); ?></td>
            </tr>
            <tr>
                <td><strong>Data do Relatório:</strong></td>
                <td><?php echo date('d/m/Y H:i', strtotime($rel['criado_em'])); ?></td>
            </tr>
            <tr>
                <td><strong>Técnico Responsável:</strong></td>
                <td><?php echo htmlspecialchars($rel['usuario_nome']); ?></td>
            </tr>
        </table>
    </div>

    <!-- Checklist -->
    <div class="print-section">
        <h2>Checklist de Verificação</h2>
        <?php 
        $checklist = json_decode($rel['checklist'], true);
        if($checklist): 
        ?>
            <table class="print-table checklist-table">
                <?php foreach($checklist as $item => $value): ?>
                    <tr>
                        <td width="70%"><?php echo ucfirst($item); ?></td>
                        <td width="30%" class="<?php echo $value ? 'text-success' : 'text-danger'; ?>">
                            <strong><?php echo $value ? '✓ CONFORME' : '✗ NÃO CONFORME'; ?></strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Nenhum checklist disponível</p>
        <?php endif; ?>
    </div>

    <!-- Descrição do Problema/Solução -->
    <div class="print-section">
        <h2>Descrição do Problema e Solução</h2>
        <div class="description-print">
            <?php echo nl2br(htmlspecialchars($rel['descricao'])); ?>
        </div>
    </div>

    <!-- Assinaturas -->
    <div class="print-section signatures">
        <h2>Assinaturas</h2>
        <div class="signature-container">
            <div class="signature-box">
                <div class="signature-line"></div>
                <p>Técnico Responsável</p>
                <p><strong><?php echo htmlspecialchars($rel['usuario_nome']); ?></strong></p>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <p>Supervisor</p>
                <p>_________________________</p>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <p>Gestor do Setor</p>
                <p>_________________________</p>
            </div>
        </div>
    </div>

    <!-- Observações -->
    <div class="print-section">
        <h2>Observações</h2>
        <div class="observations">
            <p>Relatório gerado automaticamente pelo Sistema de Controle de Manutenção</p>
            <p>Data de impressão: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </div>
</div>