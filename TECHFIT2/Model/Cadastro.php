<?php
class Cadastro {
    private $id;
    private $tipo;
    private $nome;
    private $senha;
    private $email;
    private $documento;
    private $datanascimento;

    public function __construct($tipo, $nome, $senha, $email, $documento, $datanascimento, $id = null){
        $this->id = $id;
        $this->tipo = $tipo;
        $this->nome = $nome;
        $this->senha = $senha;
        $this->email = $email;
        $this->documento = $documento;
        $this->datanascimento = $datanascimento;
    } 

    public function getId() { return $this->id; }
    public function getTipo() { return $this->tipo; }
    public function getNome() { return $this->nome; }
    public function getSenha() { return $this->senha; }
    public function getEmail() { return $this->email; }
    public function getDocumento() { return $this->documento; }
    public function getDataNascimento() { return $this->datanascimento; }

    public function setTipo($tipo): self { $this->tipo = $tipo; return $this; }
    public function setNome($nome): self { $this->nome = $nome; return $this; }
    public function setSenha($senha): self { $this->senha = $senha; return $this; }
    public function setEmail($email): self { $this->email = $email; return $this; }
    public function setDocumento($documento): self { $this->documento = $documento; return $this; }
    public function setDataNascimento($datanascimento) { $this->datanascimento = $datanascimento; }
}
?>
