<?php
class OrdemServico {
    private $conn;
    private $table_name = "ordens_servico";

    public $id;
    public $numero_os;
    public $tipo_manutencao;
    public $equipamento;
    public $area;
    public $setor_id;
    public $descricao_defeito;
    public $causa_defeito;
    public $acao_corretiva;
    public $entrada_preventiva;
    public $data_programada;
    public $status;
    public $prioridade;
    public $solicitado_por;
    public $recebido_por;
    public $aceito_por;
    public $analisado_por;
    public $data_abertura;
    public $data_recebimento;
    public $data_aceite;
    public $data_analise;
    public $data_conclusao;
    public $observacoes;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $query = "SELECT 
                    os.*,
                    s.nome_setor,
                    u_solicitante.nome as solicitante_nome,
                    u_recebedor.nome as recebedor_nome,
                    u_aceitador.nome as aceitador_nome,
                    u_analisador.nome as analisador_nome
                  FROM " . $this->table_name . " os
                  LEFT JOIN setores s ON os.setor_id = s.id
                  LEFT JOIN usuarios u_solicitante ON os.solicitado_por = u_solicitante.id
                  LEFT JOIN usuarios u_recebedor ON os.recebido_por = u_recebedor.id
                  LEFT JOIN usuarios u_aceitador ON os.aceito_por = u_aceitador.id
                  LEFT JOIN usuarios u_analisador ON os.analisado_por = u_analisador.id
                  ORDER BY os.data_abertura DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $exception) {
            throw new Exception("Erro ao listar ordens de serviço: " . $exception->getMessage());
        }
    }

    public function criar() {
        // Gerar número da OS automaticamente
        $this->numero_os = $this->gerarNumeroOS();
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (numero_os, tipo_manutencao, equipamento, area, setor_id, 
                   descricao_defeito, causa_defeito, acao_corretiva, entrada_preventiva,
                   data_programada, status, prioridade, solicitado_por, observacoes)
                  VALUES 
                  (:numero_os, :tipo_manutencao, :equipamento, :area, :setor_id,
                   :descricao_defeito, :causa_defeito, :acao_corretiva, :entrada_preventiva,
                   :data_programada, :status, :prioridade, :solicitado_por, :observacoes)";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpar e validar dados
        $this->tipo_manutencao = htmlspecialchars(strip_tags($this->tipo_manutencao));
        $this->equipamento = htmlspecialchars(strip_tags($this->equipamento));
        $this->area = htmlspecialchars(strip_tags($this->area));
        $this->descricao_defeito = htmlspecialchars(strip_tags($this->descricao_defeito));
        $this->causa_defeito = htmlspecialchars(strip_tags($this->causa_defeito));
        $this->acao_corretiva = htmlspecialchars(strip_tags($this->acao_corretiva));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        
        $stmt->bindParam(":numero_os", $this->numero_os);
        $stmt->bindParam(":tipo_manutencao", $this->tipo_manutencao);
        $stmt->bindParam(":equipamento", $this->equipamento);
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":setor_id", $this->setor_id);
        $stmt->bindParam(":descricao_defeito", $this->descricao_defeito);
        $stmt->bindParam(":causa_defeito", $this->causa_defeito);
        $stmt->bindParam(":acao_corretiva", $this->acao_corretiva);
        $stmt->bindParam(":entrada_preventiva", $this->entrada_preventiva);
        $stmt->bindParam(":data_programada", $this->data_programada);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":prioridade", $this->prioridade);
        $stmt->bindParam(":solicitado_por", $this->solicitado_por);
        $stmt->bindParam(":observacoes", $this->observacoes);
        
        try {
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch(PDOException $exception) {
            throw new Exception("Erro ao criar ordem de serviço: " . $exception->getMessage());
        }
    }

    private function gerarNumeroOS() {
        $ano = date('Y');
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE YEAR(data_abertura) = :ano";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ano", $ano);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $numero = $row['total'] + 1;
        return "OS-" . $ano . "-" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function buscarPorId($id) {
        $query = "SELECT 
                    os.*,
                    s.nome_setor,
                    u_solicitante.nome as solicitante_nome,
                    u_recebedor.nome as recebedor_nome,
                    u_aceitador.nome as aceitador_nome,
                    u_analisador.nome as analisador_nome
                  FROM " . $this->table_name . " os
                  LEFT JOIN setores s ON os.setor_id = s.id
                  LEFT JOIN usuarios u_solicitante ON os.solicitado_por = u_solicitante.id
                  LEFT JOIN usuarios u_recebedor ON os.recebido_por = u_recebedor.id
                  LEFT JOIN usuarios u_aceitador ON os.aceito_por = u_aceitador.id
                  LEFT JOIN usuarios u_analisador ON os.analisado_por = u_analisador.id
                  WHERE os.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function atualizarStatus($id, $status, $usuario_id = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status";
        
        // Adicionar campos baseados no status
        switch($status) {
            case 'EM_ANDAMENTO':
                $query .= ", recebido_por = :usuario_id, data_recebimento = NOW()";
                break;
            case 'CONCLUIDA':
                $query .= ", aceito_por = :usuario_id, data_aceite = NOW(), data_conclusao = NOW()";
                break;
            case 'CANCELADA':
                $query .= ", analisado_por = :usuario_id, data_analise = NOW()";
                break;
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        
        if($usuario_id) {
            $stmt->bindParam(":usuario_id", $usuario_id);
        }
        
        try {
            return $stmt->execute();
        } catch(PDOException $exception) {
            throw new Exception("Erro ao atualizar status: " . $exception->getMessage());
        }
    }

    public function contarTotal() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function contarPorStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

        public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET tipo_manutencao = :tipo_manutencao, 
                      equipamento = :equipamento, 
                      area = :area, 
                      setor_id = :setor_id,
                      descricao_defeito = :descricao_defeito,
                      causa_defeito = :causa_defeito,
                      acao_corretiva = :acao_corretiva,
                      entrada_preventiva = :entrada_preventiva,
                      data_programada = :data_programada,
                      prioridade = :prioridade,
                      observacoes = :observacoes
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpar dados
        $this->tipo_manutencao = htmlspecialchars(strip_tags($this->tipo_manutencao));
        $this->equipamento = htmlspecialchars(strip_tags($this->equipamento));
        $this->area = htmlspecialchars(strip_tags($this->area));
        $this->descricao_defeito = htmlspecialchars(strip_tags($this->descricao_defeito));
        $this->causa_defeito = htmlspecialchars(strip_tags($this->causa_defeito));
        $this->acao_corretiva = htmlspecialchars(strip_tags($this->acao_corretiva));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
        
        $stmt->bindParam(":tipo_manutencao", $this->tipo_manutencao);
        $stmt->bindParam(":equipamento", $this->equipamento);
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":setor_id", $this->setor_id);
        $stmt->bindParam(":descricao_defeito", $this->descricao_defeito);
        $stmt->bindParam(":causa_defeito", $this->causa_defeito);
        $stmt->bindParam(":acao_corretiva", $this->acao_corretiva);
        $stmt->bindParam(":entrada_preventiva", $this->entrada_preventiva);
        $stmt->bindParam(":data_programada", $this->data_programada);
        $stmt->bindParam(":prioridade", $this->prioridade);
        $stmt->bindParam(":observacoes", $this->observacoes);
        $stmt->bindParam(":id", $this->id);
        
        try {
            return $stmt->execute();
        } catch(PDOException $exception) {
            throw new Exception("Erro ao atualizar ordem de serviço: " . $exception->getMessage());
        }
    }
    
}

?>
