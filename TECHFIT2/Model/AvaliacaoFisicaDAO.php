<?php
require_once 'AvaliacaoFisica.php';
require_once 'Connection.php';

class AvaliacaoFisicaDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    public function criarAvaliacao(AvaliacaoFisica $af) {
        // Calcular IMC se peso e altura estiverem presentes
        if ($af->getPeso() && $af->getAltura()) {
            $imc = $af->getPeso() / ($af->getAltura() * $af->getAltura());
            $af->setImc(round($imc, 2));
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

    public function listarPorAluno($alunoId) {
        $stmt = $this->conn->prepare("
            SELECT af.*, c.nome as aluno_nome
            FROM AvaliacoesFisicas af
            INNER JOIN Cadastros c ON af.aluno_id = c.id
            WHERE af.aluno_id = :aluno_id
            ORDER BY af.data_avaliacao DESC
        ");

        $stmt->execute([':aluno_id' => $alunoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarUltimaAvaliacao($alunoId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM AvaliacoesFisicas
            WHERE aluno_id = :aluno_id
            ORDER BY data_avaliacao DESC
            LIMIT 1
        ");

        $stmt->execute([':aluno_id' => $alunoId]);
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

    public function listarTodos() {
        $stmt = $this->conn->query("
            SELECT af.*, c.nome as aluno_nome, c.email as aluno_email
            FROM AvaliacoesFisicas af
            INNER JOIN Cadastros c ON af.aluno_id = c.id
            ORDER BY af.data_avaliacao DESC
        ");

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
}
?>

