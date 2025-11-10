<?php
class DatabaseConfig {
    public static function getConnection() {
        $host = "127.0.0.1";
        $dbname = "manutencao_maquinas";
        $username = "root";
        $password = "";
        
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
}
?>