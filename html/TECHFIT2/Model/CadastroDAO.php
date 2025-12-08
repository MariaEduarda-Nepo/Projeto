<?php
require_once 'Cadastro.php';
require_once 'Connection.php';

class CadastroDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    // -----------------------------
    // CRIAR CADASTRO
    // -----------------------------
    public function criarCadastro(Cadastro $c) {

        // Verifica email duplicado
        if ($this->buscarPorEmail($c->getEmail())) {
            return "Já existe cadastro com este email!";
        }

        // Verifica CPF duplicado (apenas se CPF não estiver vazio)
        if (!empty($c->getCpf())) {
            $cpfExistente = $this->buscarPorCpf($c->getCpf());
            if ($cpfExistente) {
                return "Já existe cadastro com este CPF!";
            }
        }

        $stmt = $this->conn->prepare("
            INSERT INTO Cadastros (tipo, nome, email, senha, cpf, telefone, datanascimento)
            VALUES (:tipo, :nome, :email, :senha, :cpf, :telefone, :datanascimento)
        ");

        $stmt->execute([
            ':tipo' => $c->getTipo(),
            ':nome' => $c->getNome(),
            ':email' => $c->getEmail(),
            ':senha' => $c->getSenha(),
            ':cpf' => $c->getCpf(),
            ':telefone' => $c->getTelefone(),
            ':datanascimento' => $c->getDataNascimento()
        ]);

        return true;
    }

    // -----------------------------
    // LER CADASTROS
    // -----------------------------
    public function lerCadastro() {
        $stmt = $this->conn->query("SELECT * FROM Cadastros ORDER BY tipo, nome");

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Cadastro(
                $row['tipo'],
                $row['nome'],
                $row['email'],
                $row['senha'],
                $row['cpf'],
                $row['telefone'],
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
            $row['email'],
            $row['senha'],
            $row['cpf'],
            $row['telefone'],
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
    // BUSCAR POR CPF
    // -----------------------------
    public function buscarPorCpf($cpf) {
        $stmt = $this->conn->prepare("SELECT * FROM Cadastros WHERE cpf=:cpf LIMIT 1");
        $stmt->execute([':cpf' => $cpf]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // ATUALIZAR CADASTRO
    // -----------------------------
    public function atualizarCadastro($id, $tipo, $nome, $email, $senha, $cpf, $telefone, $datanascimento) {

        // Verifica duplicidade de email
        $exEmail = $this->buscarPorEmail($email);
        if ($exEmail && intval($exEmail['id']) !== intval($id)) {
            return "Já existe outro cadastro com este email!";
        }

        // Verifica duplicidade de CPF
        $exCpf = $this->buscarPorCpf($cpf);
        if ($exCpf && intval($exCpf['id']) !== intval($id)) {
            return "Já existe outro cadastro com este CPF!";
        }

        $stmt = $this->conn->prepare("
            UPDATE Cadastros 
            SET tipo=:tipo, nome=:nome, email=:email, senha=:senha, cpf=:cpf, telefone=:telefone, datanascimento=:datanascimento
            WHERE id=:id
        ");

        $stmt->execute([
            ':tipo' => $tipo,
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senha,
            ':cpf' => $cpf,
            ':telefone' => $telefone,
            ':datanascimento' => $datanascimento,
            ':id' => $id
        ]);

        return true;
    }

    // -----------------------------
    // LISTAR TODOS (retorna array associativo)
    // -----------------------------
    public function listarTodos() {
        $stmt = $this->conn->query("SELECT * FROM Cadastros ORDER BY tipo, nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
