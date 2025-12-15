<?php
require_once 'Frequencia.php';
require_once 'Connection.php';

class FrequenciaDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    public function registrarFrequencia(Frequencia $f) {
        $stmt = $this->conn->prepare("
            INSERT INTO Frequencias (aluno_id, data_acesso, hora_acesso, tipo_acesso, modalidade, observacoes)
            VALUES (:aluno_id, :data_acesso, :hora_acesso, :tipo_acesso, :modalidade, :observacoes)
        ");

        $stmt->execute([
            ':aluno_id' => $f->getAlunoId(),
            ':data_acesso' => $f->getDataAcesso(),
            ':hora_acesso' => $f->getHoraAcesso(),
            ':tipo_acesso' => $f->getTipoAcesso(),
            ':modalidade' => $f->getModalidade(),
            ':observacoes' => $f->getObservacoes()
        ]);

        return true;
    }

    public function listarPorAluno($alunoId, $dataInicio = null, $dataFim = null) {
        $sql = "
            SELECT f.*, c.nome as aluno_nome
            FROM Frequencias f
            INNER JOIN Cadastros c ON f.aluno_id = c.id
            WHERE f.aluno_id = :aluno_id
        ";

        $params = [':aluno_id' => $alunoId];

        if ($dataInicio) {
            $sql .= " AND f.data_acesso >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim) {
            $sql .= " AND f.data_acesso <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }

        $sql .= " ORDER BY f.data_acesso DESC, f.hora_acesso DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodos($dataInicio = null, $dataFim = null) {
        $sql = "
            SELECT f.*, c.nome as aluno_nome, c.email as aluno_email
            FROM Frequencias f
            INNER JOIN Cadastros c ON f.aluno_id = c.id
            WHERE 1=1
        ";

        $params = [];

        if ($dataInicio) {
            $sql .= " AND f.data_acesso >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim) {
            $sql .= " AND f.data_acesso <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }

        $sql .= " ORDER BY f.data_acesso DESC, f.hora_acesso DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarFrequenciaPorAluno($alunoId, $dataInicio = null, $dataFim = null) {
        $sql = "
            SELECT COUNT(*) as total
            FROM Frequencias
            WHERE aluno_id = :aluno_id
        ";

        $params = [':aluno_id' => $alunoId];

        if ($dataInicio) {
            $sql .= " AND data_acesso >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim) {
            $sql .= " AND data_acesso <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    public function gerarRelatorioOcupacao($dataInicio = null, $dataFim = null) {
        // Usar agendamentos ao invés de frequências, pois nem todos alunos registram frequência
        // Mas também incluir frequências se existirem
        $sql = "
            SELECT 
                DATE(a.data_aula) as data,
                a.horario,
                COUNT(DISTINCT a.id) as total_acessos,
                COUNT(DISTINCT a.aluno_id) as alunos_unicos
            FROM Agendamentos a
            WHERE a.status = 'Agendada'
        ";

        $params = [];

        if ($dataInicio) {
            $sql .= " AND a.data_aula >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim) {
            $sql .= " AND a.data_aula <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }

        // Filtrar apenas aulas que já terminaram
        $sql .= " AND (
            a.data_aula < CURDATE() 
            OR (
                a.data_aula = CURDATE() 
                AND SUBSTRING_INDEX(a.horario, '-', -1) < TIME_FORMAT(CURTIME(), '%H:%i')
            )
        )";

        $sql .= " GROUP BY DATE(a.data_aula), a.horario ORDER BY data DESC, a.horario DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

