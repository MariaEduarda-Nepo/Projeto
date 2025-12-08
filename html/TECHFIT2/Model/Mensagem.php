<?php
class Mensagem {
    private $id;
    private $remetenteId;
    private $destinatarioId;
    private $tipoDestinatario;
    private $assunto;
    private $mensagem;
    private $lida;
    private $dataEnvio;

    public function __construct($remetenteId, $destinatarioId, $assunto, $mensagem, $tipoDestinatario = 'Aluno', $lida = false, $id = null, $dataEnvio = null) {
        $this->id = $id;
        $this->remetenteId = $remetenteId;
        $this->destinatarioId = $destinatarioId;
        $this->tipoDestinatario = $tipoDestinatario;
        $this->assunto = $assunto;
        $this->mensagem = $mensagem;
        $this->lida = $lida;
        $this->dataEnvio = $dataEnvio;
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getRemetenteId() { return $this->remetenteId; }
    public function getDestinatarioId() { return $this->destinatarioId; }
    public function getTipoDestinatario() { return $this->tipoDestinatario; }
    public function getAssunto() { return $this->assunto; }
    public function getMensagem() { return $this->mensagem; }
    public function getLida() { return $this->lida; }
    public function getDataEnvio() { return $this->dataEnvio; }

    // SETTERS
    public function setRemetenteId($remetenteId) { $this->remetenteId = $remetenteId; }
    public function setDestinatarioId($destinatarioId) { $this->destinatarioId = $destinatarioId; }
    public function setTipoDestinatario($tipoDestinatario) { $this->tipoDestinatario = $tipoDestinatario; }
    public function setAssunto($assunto) { $this->assunto = $assunto; }
    public function setMensagem($mensagem) { $this->mensagem = $mensagem; }
    public function setLida($lida) { $this->lida = $lida; }
}
?>

