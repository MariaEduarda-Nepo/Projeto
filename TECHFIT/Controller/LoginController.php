<?php
require_once __DIR__ . '/../Model/LoginDAO.php';
require_once __DIR__ . '/../Model/Login.php';

class LoginController {

    private $dao;

    public function __construct() {
        $this->dao = new LoginDAO();
    }

    public function login($email, $senha) {

        // session_start() já é chamado no arquivo que usa este controller

        $usuario = $this->dao->buscarPorEmail($email);

        if (!$usuario) {
            return ["erro", "Usuário não encontrado!"];
        }

        // Verificação da senha com hash
        if (!password_verify($senha, $usuario['senha'])) {
            return ["erro", "Senha incorreta!"];
        }

        // Para admin primário, o nome pode ser NULL - usar email ou "Administrador" como padrão
        $nomeUsuario = isset($usuario['nome']) ? trim($usuario['nome']) : '';
        
        // Se o nome estiver vazio (admin primário), usar um nome padrão
        if (empty($nomeUsuario)) {
            if ($usuario['tipo'] === 'Funcionario') {
                $nomeUsuario = 'Administrador';
            } else {
                // Para outros tipos, usar o email como fallback
                $nomeUsuario = $usuario['email'];
            }
        }

        // Criar sessão do usuário
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['tipo'] = $usuario['tipo'];
        $_SESSION['nome'] = $nomeUsuario;
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['cpf'] = isset($usuario['cpf']) ? $usuario['cpf'] : '';
        $_SESSION['telefone'] = isset($usuario['telefone']) ? $usuario['telefone'] : '';
        $_SESSION['nascimento'] = isset($usuario['datanascimento']) ? $usuario['datanascimento'] : '';

        return ["sucesso", "Login realizado com sucesso!"];
    }
}
?>
