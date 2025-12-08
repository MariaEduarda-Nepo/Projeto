<?php
class ListaEspera {
    private $id;
    private $alunoId;
    private $modalidade;
    private $dataAula;
    private $horario;
    private $dataInscricao;
    private $status;

    public function __construct($alunoId, $modalidade, $dataAula, $horario, $status = 'Aguardando', $id = null, $dataInscricao = null) {
        $this->id = $id;
        $this->alunoId = $alunoId;
        $this->modalidade = $modalidade;
        $this->dataAula = $dataAula;
        $this->horario = $horario;
        $this->status = $status;
        $this->dataInscricao = $dataInscricao;
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getAlunoId() { return $this->alunoId; }
    public function getModalidade() { return $this->modalidade; }
    public function getDataAula() { return $this->dataAula; }
    public function getHorario() { return $this->horario; }
    public function getDataInscricao() { return $this->dataInscricao; }
    public function getStatus() { return $this->status; }

    // SETTERS
    public function setAlunoId($alunoId) { $this->alunoId = $alunoId; }
    public function setModalidade($modalidade) { $this->modalidade = $modalidade; }
    public function setDataAula($dataAula) { $this->dataAula = $dataAula; }
    public function setHorario($horario) { $this->horario = $horario; }
    public function setStatus($status) { $this->status = $status; }
}
?>

