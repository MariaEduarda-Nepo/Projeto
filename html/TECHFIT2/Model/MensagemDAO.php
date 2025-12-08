<?php
require_once 'Mensagem.php';
require_once 'Connection.php';

class MensagemDAO {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    public function criarMensagem(Mensagem $m) {
        $stmt = $this->conn->prepare("
            INSERT INTO Mensagens (remetente_id, destinatario_id, tipo_destinatario, assunto, mensagem, lida)
            VALUES (:remetente_id, :destinatario_id, :tipo_destinatario, :assunto, :mensagem, :lida)
        ");

        $stmt->execute([
            ':remetente_id' => $m->getRemetenteId(),
            ':destinatario_id' => $m->getDestinatarioId(),
            ':tipo_destinatario' => $m->getTipoDestinatario(),
            ':assunto' => $m->getAssunto(),
            ':mensagem' => $m->getMensagem(),
            ':lida' => $m->getLida() ? 1 : 0
        ]);

        return true;
    }

    public function listarPorDestinatario($destinatarioId) {
        $stmt = $this->conn->prepare("
            SELECT m.*, 
                   r.nome as remetente_nome,
                   d.nome as destinatario_nome
            FROM Mensagens m
            LEFT JOIN Cadastros r ON m.remetente_id = r.id
            INNER JOIN Cadastros d ON m.destinatario_id = d.id
            WHERE m.destinatario_id = :destinatario_id
            ORDER BY m.data_envio DESC
        ");

        $stmt->execute([':destinatario_id' => $destinatarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorRemetente($remetenteId) {
        $stmt = $this->conn->prepare("
            SELECT m.*, 
                   r.nome as remetente_nome,
                   d.nome as destinatario_nome
            FROM Mensagens m
            INNER JOIN Cadastros r ON m.remetente_id = r.id
            INNER JOIN Cadastros d ON m.destinatario_id = d.id
            WHERE m.remetente_id = :remetente_id
            ORDER BY m.data_envio DESC
        ");

        $stmt->execute([':remetente_id' => $remetenteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("
            SELECT m.*, 
                   r.nome as remetente_nome,
                   d.nome as destinatario_nome
            FROM Mensagens m
            LEFT JOIN Cadastros r ON m.remetente_id = r.id
            INNER JOIN Cadastros d ON m.destinatario_id = d.id
            WHERE m.id = :id
            LIMIT 1
        ");

        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function marcarComoLida($id, $destinatarioId) {
        $stmt = $this->conn->prepare("
            UPDATE Mensagens 
            SET lida = 1
            WHERE id = :id AND destinatario_id = :destinatario_id
        ");

        $stmt->execute([
            ':id' => $id,
            ':destinatario_id' => $destinatarioId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function contarNaoLidas($destinatarioId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total
            FROM Mensagens
            WHERE destinatario_id = :destinatario_id AND lida = 0
        ");

        $stmt->execute([':destinatario_id' => $destinatarioId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total'] : 0;
    }

    public function enviarMensagemParaTodos($remetenteId, $tipoDestinatario, $assunto, $mensagem) {
        // Buscar todos os alunos do tipo especificado
        $stmt = $this->conn->prepare("
            SELECT id FROM Cadastros WHERE tipo = :tipo
        ");
        $stmt->execute([':tipo' => $tipoDestinatario]);
        $destinatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $enviadas = 0;
        foreach ($destinatarios as $dest) {
            $m = new Mensagem($remetenteId, $dest['id'], $assunto, $mensagem, $tipoDestinatario, false);
            if ($this->criarMensagem($m)) {
                $enviadas++;
            }
        }

        return $enviadas;
    }
}
?>

