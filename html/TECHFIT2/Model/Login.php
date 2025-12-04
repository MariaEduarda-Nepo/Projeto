<?php

class Login {

    private $email;
    private $senha;

    public function __construct($email = "", $senha = "") {
        $this->email = $email;
        $this->senha = $senha;
    }

    // ----------- GETTERS -----------

    public function getEmail() {
        return $this->email;
    }

    public function getSenha() {
        return $this->senha;
    }

    // ----------- SETTERS -----------

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }
}

?>
