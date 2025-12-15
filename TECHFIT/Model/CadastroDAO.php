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
    // Cria um novo cadastro no banco de dados
    // Parâmetros:
    //   - $c: objeto Cadastro com os dados do usuário
    //   - $modalidades: array com as modalidades (apenas para Professores)
    // Retorna: true se criado com sucesso, ou string com mensagem de erro
    public function criarCadastro(Cadastro $c, $modalidades = []) {

        // ============================================
        // VALIDACAO: EMAIL DUPLICADO
        // ============================================
        // Verifica se já existe um cadastro com o mesmo email no banco de dados
        // Isso garante que cada email seja único no sistema
        // Se encontrar um email duplicado, retorna erro e impede o cadastro
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

        // Se for Professor, salva as modalidades
        if ($c->getTipo() === "Professor" && !empty($modalidades) && is_array($modalidades)) {
            $professorId = $this->conn->lastInsertId();
            $stmtModalidade = $this->conn->prepare("
                INSERT INTO ProfessorModalidade (professor_id, modalidade)
                VALUES (:professor_id, :modalidade)
            ");
            
            foreach ($modalidades as $modalidade) {
                $stmtModalidade->execute([
                    ':professor_id' => $professorId,
                    ':modalidade' => $modalidade
                ]);
            }
        }

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
    // Busca um cadastro no banco de dados pelo email
    // Parâmetros:
    //   - $email: email a ser buscado
    // Retorna: array associativo com os dados do cadastro se encontrado, ou false se não encontrado
    public function buscarPorEmail($email) {
        // Prepara a query SQL para buscar um cadastro pelo email
        // LIMIT 1 garante que retorne apenas um resultado (o primeiro encontrado)
        $stmt = $this->conn->prepare("SELECT * FROM Cadastros WHERE email=:email LIMIT 1");
        // Executa a query passando o email como parâmetro (proteção contra SQL injection)
        $stmt->execute([':email' => $email]);
        // Retorna o resultado como array associativo
        // Se não encontrar, retorna false
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
    // Atualiza os dados de um cadastro existente no banco de dados
    // Parâmetros: todos os campos do cadastro
    // Retorna: true se atualizado com sucesso, ou string com mensagem de erro
    public function atualizarCadastro($id, $tipo, $nome, $email, $senha, $cpf, $telefone, $datanascimento) {

        // ============================================
        // VALIDACAO: EMAIL DUPLICADO (ATUALIZACAO)
        // ============================================
        // Verifica se o email já está sendo usado por outro cadastro
        // Busca um cadastro com o email informado
        $exEmail = $this->buscarPorEmail($email);
        // Se encontrou um cadastro com esse email E o ID é diferente do que está sendo atualizado
        // Significa que outro cadastro já está usando esse email
        // intval() converte para inteiro para comparação segura
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

    // -----------------------------
    // ADICIONAR MODALIDADE DO PROFESSOR
    // -----------------------------
    public function adicionarProfessorModalidade($professorId, $modalidade) {
        $stmt = $this->conn->prepare("
            INSERT INTO ProfessorModalidade (professor_id, modalidade)
            VALUES (:professor_id, :modalidade)
            ON DUPLICATE KEY UPDATE modalidade = VALUES(modalidade)
        ");
        $stmt->execute([
            ':professor_id' => $professorId,
            ':modalidade' => $modalidade
        ]);
        return true;
    }
}
?>
