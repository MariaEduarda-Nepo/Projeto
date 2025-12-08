<?php
require_once __DIR__ . '/../Model/CadastroDAO.php';
require_once __DIR__ . '/../Model/Cadastro.php';

class CadastroController {

    private $dao;

    public function __construct() {
        $this->dao = new CadastroDAO();
    }

    // ------------------ VALIDAÇÃO ------------------
    private function validar($tipo, $email, $senha = null, $confirmarSenha = null, $cpf = null, $telefone = null, $dataNascimento = null) {
        
        // Validação de formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Email inválido!";
        }

        // Validação de email para ALUNO
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

        // Validação de email para PROFESSOR
        if ($tipo === "Professor" && !str_ends_with($email, "@professor.com")) {
            return "Professor só pode usar email que termine com @professor.com";
        }

        // Validação de email para FUNCIONARIO
        if ($tipo === "Funcionario" && !str_ends_with($email, "@funcionario.com")) {
            return "Funcionário só pode usar email que termine com @funcionario.com";
        }

        // Validação de CPF
        if ($cpf !== null && !empty(trim($cpf))) {
            $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
            if (strlen($cpfLimpo) != 11) {
                return "CPF deve ter 11 dígitos! Você digitou " . strlen($cpfLimpo) . " dígito(s).";
            }
            if (!$this->validarCPF($cpf)) {
                return "CPF inválido! Os dígitos verificadores não conferem. Verifique se digitou corretamente.";
            }
        }

        // Validação de telefone
        if ($telefone !== null && !$this->validarTelefone($telefone)) {
            return "Telefone inválido! Use o formato (00) 00000-0000 ou (00) 0000-0000";
        }

        // Validação de data de nascimento
        if ($dataNascimento !== null && !$this->validarDataNascimento($dataNascimento)) {
            return "Data de nascimento inválida! A data não pode ser no futuro e a idade mínima é 16 anos.";
        }

        // Confirmação de senha
        if ($senha !== null && $confirmarSenha !== null && $senha !== $confirmarSenha) {
            return "As senhas não coincidem!";
        }

        return true;
    }

    // ------------------ VALIDAR CPF ------------------
    private function validarCPF($cpf) {
        // Se CPF estiver vazio, considera válido (opcional)
        if (empty($cpf) || trim($cpf) === '') {
            return true;
        }
        
        // Remove caracteres não numéricos
        $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpfLimpo) != 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais (CPF inválido)
        // Exemplos: 111.111.111-11, 222.222.222-22, etc.
        if (preg_match('/(\d)\1{10}/', $cpfLimpo)) {
            return false;
        }
        
        // Validação dos dígitos verificadores
        // Calcula o primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += intval($cpfLimpo[$i]) * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;
        
        if (intval($cpfLimpo[9]) != $digito1) {
            return false;
        }
        
        // Calcula o segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += intval($cpfLimpo[$i]) * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        
        if (intval($cpfLimpo[10]) != $digito2) {
            return false;
        }
        
        return true;
    }

    // ------------------ VALIDAR TELEFONE ------------------
    private function validarTelefone($telefone) {
        // Remove caracteres não numéricos
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        // Verifica se tem 10 ou 11 dígitos (fixo ou celular)
        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            return false;
        }
        
        // Verifica se começa com DDD válido (11-99)
        $ddd = substr($telefone, 0, 2);
        if ($ddd < 11 || $ddd > 99) {
            return false;
        }
        
        return true;
    }

    // ------------------ VALIDAR DATA DE NASCIMENTO ------------------
    private function validarDataNascimento($dataNascimento) {
        $data = DateTime::createFromFormat('Y-m-d', $dataNascimento);
        if (!$data) {
            return false;
        }
        
        $hoje = new DateTime();
        $idade = $hoje->diff($data)->y;
        
        // Verifica se a data não é no futuro
        if ($data > $hoje) {
            return false;
        }
        
        // Verifica idade mínima (16 anos)
        if ($idade < 16) {
            return false;
        }
        
        // Verifica idade máxima razoável (120 anos)
        if ($idade > 120) {
            return false;
        }
        
        return true;
    }

    // ------------------ CRIAR CADASTRO ------------------
    public function criar($tipo, $nome, $email, $senha, $confirmarSenha, $cpf, $telefone, $dataNascimento) {
        // Limpa e valida o nome
        $nome = trim($nome);
        if (empty($nome)) {
            return "Nome é obrigatório!";
        }

        // Valida todos os campos
        $validacao = $this->validar($tipo, $email, $senha, $confirmarSenha, $cpf, $telefone, $dataNascimento);
        if ($validacao !== true) return $validacao;

        // Limpa CPF e telefone (remove formatação) - apenas se não estiverem vazios
        if (!empty($cpf)) {
            $cpf = preg_replace('/[^0-9]/', '', $cpf);
        }
        if (!empty($telefone)) {
            $telefone = preg_replace('/[^0-9]/', '', $telefone);
        }

        // Hash da senha
        $senhaHasheada = password_hash($senha, PASSWORD_DEFAULT);

        $cadastro = new Cadastro($tipo, $nome, $email, $senhaHasheada, $cpf, $telefone, $dataNascimento);
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
    public function atualizar($id, $tipo, $nome, $email, $novaSenha, $confirmarNovaSenha, $cpf, $telefone, $dataNascimento) {
        $cadastroAtual = $this->dao->buscarPorId($id);
        if (!$cadastroAtual) return "Cadastro não encontrado!";

        // Valida todos os campos
        $validacao = $this->validar($tipo, $email, $novaSenha, $confirmarNovaSenha, $cpf, $telefone, $dataNascimento);
        if ($validacao !== true) return $validacao;

        // Limpa CPF e telefone (remove formatação)
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $telefone = preg_replace('/[^0-9]/', '', $telefone);

        // Senha nova ou manter a antiga
        if (empty($novaSenha)) {
            $senhaFinal = $cadastroAtual->getSenha();
        } else {
            $senhaFinal = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        return $this->dao->atualizarCadastro($id, $tipo, $nome, $email, $senhaFinal, $cpf, $telefone, $dataNascimento);
    }

    // ------------------ DELETAR ------------------
    public function deletar($id) {
        $this->dao->excluirCadastro($id);
    }
}
?>
