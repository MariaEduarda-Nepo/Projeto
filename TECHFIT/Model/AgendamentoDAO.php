<?php
require_once 'Agendamento.php';
require_once 'Connection.php';

class AgendamentoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    // -----------------------------
    // CRIAR AGENDAMENTO
    // -----------------------------
    public function criarAgendamento(Agendamento $a) {
        // Verificar se o aluno já está agendado na mesma modalidade, data e horário
        $stmt = $this->conn->prepare("
            SELECT id FROM Agendamentos 
            WHERE aluno_id = :aluno_id 
            AND modalidade = :modalidade
            AND data_aula = :data_aula 
            AND horario = :horario 
            AND status = 'Agendada'
            LIMIT 1
        ");
        
        $stmt->execute([
            ':aluno_id' => $a->getAlunoId(),
            ':modalidade' => $a->getModalidade(),
            ':data_aula' => $a->getDataAula(),
            ':horario' => $a->getHorario()
        ]);

        if ($stmt->fetch()) {
            return "Você já está agendado nesta modalidade, data e horário!";
        }

        // Verificar limite de 20 alunos por turma
        $totalAlunos = $this->contarAlunosPorAula(
            $a->getModalidade(),
            $a->getDataAula(),
            $a->getHorario()
        );

        if ($totalAlunos >= 20) {
            return "Esta turma está completa! Limite de 20 alunos atingido.";
        }

        // Criar agendamento (aula em grupo)
        $stmt2 = $this->conn->prepare("
            INSERT INTO Agendamentos (aluno_id, professor_id, modalidade, data_aula, horario, status)
            VALUES (:aluno_id, :professor_id, :modalidade, :data_aula, :horario, :status)
        ");

        $stmt2->execute([
            ':aluno_id' => $a->getAlunoId(),
            ':professor_id' => $a->getProfessorId(),
            ':modalidade' => $a->getModalidade(),
            ':data_aula' => $a->getDataAula(),
            ':horario' => $a->getHorario(),
            ':status' => $a->getStatus()
        ]);

        return true;
    }

    // -----------------------------
    // LISTAR AGENDAMENTOS DO ALUNO
    // -----------------------------
    public function listarPorAluno($alunoId) {
        $stmt = $this->conn->prepare("
            SELECT 
                a.*, 
                p.nome as professor_nome,
                (SELECT COUNT(*) 
                 FROM Agendamentos a2 
                 WHERE a2.modalidade = a.modalidade 
                 AND a2.data_aula = a.data_aula 
                 AND a2.horario = a.horario 
                 AND a2.status = 'Agendada') as total_alunos
            FROM Agendamentos a
            INNER JOIN Cadastros p ON a.professor_id = p.id
            WHERE a.aluno_id = :aluno_id
            ORDER BY a.data_aula DESC, a.horario DESC
        ");

        $stmt->execute([':aluno_id' => $alunoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // LISTAR AGENDAMENTOS DO PROFESSOR
    // -----------------------------
    public function listarPorProfessor($professorId) {
        $stmt = $this->conn->prepare("
            SELECT 
                a.*, 
                al.nome as aluno_nome,
                (SELECT COUNT(*) 
                 FROM Agendamentos a2 
                 WHERE a2.modalidade = a.modalidade 
                 AND a2.data_aula = a.data_aula 
                 AND a2.horario = a.horario 
                 AND a2.status = 'Agendada') as total_alunos
            FROM Agendamentos a
            INNER JOIN Cadastros al ON a.aluno_id = al.id
            WHERE a.professor_id = :professor_id
            ORDER BY a.data_aula DESC, a.horario DESC
        ");

        $stmt->execute([':professor_id' => $professorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // LISTAR PROFESSORES POR MODALIDADE
    // -----------------------------
    public function listarProfessoresPorModalidade($modalidade) {
        // Primeiro tenta buscar na tabela ProfessorModalidade
        $stmt = $this->conn->prepare("
            SELECT c.id, c.nome, c.email
            FROM Cadastros c
            INNER JOIN ProfessorModalidade pm ON c.id = pm.professor_id
            WHERE c.tipo = 'Professor' AND pm.modalidade = :modalidade
            ORDER BY c.nome
        ");

        $stmt->execute([':modalidade' => $modalidade]);
        $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Se não houver na tabela, busca professores que já deram aula nessa modalidade (compatibilidade)
        if (empty($professores)) {
            $stmt2 = $this->conn->prepare("
                SELECT DISTINCT c.id, c.nome, c.email
                FROM Cadastros c
                INNER JOIN Agendamentos a ON c.id = a.professor_id
                WHERE c.tipo = 'Professor' AND a.modalidade = :modalidade
                ORDER BY c.nome
            ");
            $stmt2->execute([':modalidade' => $modalidade]);
            $professores = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $professores;
    }

    // -----------------------------
    // LISTAR TODOS OS PROFESSORES
    // -----------------------------
    public function listarProfessores() {
        $stmt = $this->conn->query("
            SELECT id, nome, email
            FROM Cadastros
            WHERE tipo = 'Professor'
            ORDER BY nome
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // CONTAR ALUNOS POR AULA (para mostrar quantos alunos estão na aula)
    // -----------------------------
    public function contarAlunosPorAula($modalidade, $dataAula, $horario) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total
            FROM Agendamentos
            WHERE modalidade = :modalidade
            AND data_aula = :data_aula
            AND horario = :horario
            AND status = 'Agendada'
        ");

        $stmt->execute([
            ':modalidade' => $modalidade,
            ':data_aula' => $dataAula,
            ':horario' => $horario
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    // -----------------------------
    // LISTAR TURMAS DISPONÍVEIS (agrupar por modalidade, data e horário)
    // -----------------------------
    public function listarTurmas($dataInicio = null, $dataFim = null) {
        $sql = "
            SELECT 
                modalidade,
                data_aula,
                horario,
                COUNT(*) as total_alunos,
                GROUP_CONCAT(DISTINCT professor_id) as professores_ids
            FROM Agendamentos
            WHERE status = 'Agendada'
        ";

        $params = [];

        if ($dataInicio) {
            $sql .= " AND data_aula >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }

        if ($dataFim) {
            $sql .= " AND data_aula <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }

        // Filtrar apenas aulas que já terminaram
        // Extrai a hora de término do horário (formato: "07:00-08:00")
        // Compara data_aula + hora_fim com data/hora atual
        $sql .= " AND (
            data_aula < CURDATE() 
            OR (
                data_aula = CURDATE() 
                AND SUBSTRING_INDEX(horario, '-', -1) < TIME_FORMAT(CURTIME(), '%H:%i')
            )
        )";

        $sql .= " GROUP BY modalidade, data_aula, horario ORDER BY data_aula, horario";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // CANCELAR AGENDAMENTO (por aluno) - Remove a participação do aluno na aula
    // -----------------------------
    public function cancelarAgendamento($id, $alunoId) {
        // Verificar se o agendamento pertence ao aluno antes de deletar
        $stmtVerificar = $this->conn->prepare("
            SELECT id FROM Agendamentos 
            WHERE id = :id AND aluno_id = :aluno_id AND status = 'Agendada'
        ");
        $stmtVerificar->execute([
            ':id' => $id,
            ':aluno_id' => $alunoId
        ]);
        
        if ($stmtVerificar->rowCount() === 0) {
            return false; // Agendamento não encontrado ou não pertence ao aluno
        }
        
        // Deletar o agendamento (remove a participação do aluno na aula)
        $stmt = $this->conn->prepare("
            DELETE FROM Agendamentos 
            WHERE id = :id AND aluno_id = :aluno_id
        ");

        $stmt->execute([
            ':id' => $id,
            ':aluno_id' => $alunoId
        ]);

        return $stmt->rowCount() > 0;
    }

    // -----------------------------
    // CANCELAR AGENDAMENTO (por professor)
    // -----------------------------
    public function cancelarAgendamentoPorProfessor($id, $professorId) {
        $stmt = $this->conn->prepare("
            UPDATE Agendamentos 
            SET status = 'Cancelada'
            WHERE id = :id AND professor_id = :professor_id
        ");

        $stmt->execute([
            ':id' => $id,
            ':professor_id' => $professorId
        ]);

        return $stmt->rowCount() > 0;
    }

    // -----------------------------
    // BUSCAR AGENDAMENTO POR ID
    // -----------------------------
    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("
            SELECT 
                a.*, 
                p.nome as professor_nome, 
                al.nome as aluno_nome,
                (SELECT COUNT(*) 
                 FROM Agendamentos a2 
                 WHERE a2.modalidade = a.modalidade 
                 AND a2.data_aula = a.data_aula 
                 AND a2.horario = a.horario 
                 AND a2.status = 'Agendada') as total_alunos
            FROM Agendamentos a
            INNER JOIN Cadastros p ON a.professor_id = p.id
            INNER JOIN Cadastros al ON a.aluno_id = al.id
            WHERE a.id = :id
            LIMIT 1
        ");

        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // LISTAR TODOS OS AGENDAMENTOS (para funcionários)
    // -----------------------------
    public function listarTodos() {
        $stmt = $this->conn->query("
            SELECT 
                a.*, 
                p.nome as professor_nome, 
                al.nome as aluno_nome,
                (SELECT COUNT(*) 
                 FROM Agendamentos a2 
                 WHERE a2.modalidade = a.modalidade 
                 AND a2.data_aula = a.data_aula 
                 AND a2.horario = a.horario 
                 AND a2.status = 'Agendada') as total_alunos
            FROM Agendamentos a
            INNER JOIN Cadastros p ON a.professor_id = p.id
            INNER JOIN Cadastros al ON a.aluno_id = al.id
            ORDER BY a.data_aula DESC, a.horario DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -----------------------------
    // LISTAR ALUNOS DE UMA TURMA ESPECÍFICA
    // -----------------------------
    public function listarAlunosPorTurma($modalidade, $dataAula, $horario) {
        $stmt = $this->conn->prepare("
            SELECT 
                a.id as agendamento_id,
                al.id as aluno_id,
                al.nome as aluno_nome,
                al.email as aluno_email,
                p.id as professor_id,
                p.nome as professor_nome,
                a.data_agendamento
            FROM Agendamentos a
            INNER JOIN Cadastros al ON a.aluno_id = al.id
            INNER JOIN Cadastros p ON a.professor_id = p.id
            WHERE a.modalidade = :modalidade
            AND a.data_aula = :data_aula
            AND a.horario = :horario
            AND a.status = 'Agendada'
            ORDER BY al.nome
        ");

        $stmt->execute([
            ':modalidade' => $modalidade,
            ':data_aula' => $dataAula,
            ':horario' => $horario
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
