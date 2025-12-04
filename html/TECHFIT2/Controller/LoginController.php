<?php
require_once __DIR__ . '/../Model/LoginDAO.php';
require_once __DIR__ . '/../Model/Login.php';

class LoginController {

    private $dao;

    public function __construct() {
        $this->dao = new LoginDAO();
    }

    public function login($email, $senha) {

        session_start();

        $usuario = $this->dao->buscarPorEmail($email);

        if (!$usuario) {
            return ["erro", "Usuário não encontrado!"];
        }

        // Verificação da senha com hash
        if (!password_verify($senha, $usuario['senha'])) {
            return ["erro", "Senha incorreta!"];
        }

        // Criar sessão do usuário
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['tipo'] = $usuario['tipo'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['documento'] = $usuario['documento'];
        $_SESSION['nascimento'] = $usuario['datanascimento'];

        return ["sucesso", "Login realizado com sucesso!"];
    }
}
?>
