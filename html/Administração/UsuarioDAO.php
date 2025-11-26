<?php
// Configurações do Banco de Dados
define('DB_SERVER', 'localhost');  // CORRIGIDO: era '3306'
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'senaisp');
define('DB_NAME', 'TechFit');

class UsuarioDAO {
    private $conn;

    public function __construct() {
        // Conecta ao banco de dados no momento da instância
        $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($this->conn->connect_error) {
            die("Falha na conexão com o Banco de Dados: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    // --- CRIAR/CREATE (Insere um novo usuário) ---
    public function cadastrar($nome, $email, $senha, $documento, $tipo, $id_filial) {
        $stmt = $this->conn->prepare("INSERT INTO USUARIOS (Nome, Email, Senha, Documento, Tipo, ID_FILIAL) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Em um projeto real, a senha deveria ser hasheada aqui antes de inserir
        // $senhaHashed = password_hash($senha, PASSWORD_DEFAULT);
        $stmt->bind_param("sssssi", $nome, $email, $senha, $documento, $tipo, $id_filial);
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // --- LER/READ (Retorna todos os usuários) ---
    public function lerTodos() {
        $sql = "SELECT ID_USUARIO, Nome, Email, Tipo, ID_FILIAL, Documento FROM USUARIOS";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    // --- ATUALIZAR/UPDATE (Modifica dados de um usuário existente) ---
    public function atualizar($id_usuario, $nome, $email, $senha, $documento, $tipo, $id_filial) {
        if (!empty($senha)) {
            // Atualiza com nova senha
            $sql = "UPDATE USUARIOS SET Nome=?, Email=?, Senha=?, Documento=?, Tipo=?, ID_FILIAL=? WHERE ID_USUARIO=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssii", $nome, $email, $senha, $documento, $tipo, $id_filial, $id_usuario);
        } else {
            // Atualiza sem alterar a senha
            $sql = "UPDATE USUARIOS SET Nome=?, Email=?, Documento=?, Tipo=?, ID_FILIAL=? WHERE ID_USUARIO=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssii", $nome, $email, $documento, $tipo, $id_filial, $id_usuario);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // --- EXCLUIR/DELETE (Remove um usuário) ---
    public function excluir($id_usuario) {
        $stmt = $this->conn->prepare("DELETE FROM USUARIOS WHERE ID_USUARIO = ?");
        $stmt->bind_param("i", $id_usuario);

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function __destruct() {
        // Fecha a conexão com o banco de dados
        $this->conn->close();
    }
}
?>