<?php
class Frequencia {
    private $id;
    private $alunoId;
    private $dataAcesso;
    private $horaAcesso;
    private $tipoAcesso;
    private $modalidade;
    private $observacoes;
    private $dataRegistro;

    public function __construct($alunoId, $dataAcesso, $horaAcesso, $tipoAcesso = 'Entrada', $modalidade = null, $observacoes = null, $id = null, $dataRegistro = null) {
        $this->id = $id;
        $this->alunoId = $alunoId;
        $this->dataAcesso = $dataAcesso;
        $this->horaAcesso = $horaAcesso;
        $this->tipoAcesso = $tipoAcesso;
        $this->modalidade = $modalidade;
        $this->observacoes = $observacoes;
        $this->dataRegistro = $dataRegistro;
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getAlunoId() { return $this->alunoId; }
    public function getDataAcesso() { return $this->dataAcesso; }
    public function getHoraAcesso() { return $this->horaAcesso; }
    public function getTipoAcesso() { return $this->tipoAcesso; }
    public function getModalidade() { return $this->modalidade; }
    public function getObservacoes() { return $this->observacoes; }
    public function getDataRegistro() { return $this->dataRegistro; }

    // SETTERS
    public function setAlunoId($alunoId) { $this->alunoId = $alunoId; }
    public function setDataAcesso($dataAcesso) { $this->dataAcesso = $dataAcesso; }
    public function setHoraAcesso($horaAcesso) { $this->horaAcesso = $horaAcesso; }
    public function setTipoAcesso($tipoAcesso) { $this->tipoAcesso = $tipoAcesso; }
    public function setModalidade($modalidade) { $this->modalidade = $modalidade; }
    public function setObservacoes($observacoes) { $this->observacoes = $observacoes; }
}
?>

