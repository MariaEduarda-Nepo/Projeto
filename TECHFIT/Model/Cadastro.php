<?php
class Cadastro {
    private $id;
    private $tipo;
    private $nome;
    private $email;
    private $senha;
    private $cpf;
    private $telefone;
    private $datanascimento;

    public function __construct($tipo, $nome, $email, $senha, $cpf, $telefone, $datanascimento, $id = null) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->datanascimento = $datanascimento;
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getTipo() { return $this->tipo; }
    public function getNome() { return $this->nome; }
    public function getEmail() { return $this->email; }
    public function getSenha() { return $this->senha; }
    public function getCpf() { return $this->cpf; }
    public function getTelefone() { return $this->telefone; }
    public function getDataNascimento() { return $this->datanascimento; }

    // SETTERS
    public function setTipo($tipo) { $this->tipo = $tipo; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setEmail($email) { $this->email = $email; }
    public function setSenha($senha) { $this->senha = $senha; }
    public function setCpf($cpf) { $this->cpf = $cpf; }
    public function setTelefone($telefone) { $this->telefone = $telefone; }
    public function setDataNascimento($datanascimento) { $this->datanascimento = $datanascimento; }
}
?>
