<?php
require_once __DIR__ . '/Login.php';
require_once __DIR__ . '/Connection.php';

class LoginDAO {

    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    // Buscar usuário pelo email
    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("
            SELECT 
                id, 
                tipo, 
                nome, 
                email, 
                senha, 
                cpf, 
                telefone, 
                datanascimento
            FROM Cadastros 
            WHERE email = :email 
            LIMIT 1
        ");

        $stmt->execute([':email' => $email]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug: verificar se o nome está vindo corretamente
        if ($resultado && isset($resultado['nome'])) {
            $resultado['nome'] = trim($resultado['nome']);
        }
        
        return $resultado;
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
