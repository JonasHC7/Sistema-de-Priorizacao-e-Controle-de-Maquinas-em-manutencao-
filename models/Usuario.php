<?php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nome;
    public $email;
    public $telefone;
    public $senha;
    public $cargo;
    public $setor_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $senha) {
        $query = "SELECT id, nome, email, senha, cargo, setor_id 
                  FROM " . $this->table_name . " 
                  WHERE email = :email AND senha = :senha";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->email = $row['email'];
            $this->cargo = $row['cargo'];
            $this->setor_id = $row['setor_id'];
            return true;
        }
        return false;
    }

    public function listar() {
        $query = "SELECT 
                    u.id,
                    u.nome,
                    u.email,
                    u.telefone,
                    u.cargo,
                    u.setor_id,
                    u.created_at,
                    s.nome_setor
                  FROM " . $this->table_name . " u
                  LEFT JOIN setores s ON u.setor_id = s.id
                  ORDER BY u.nome ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $exception) {
            throw new Exception("Erro ao listar usuários: " . $exception->getMessage());
        }
    }

    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome, email, telefone, senha, cargo, setor_id, created_at)
                  VALUES 
                  (:nome, :email, :telefone, :senha, :cargo, :setor_id, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpar e validar dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->senha = htmlspecialchars(strip_tags($this->senha));
        $this->cargo = htmlspecialchars(strip_tags($this->cargo));
        $this->setor_id = (int)$this->setor_id;
        
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":senha", $this->senha);
        $stmt->bindParam(":cargo", $this->cargo);
        $stmt->bindParam(":setor_id", $this->setor_id);
        
        try {
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch(PDOException $exception) {
            throw new Exception("Erro ao criar usuário: " . $exception->getMessage());
        }
    }

    public function emailExiste($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function buscarPorId($id) {
        $query = "SELECT 
                    u.*,
                    s.nome_setor
                  FROM " . $this->table_name . " u
                  LEFT JOIN setores s ON u.setor_id = s.id
                  WHERE u.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Atribuir valores às propriedades
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->email = $row['email'];
            $this->telefone = $row['telefone'];
            $this->cargo = $row['cargo'];
            $this->setor_id = $row['setor_id'];
            $this->created_at = $row['created_at'];
            
            return $row;
        }
        return false;
    }

    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome = :nome, email = :email, telefone = :telefone, 
                      cargo = :cargo, setor_id = :setor_id
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpar e validar dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->cargo = htmlspecialchars(strip_tags($this->cargo));
        $this->setor_id = (int)$this->setor_id;
        
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":cargo", $this->cargo);
        $stmt->bindParam(":setor_id", $this->setor_id);
        $stmt->bindParam(":id", $this->id);
        
        try {
            return $stmt->execute();
        } catch(PDOException $exception) {
            throw new Exception("Erro ao atualizar usuário: " . $exception->getMessage());
        }
    }

    public function excluir($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        try {
            return $stmt->execute();
        } catch(PDOException $exception) {
            throw new Exception("Erro ao excluir usuário: " . $exception->getMessage());
        }
    }

    public function contarTotal() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function contarPorCargo($cargo) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE cargo = :cargo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cargo", $cargo);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>