<?php
require_once 'AvaliacaoFisica.php';
require_once 'Connection.php';

class AvaliacaoFisicaDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    public function criarAvaliacao(AvaliacaoFisica $af) {
        // Verificar se já existe avaliação para este aluno na mesma data
        $stmtCheck = $this->conn->prepare("
            SELECT id FROM AvaliacoesFisicas 
            WHERE aluno_id = :aluno_id AND data_avaliacao = :data_avaliacao
            LIMIT 1
        ");
        
        $stmtCheck->execute([
            ':aluno_id' => $af->getAlunoId(),
            ':data_avaliacao' => $af->getDataAvaliacao()
        ]);
        
        if ($stmtCheck->fetch()) {
            $dataFormatada = date('d/m/Y', strtotime($af->getDataAvaliacao()));
            return "Não é possível criar duas avaliações no mesmo dia! Já existe uma avaliação física para este aluno na data {$dataFormatada}.";
        }
        
        // Validar e limitar valores numéricos conforme limites do banco
        
        // Peso: DECIMAL(5,2) - máximo 999.99
        if ($af->getPeso() !== null) {
            $peso = floatval($af->getPeso());
            if ($peso > 999.99) {
                return "O peso não pode ser maior que 999.99 kg!";
            }
            if ($peso < 0) {
                return "O peso não pode ser negativo!";
            }
        }
        
        // Altura: DECIMAL(3,2) - máximo 9.99m (mas validamos 0.5 a 2.5m)
        if ($af->getAltura() !== null) {
            $altura = floatval($af->getAltura());
            if ($altura < 0.5 || $altura > 2.5) {
                return "A altura deve estar entre 0.5m e 2.5m (50cm a 250cm)!";
            }
        }
        
        // Percentual de gordura: DECIMAL(4,2) - máximo 99.99
        if ($af->getPercentualGordura() !== null) {
            $percentual = floatval($af->getPercentualGordura());
            if ($percentual > 99.99) {
                return "O percentual de gordura não pode ser maior que 99.99%!";
            }
            if ($percentual < 0) {
                return "O percentual de gordura não pode ser negativo!";
            }
        }
        
        // Massa muscular: DECIMAL(5,2) - máximo 999.99
        if ($af->getMassaMuscular() !== null) {
            $massa = floatval($af->getMassaMuscular());
            if ($massa > 999.99) {
                return "A massa muscular não pode ser maior que 999.99 kg!";
            }
            if ($massa < 0) {
                return "A massa muscular não pode ser negativa!";
            }
        }
        
        // Circunferências: DECIMAL(4,2) - máximo 99.99
        if ($af->getCircunferenciaBraco() !== null) {
            $braco = floatval($af->getCircunferenciaBraco());
            if ($braco > 99.99) {
                return "A circunferência do braço não pode ser maior que 99.99 cm!";
            }
            if ($braco < 0) {
                return "A circunferência do braço não pode ser negativa!";
            }
        }
        
        if ($af->getCircunferenciaCintura() !== null) {
            $cintura = floatval($af->getCircunferenciaCintura());
            if ($cintura > 99.99) {
                return "A circunferência da cintura não pode ser maior que 99.99 cm!";
            }
            if ($cintura < 0) {
                return "A circunferência da cintura não pode ser negativa!";
            }
        }
        
        if ($af->getCircunferenciaQuadril() !== null) {
            $quadril = floatval($af->getCircunferenciaQuadril());
            if ($quadril > 99.99) {
                return "A circunferência do quadril não pode ser maior que 99.99 cm!";
            }
            if ($quadril < 0) {
                return "A circunferência do quadril não pode ser negativa!";
            }
        }
        
        // Calcular IMC se peso e altura estiverem presentes
        if ($af->getPeso() && $af->getAltura()) {
            $peso = floatval($af->getPeso());
            $altura = floatval($af->getAltura());
            
            // Validar valores razoáveis
            // Altura deve estar entre 0.5m e 2.5m (50cm a 250cm)
            // Peso deve estar entre 10kg e 500kg
            if ($altura >= 0.5 && $altura <= 2.5 && $peso >= 10 && $peso <= 500) {
                $imc = $peso / ($altura * $altura);
                // Limitar IMC a um valor máximo razoável (99.99)
                $imc = min($imc, 99.99);
                $af->setImc(round($imc, 2));
            } else {
                // Se valores estão fora do range, não calcular IMC
                $af->setImc(null);
            }
        }

        $stmt = $this->conn->prepare("
            INSERT INTO AvaliacoesFisicas (
                aluno_id, data_avaliacao, peso, altura, imc, 
                percentual_gordura, massa_muscular, 
                circunferencia_braco, circunferencia_cintura, circunferencia_quadril,
                observacoes, proxima_avaliacao
            )
            VALUES (
                :aluno_id, :data_avaliacao, :peso, :altura, :imc,
                :percentual_gordura, :massa_muscular,
                :circunferencia_braco, :circunferencia_cintura, :circunferencia_quadril,
                :observacoes, :proxima_avaliacao
            )
        ");

        $stmt->execute([
            ':aluno_id' => $af->getAlunoId(),
            ':data_avaliacao' => $af->getDataAvaliacao(),
            ':peso' => $af->getPeso(),
            ':altura' => $af->getAltura(),
            ':imc' => $af->getImc(),
            ':percentual_gordura' => $af->getPercentualGordura(),
            ':massa_muscular' => $af->getMassaMuscular(),
            ':circunferencia_braco' => $af->getCircunferenciaBraco(),
            ':circunferencia_cintura' => $af->getCircunferenciaCintura(),
            ':circunferencia_quadril' => $af->getCircunferenciaQuadril(),
            ':observacoes' => $af->getObservacoes(),
            ':proxima_avaliacao' => $af->getProximaAvaliacao()
        ]);

        return true;
    }

    // ============================================
    // LISTAR AVALIACOES DE UM ALUNO ESPECIFICO
    // ============================================
    // Busca todas as avaliações físicas de um aluno específico
    // Parâmetros:
    //   - $alunoId: ID do aluno
    // Retorna: array associativo com todas as avaliações do aluno, ordenadas por data (mais recente primeiro)
    public function listarPorAluno($alunoId) {
        // Prepara a query SQL com JOIN para trazer o nome do aluno junto com os dados da avaliação
        // INNER JOIN conecta a tabela AvaliacoesFisicas com Cadastros usando o aluno_id
        // ORDER BY af.data_avaliacao DESC ordena do mais recente para o mais antigo
        $stmt = $this->conn->prepare("
            SELECT af.*, c.nome as aluno_nome
            FROM AvaliacoesFisicas af
            INNER JOIN Cadastros c ON af.aluno_id = c.id
            WHERE af.aluno_id = :aluno_id
            ORDER BY af.data_avaliacao DESC
        ");

        // Executa a query passando o ID do aluno como parâmetro (proteção contra SQL injection)
        $stmt->execute([':aluno_id' => $alunoId]);
        // Retorna todos os resultados como array associativo
        // Cada elemento do array contém: dados da avaliação + nome do aluno
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // BUSCAR ULTIMA AVALIACAO DE UM ALUNO
    // ============================================
    // Busca a avaliação física mais recente de um aluno específico
    // Parâmetros:
    //   - $alunoId: ID do aluno
    // Retorna: array associativo com a última avaliação, ou false se não encontrar
    public function buscarUltimaAvaliacao($alunoId) {
        // Prepara a query SQL para buscar a avaliação mais recente
        // ORDER BY data_avaliacao DESC ordena do mais recente para o mais antigo
        // LIMIT 1 retorna apenas o primeiro resultado (a mais recente)
        $stmt = $this->conn->prepare("
            SELECT * FROM AvaliacoesFisicas
            WHERE aluno_id = :aluno_id
            ORDER BY data_avaliacao DESC
            LIMIT 1
        ");

        // Executa a query passando o ID do aluno como parâmetro
        $stmt->execute([':aluno_id' => $alunoId]);
        // Retorna apenas um resultado (a última avaliação) como array associativo
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("
            SELECT af.*, c.nome as aluno_nome
            FROM AvaliacoesFisicas af
            INNER JOIN Cadastros c ON af.aluno_id = c.id
            WHERE af.id = :id
            LIMIT 1
        ");

        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ============================================
    // LISTAR TODAS AS AVALIACOES
    // ============================================
    // Busca todas as avaliações físicas de todos os alunos
    // Usado principalmente por professores e funcionários para ver todas as avaliações
    // Retorna: array associativo com todas as avaliações, incluindo nome e email do aluno
    public function listarTodos() {
        // Prepara a query SQL com JOIN para trazer nome e email do aluno
        // INNER JOIN conecta AvaliacoesFisicas com Cadastros
        // ORDER BY ordena do mais recente para o mais antigo
        $stmt = $this->conn->query("
            SELECT af.*, c.nome as aluno_nome, c.email as aluno_email
            FROM AvaliacoesFisicas af
            INNER JOIN Cadastros c ON af.aluno_id = c.id
            ORDER BY af.data_avaliacao DESC
        ");

        // Retorna todos os resultados como array associativo
        // Cada elemento contém: dados da avaliação + nome + email do aluno
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAlunosComProximaAvaliacaoVencida() {
        $hoje = date('Y-m-d');
        $stmt = $this->conn->prepare("
            SELECT DISTINCT c.id, c.nome, c.email, c.telefone,
                   MAX(af.proxima_avaliacao) as proxima_avaliacao
            FROM Cadastros c
            INNER JOIN AvaliacoesFisicas af ON c.id = af.aluno_id
            WHERE c.tipo = 'Aluno'
            AND af.proxima_avaliacao IS NOT NULL
            AND af.proxima_avaliacao <= :hoje
            GROUP BY c.id, c.nome, c.email, c.telefone
        ");

        $stmt->execute([':hoje' => $hoje]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // LISTAR ULTIMA AVALIACAO DE CADA ALUNO
    // ============================================
    // Busca a última avaliação física de cada aluno
    // Usado por professores para ver um resumo das últimas avaliações de todos os alunos
    // Parâmetros:
    //   - $nomeFiltro: (opcional) filtro por nome do aluno
    // Retorna: array associativo com a última avaliação de cada aluno
    public function listarUltimasAvaliacoesAlunos($nomeFiltro = null) {
        // Query SQL complexa que usa subquery para encontrar a data máxima de cada aluno
        // A subquery (SELECT aluno_id, MAX(data_avaliacao)...) encontra a data da última avaliação de cada aluno
        // O INNER JOIN conecta apenas as avaliações que correspondem à data máxima de cada aluno
        // WHERE c.tipo = 'Aluno' garante que só busca alunos (não professores ou funcionários)
        $sql = "
            SELECT af.*, c.id as aluno_id, c.nome as aluno_nome, c.email as aluno_email
            FROM AvaliacoesFisicas af
            INNER JOIN Cadastros c ON af.aluno_id = c.id
            INNER JOIN (
                SELECT aluno_id, MAX(data_avaliacao) as max_data
                FROM AvaliacoesFisicas
                GROUP BY aluno_id
            ) ultima ON af.aluno_id = ultima.aluno_id AND af.data_avaliacao = ultima.max_data
            WHERE c.tipo = 'Aluno'
        ";

        // Se foi fornecido um filtro por nome, adiciona à query
        $params = [];
        if ($nomeFiltro) {
            // LIKE com % permite busca parcial (ex: "João" encontra "João Silva")
            $sql .= " AND c.nome LIKE :nome";
            $params[':nome'] = '%' . $nomeFiltro . '%';
        }

        // Ordena os resultados por nome do aluno (A-Z)
        $sql .= " ORDER BY c.nome ASC";

        // Executa a query com os parâmetros
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        // Retorna todas as últimas avaliações como array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

