<?php
class Relatorio {
    private $conn;
    private $table_name = "relatorios";

    public $id;
    public $maquina_id;
    public $criado_por;
    public $criado_em;
    public $checklist;
    public $status_final;
    public $descricao;
    public $notificado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $query = "SELECT 
                    r.id,
                    r.maquina_id,
                    r.criado_por,
                    r.criado_em,
                    r.checklist,
                    r.status_final,
                    r.descricao,
                    r.notificado,
                    m.nome_maquina,
                    t.nome_tipo,
                    s.nome_setor,
                    u.nome as usuario_nome
                  FROM " . $this->table_name . " r
                  LEFT JOIN maquinas m ON r.maquina_id = m.id
                  LEFT JOIN tipos_maquinas t ON m.tipo_id = t.id
                  LEFT JOIN setores s ON m.setor_id = s.id
                  LEFT JOIN usuarios u ON r.criado_por = u.id
                  ORDER BY r.criado_em DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $exception) {
            throw new Exception("Erro ao listar relatórios: " . $exception->getMessage());
        }
    }

    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (maquina_id, criado_por, checklist, status_final, descricao, criado_em)
                  VALUES 
                  (:maquina_id, :criado_por, :checklist, :status_final, :descricao, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpar e validar dados
        $this->maquina_id = (int)$this->maquina_id;
        $this->criado_por = (int)$this->criado_por;
        $this->status_final = htmlspecialchars(strip_tags($this->status_final));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        
        // Verificar se o checklist é um JSON válido
        if (is_string($this->checklist)) {
            $checklist_data = $this->checklist;
        } else {
            $checklist_data = json_encode($this->checklist);
        }
        
        $stmt->bindParam(":maquina_id", $this->maquina_id);
        $stmt->bindParam(":criado_por", $this->criado_por);
        $stmt->bindParam(":checklist", $checklist_data);
        $stmt->bindParam(":status_final", $this->status_final);
        $stmt->bindParam(":descricao", $this->descricao);
        
        try {
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch(PDOException $exception) {
            throw new Exception("Erro ao criar relatório: " . $exception->getMessage());
        }
    }

    public function buscarPorId($id) {
        $query = "SELECT 
                    r.*,
                    m.nome_maquina,
                    t.nome_tipo,
                    s.nome_setor,
                    u.nome as usuario_nome,
                    u.cargo as usuario_cargo
                  FROM " . $this->table_name . " r
                  LEFT JOIN maquinas m ON r.maquina_id = m.id
                  LEFT JOIN tipos_maquinas t ON m.tipo_id = t.id
                  LEFT JOIN setores s ON m.setor_id = s.id
                  LEFT JOIN usuarios u ON r.criado_por = u.id
                  WHERE r.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Atribuir valores às propriedades
            $this->id = $row['id'];
            $this->maquina_id = $row['maquina_id'];
            $this->criado_por = $row['criado_por'];
            $this->criado_em = $row['criado_em'];
            $this->checklist = $row['checklist'];
            $this->status_final = $row['status_final'];
            $this->descricao = $row['descricao'];
            $this->notificado = $row['notificado'];
            
            return $row;
        }
        return false;
    }

    public function contarTotal() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function excluir($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        try {
            return $stmt->execute();
        } catch(PDOException $exception) {
            throw new Exception("Erro ao excluir relatório: " . $exception->getMessage());
        }
    }
}
?>