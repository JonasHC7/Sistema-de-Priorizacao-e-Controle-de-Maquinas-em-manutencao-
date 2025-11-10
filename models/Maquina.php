<?php
class Maquina {
    private $conn;
    private $table_name = "maquinas";

    public $id;
    public $nome_maquina;
    public $tipo_id;
    public $setor_id;
    public $status;
    public $saude;
    public $assigned_user;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $query = "SELECT 
                    m.id,
                    m.nome_maquina,
                    m.tipo_id,
                    m.setor_id,
                    m.status,
                    m.saude,
                    m.assigned_user,
                    m.updated_at,
                    t.nome_tipo,
                    s.nome_setor,
                    u.nome as usuario_nome
                  FROM " . $this->table_name . " m
                  LEFT JOIN tipos_maquinas t ON m.tipo_id = t.id
                  LEFT JOIN setores s ON m.setor_id = s.id
                  LEFT JOIN usuarios u ON m.assigned_user = u.id
                  ORDER BY m.id ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $exception) {
            throw new Exception("Erro ao listar máquinas: " . $exception->getMessage());
        }
    }

    public function buscarPorId($id) {
        $query = "SELECT 
                    m.*,
                    t.nome_tipo,
                    s.nome_setor,
                    u.nome as usuario_nome
                  FROM " . $this->table_name . " m
                  LEFT JOIN tipos_maquinas t ON m.tipo_id = t.id
                  LEFT JOIN setores s ON m.setor_id = s.id
                  LEFT JOIN usuarios u ON m.assigned_user = u.id
                  WHERE m.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function contarPorStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function contarPorSaude($saude) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE saude = :saude";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":saude", $saude);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function atualizarStatus($id, $status, $saude, $usuario_id = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, saude = :saude, assigned_user = :usuario_id, updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":saude", $saude);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":id", $id);
        
        try {
            return $stmt->execute();
        } catch(PDOException $exception) {
            throw new Exception("Erro ao atualizar máquina: " . $exception->getMessage());
        }
    }
}
?>