<?php
require_once 'Cadastro.php';
require_once 'Connection.php';

class CadastroDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();

        // Criação da tabela caso não exista
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS Cadastros (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo VARCHAR(200) NOT NULL,
                nome VARCHAR(150) NOT NULL,
                senha VARCHAR(255) NOT NULL,
                email VARCHAR(200) NOT NULL UNIQUE,
                documento VARCHAR(20) NOT NULL UNIQUE,
                datanascimento DATE NOT NULL
            )
        ");
    }

    // -----------------------------
    // CRIAR CADASTRO
    // -----------------------------
    public function criarCadastro(Cadastro $c) {

        // Verifica email duplicado
        if ($this->buscarPorEmail($c->getEmail())) {
            return "Já existe cadastro com este email!";
        }

        // Verifica documento duplicado
        if ($this->buscarPorDocumento($c->getDocumento())) {
            return "Já existe cadastro com este documento!";
        }

        // IMPORTANTE:
        // Controller já envia a senha HASHEADA!
        // Aqui só grava no banco.

        $stmt = $this->conn->prepare("
            INSERT INTO Cadastros (tipo, nome, senha, email, documento, datanascimento)
            VALUES (:tipo, :nome, :senha, :email, :documento, :datanascimento)
        ");

        $stmt->execute([
            ':tipo' => $c->getTipo(),
            ':nome' => $c->getNome(),
            ':senha' => $c->getSenha(), // já vem hasheada
            ':email' => $c->getEmail(),
            ':documento' => $c->getDocumento(),
            ':datanascimento' => $c->getDataNascimento()
        ]);

        return true;
    }

    // -----------------------------
    // LER CADASTROS
    // -----------------------------
    public function lerCadastro() {
        $stmt = $this->conn->query("SELECT * FROM Cadastros ORDER BY tipo");

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Cadastro(
                $row['tipo'], 
                $row['nome'], 
                $row['senha'],
                $row['email'], 
                $row['documento'], 
                $row['datanascimento'], 
                $row['id']
            );
        }

        return $result;
    }

    // -----------------------------
    // BUSCAR POR ID
    // -----------------------------
    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM Cadastros WHERE id=:id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Cadastro(
            $row['tipo'], 
            $row['nome'], 
            $row['senha'],
            $row['email'], 
            $row['documento'],
            $row['datanascimento'], 
            $row['id']
        ) : null;
    }

    // -----------------------------
    // BUSCAR POR EMAIL
    // -----------------------------
    public function buscarPorEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM Cadastros WHERE email=:email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // BUSCAR POR DOCUMENTO
    // -----------------------------
    public function buscarPorDocumento($documento) {
        $stmt = $this->conn->prepare("SELECT * FROM Cadastros WHERE documento=:documento LIMIT 1");
        $stmt->execute([':documento' => $documento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // ATUALIZAR CADASTRO
    // -----------------------------
    public function atualizarCadastro($id, $tipo, $nome, $senha, $email, $documento, $datanascimento) {

        // Verifica duplicidade de email
        $exEmail = $this->buscarPorEmail($email);
        if ($exEmail && intval($exEmail['id']) !== intval($id)) {
            return "Já existe outro cadastro com este email!";
        }

        // Verifica duplicidade de documento
        $exDoc = $this->buscarPorDocumento($documento);
        if ($exDoc && intval($exDoc['id']) !== intval($id)) {
            return "Já existe outro cadastro com este documento!";
        }

        // Controller decide se envia hash novo ou mantém a senha antiga
        $stmt = $this->conn->prepare("
            UPDATE Cadastros 
            SET tipo=:tipo, nome=:nome, senha=:senha, email=:email, documento=:documento, datanascimento=:datanascimento
            WHERE id=:id
        ");

        $stmt->execute([
            ':tipo' => $tipo,
            ':nome' => $nome,
            ':senha' => $senha, // já vem hasheada se for nova
            ':email' => $email,
            ':documento' => $documento,
            ':datanascimento' => $datanascimento,
            ':id' => $id
        ]);

        return true;
    }

    // -----------------------------
    // EXCLUIR
    // -----------------------------
    public function excluirCadastro($id) {
        $stmt = $this->conn->prepare("DELETE FROM Cadastros WHERE id=:id");
        $stmt->execute([':id' => $id]);
        return true;
    }
}
?>
