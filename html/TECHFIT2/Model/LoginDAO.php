<?php
require_once __DIR__ . '/Login.php';
require_once __DIR__ . '/Connection.php';

class LoginDAO {

    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    // Buscar usuário somente pelo login
    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("
            SELECT id, tipo, nome, senha, email, documento, datanascimento
            FROM Cadastros 
            WHERE email = :email 
            LIMIT 1
        ");

        $stmt->execute([':email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC); // LoginController espera array
    }

    // Registrar último acesso (futuramente, se quiser)
    public function registrarUltimoAcesso($id) {
        $stmt = $this->conn->prepare("
            UPDATE Cadastros 
            SET ultimo_acesso = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

}
?>
