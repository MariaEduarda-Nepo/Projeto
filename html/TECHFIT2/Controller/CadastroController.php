<?php
require_once __DIR__ . '/../Model/CadastroDAO.php';
require_once __DIR__ . '/../Model/Cadastro.php';

class CadastroController {

    private $dao;

    public function __construct() {
        $this->dao = new CadastroDAO();
    }

    // ------------------ VALIDAÇÃO ------------------
    private function validar($tipo, $email, $senha = null, $confirmarSenha = null) {

        // Validação de email para aluno
        if ($tipo === "Aluno") {
            $dominiosPermitidos = ["@gmail.com", "@icloud.com", "@outlook.com"];
            $emailValido = false;

            foreach ($dominiosPermitidos as $dom) {
                if (str_ends_with($email, $dom)) {
                    $emailValido = true;
                    break;
                }
            }

            if (!$emailValido) {
                return "Aluno só pode usar email @gmail.com, @icloud.com ou @outlook.com";
            }
        }

        // Validação de email para professor
        if ($tipo === "Professor" && !str_ends_with($email, "@func.com")) {
            return "Professor só pode usar email que termine com @func.com";
        }

        // Confirmação de senha se informado
        if ($senha !== null && $senha !== $confirmarSenha) {
            return "As senhas não coincidem!";
        }

        return true;
    }

    // ------------------ CRIAR CADASTRO ------------------
    public function criar($tipo, $nome, $senha, $confirmarSenha, $email, $documento, $dataNascimento) {
        $validacao = $this->validar($tipo, $email, $senha, $confirmarSenha);
        if ($validacao !== true) return $validacao;

        // Hash da senha
        $senhaHasheada = password_hash($senha, PASSWORD_DEFAULT);

        $cadastro = new Cadastro($tipo, $nome, $senhaHasheada, $email, $documento, $dataNascimento);
        return $this->dao->criarCadastro($cadastro);
    }

    // ------------------ LISTAR ------------------
    public function ler() {
        return $this->dao->lerCadastro();
    }

    // ------------------ BUSCAR POR ID ------------------
    public function buscarPorId($id) {
        return $this->dao->buscarPorId($id);
    }

    // ------------------ ATUALIZAR ------------------
    public function atualizar($id, $tipo, $nome, $novaSenha, $confirmarNovaSenha, $email, $documento, $dataNascimento) {
        $cadastroAtual = $this->dao->buscarPorId($id);
        if (!$cadastroAtual) return "Cadastro não encontrado!";

        $validacao = $this->validar($tipo, $email, $novaSenha, $confirmarNovaSenha);
        if ($validacao !== true) return $validacao;

        // Senha nova ou manter a antiga
        if (empty($novaSenha)) {
            $senhaFinal = $cadastroAtual->getSenha();
        } else {
            $senhaFinal = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        return $this->dao->atualizarCadastro($id, $tipo, $nome, $senhaFinal, $email, $documento, $dataNascimento);
    }

    // ------------------ DELETAR ------------------
    public function deletar($id) {
        $this->dao->excluirCadastro($id);
    }
}
?>
