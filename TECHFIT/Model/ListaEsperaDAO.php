<?php
require_once 'ListaEspera.php';
require_once 'Connection.php';

class ListaEsperaDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    public function criarListaEspera(ListaEspera $le) {
        // Verificar se já está na lista de espera
        $stmt = $this->conn->prepare("
            SELECT id FROM ListaEspera 
            WHERE aluno_id = :aluno_id 
            AND modalidade = :modalidade
            AND data_aula = :data_aula 
            AND horario = :horario 
            AND status = 'Aguardando'
            LIMIT 1
        ");
        
        $stmt->execute([
            ':aluno_id' => $le->getAlunoId(),
            ':modalidade' => $le->getModalidade(),
            ':data_aula' => $le->getDataAula(),
            ':horario' => $le->getHorario()
        ]);

        if ($stmt->fetch()) {
            return "Você já está na lista de espera para esta turma!";
        }

        $stmt2 = $this->conn->prepare("
            INSERT INTO ListaEspera (aluno_id, modalidade, data_aula, horario, status)
            VALUES (:aluno_id, :modalidade, :data_aula, :horario, :status)
        ");

        $stmt2->execute([
            ':aluno_id' => $le->getAlunoId(),
            ':modalidade' => $le->getModalidade(),
            ':data_aula' => $le->getDataAula(),
            ':horario' => $le->getHorario(),
            ':status' => $le->getStatus()
        ]);

        return true;
    }

    public function listarPorAluno($alunoId) {
        $stmt = $this->conn->prepare("
            SELECT le.*, c.nome as aluno_nome
            FROM ListaEspera le
            INNER JOIN Cadastros c ON le.aluno_id = c.id
            WHERE le.aluno_id = :aluno_id
            ORDER BY le.data_inscricao ASC
        ");

        $stmt->execute([':aluno_id' => $alunoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorTurma($modalidade, $dataAula, $horario) {
        $stmt = $this->conn->prepare("
            SELECT le.*, c.nome as aluno_nome, c.email as aluno_email, c.telefone as aluno_telefone
            FROM ListaEspera le
            INNER JOIN Cadastros c ON le.aluno_id = c.id
            WHERE le.modalidade = :modalidade
            AND le.data_aula = :data_aula
            AND le.horario = :horario
            AND le.status = 'Aguardando'
            ORDER BY le.data_inscricao ASC
        ");

        $stmt->execute([
            ':modalidade' => $modalidade,
            ':data_aula' => $dataAula,
            ':horario' => $horario
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removerListaEspera($id, $alunoId) {
        $stmt = $this->conn->prepare("
            DELETE FROM ListaEspera 
            WHERE id = :id AND aluno_id = :aluno_id
        ");

        $stmt->execute([
            ':id' => $id,
            ':aluno_id' => $alunoId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function atualizarStatus($id, $status) {
        $stmt = $this->conn->prepare("
            UPDATE ListaEspera 
            SET status = :status
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id,
            ':status' => $status
        ]);

        return $stmt->rowCount() > 0;
    }

    public function buscarPrimeiroNaFila($modalidade, $dataAula, $horario) {
        $stmt = $this->conn->prepare("
            SELECT le.*, c.nome as aluno_nome
            FROM ListaEspera le
            INNER JOIN Cadastros c ON le.aluno_id = c.id
            WHERE le.modalidade = :modalidade
            AND le.data_aula = :data_aula
            AND le.horario = :horario
            AND le.status = 'Aguardando'
            ORDER BY le.data_inscricao ASC
            LIMIT 1
        ");

        $stmt->execute([
            ':modalidade' => $modalidade,
            ':data_aula' => $dataAula,
            ':horario' => $horario
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

