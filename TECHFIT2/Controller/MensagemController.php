<?php
require_once __DIR__ . '/../Model/MensagemDAO.php';
require_once __DIR__ . '/../Model/Mensagem.php';

class MensagemController {
    private $dao;

    public function __construct() {
        $this->dao = new MensagemDAO();
    }

    public function criar($remetenteId, $destinatarioId, $assunto, $mensagem, $tipoDestinatario = 'Aluno') {
        if (empty($assunto) || empty($mensagem)) {
            return "Assunto e mensagem são obrigatórios!";
        }

        $msg = new Mensagem($remetenteId, $destinatarioId, $assunto, $mensagem, $tipoDestinatario, false);
        return $this->dao->criarMensagem($msg);
    }

    public function enviarParaTodos($remetenteId, $tipoDestinatario, $assunto, $mensagem) {
        if (empty($assunto) || empty($mensagem)) {
            return "Assunto e mensagem são obrigatórios!";
        }

        return $this->dao->enviarMensagemParaTodos($remetenteId, $tipoDestinatario, $assunto, $mensagem);
    }

    public function listarPorDestinatario($destinatarioId) {
        return $this->dao->listarPorDestinatario($destinatarioId);
    }

    public function listarPorRemetente($remetenteId) {
        return $this->dao->listarPorRemetente($remetenteId);
    }

    public function buscarPorId($id) {
        return $this->dao->buscarPorId($id);
    }

    public function marcarComoLida($id, $destinatarioId) {
        return $this->dao->marcarComoLida($id, $destinatarioId);
    }

    public function contarNaoLidas($destinatarioId) {
        return $this->dao->contarNaoLidas($destinatarioId);
    }
}
?>

