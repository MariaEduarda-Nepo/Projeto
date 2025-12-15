<?php
require_once __DIR__ . '/../Model/CadastroDAO.php';
require_once __DIR__ . '/../Model/Cadastro.php';

class CadastroController {

    private $dao;

    public function __construct() {
        $this->dao = new CadastroDAO();
    }

    // ============================================
    // FUNCAO: VALIDAR DADOS DO CADASTRO
    // ============================================
    // Esta função valida todos os campos antes de criar ou atualizar um cadastro
    // Parâmetros:
    //   - $tipo: tipo de usuário (Aluno, Professor, Funcionario)
    //   - $email: email do usuário
    //   - $senha: senha (opcional)
    //   - $confirmarSenha: confirmação da senha (opcional)
    //   - $cpf: CPF (opcional)
    //   - $telefone: telefone (opcional)
    //   - $dataNascimento: data de nascimento (opcional)
    // Retorna: true se válido, ou string com mensagem de erro
    private function validar($tipo, $email, $senha = null, $confirmarSenha = null, $cpf = null, $telefone = null, $dataNascimento = null) {
        
        // ============================================
        // VALIDACAO 1: FORMATO DO EMAIL
        // ============================================
        // Verifica se o email tem um formato válido usando a função nativa do PHP
        // filter_var() com FILTER_VALIDATE_EMAIL verifica:
        //   - Se tem @ (arroba)
        //   - Se tem domínio válido
        //   - Se o formato geral está correto (ex: usuario@dominio.com)
        // Se o formato estiver inválido, retorna erro e para a validação
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Email inválido!";
        }

        // ============================================
        // VALIDACAO 2: DOMINIO DO EMAIL POR TIPO DE USUARIO
        // ============================================
        // Cada tipo de usuário só pode usar domínios específicos
        
        // ===== VALIDACAO PARA ALUNO =====
        // Alunos só podem usar emails de provedores públicos comuns
        if ($tipo === "Aluno") {
            // Lista de domínios permitidos para alunos
            $dominiosPermitidos = ["@gmail.com", "@icloud.com", "@outlook.com"];
            $emailValido = false; // Flag para controlar se encontrou um domínio válido

            // Percorre a lista de domínios permitidos
            foreach ($dominiosPermitidos as $dom) {
                // Verifica se o email termina com algum dos domínios permitidos
                // str_ends_with() verifica se a string termina com a substring especificada
                if (str_ends_with($email, $dom)) {
                    $emailValido = true; // Encontrou um domínio válido
                    break; // Para o loop, não precisa verificar os outros
                }
            }

            // Se não encontrou nenhum domínio válido, retorna erro
            if (!$emailValido) {
                return "Aluno só pode usar email @gmail.com, @icloud.com ou @outlook.com";
            }
        }

        // ===== VALIDACAO PARA PROFESSOR =====
        // Professores só podem usar emails que terminem com @professor.com
        // Exemplos válidos: joao@professor.com, maria.silva@professor.com
        if ($tipo === "Professor" && !str_ends_with($email, "@professor.com")) {
            return "Professor só pode usar email que termine com @professor.com";
        }

        // ===== VALIDACAO PARA FUNCIONARIO =====
        // Funcionários só podem usar emails que terminem com @funcionario.com
        // Exemplos válidos: admin@funcionario.com, gerente@funcionario.com
        if ($tipo === "Funcionario" && !str_ends_with($email, "@funcionario.com")) {
            return "Funcionário só pode usar email que termine com @funcionario.com";
        }

        // Validação de CPF (apenas verifica se tem 11 dígitos)
        if ($cpf !== null && !empty(trim($cpf))) {
            $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
            if (strlen($cpfLimpo) != 11) {
                return "CPF deve ter 11 dígitos! Você digitou " . strlen($cpfLimpo) . " dígito(s).";
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

        // Validação de senha (mínimo 6 caracteres)
        if ($senha !== null && !empty($senha)) {
            if (strlen($senha) < 6) {
                return "A senha deve ter no mínimo 6 caracteres!";
            }
        }

        // Confirmação de senha
        if ($senha !== null && $confirmarSenha !== null && $senha !== $confirmarSenha) {
            return "As senhas não coincidem!";
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
    public function criar($tipo, $nome, $email, $senha, $confirmarSenha, $cpf, $telefone, $dataNascimento, $modalidades = [], $isAdmin = false) {
        // Validação de tipo de conta baseado no contexto
        if ($isAdmin) {
            // Admin só pode cadastrar Professor ou Funcionário
            if (!in_array($tipo, ['Professor', 'Funcionario'])) {
                return "Apenas Professor e Funcionário podem ser cadastrados pelo painel admin!";
            }
        } else {
            // Público só pode cadastrar Aluno
            if ($tipo !== 'Aluno') {
                return "Apenas Alunos podem se cadastrar publicamente!";
            }
        }
        
        // Limpa e valida o nome (opcional para admin primário)
        if ($nome !== null) {
            $nome = trim($nome);
        }
        
        // Nome é obrigatório exceto para admin primário (Funcionario sem nome)
        if ($tipo !== 'Funcionario' || $nome !== null) {
            if (empty($nome)) {
                return "Nome é obrigatório!";
            }
        }

        // Valida todos os campos
        $validacao = $this->validar($tipo, $email, $senha, $confirmarSenha, $cpf, $telefone, $dataNascimento);
        if ($validacao !== true) return $validacao;

        // Validação de modalidades para Professor
        if ($tipo === "Professor") {
            if (empty($modalidades) || !is_array($modalidades)) {
                return "Professor deve selecionar pelo menos uma modalidade!";
            }
            $modalidadesValidas = ['Boxe', 'Muay Thai'];
            foreach ($modalidades as $modalidade) {
                if (!in_array($modalidade, $modalidadesValidas)) {
                    return "Modalidade inválida! Selecione Box ou Muay Thai.";
                }
            }
        }

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
        $resultado = $this->dao->criarCadastro($cadastro, $modalidades);
        return $resultado;
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

        // Limpa CPF e telefone (remove formatação) - apenas se não estiverem vazios
        if (!empty($cpf)) {
            $cpf = preg_replace('/[^0-9]/', '', $cpf);
        }
        if (!empty($telefone)) {
            $telefone = preg_replace('/[^0-9]/', '', $telefone);
        }

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
