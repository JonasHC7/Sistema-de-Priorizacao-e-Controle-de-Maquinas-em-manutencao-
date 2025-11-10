<?php
class Setor {
    private $conn;
    private $table_name = "setores";

    public $id;
    public $nome_setor;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $query = "SELECT id, nome_setor FROM " . $this->table_name . " ORDER BY nome_setor ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $exception) {
            throw new Exception("Erro ao listar setores: " . $exception->getMessage());
        }
    }

    public function buscarPorId($id) {
        $query = "SELECT id, nome_setor FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
}
?>