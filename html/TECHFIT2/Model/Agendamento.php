<?php
class Agendamento {
    private $id;
    private $alunoId;
    private $professorId;
    private $modalidade;
    private $dataAula;
    private $horario;
    private $status;
    private $dataAgendamento;

    public function __construct($alunoId, $professorId, $modalidade, $dataAula, $horario, $status = 'Agendada', $id = null, $dataAgendamento = null) {
        $this->id = $id;
        $this->alunoId = $alunoId;
        $this->professorId = $professorId;
        $this->modalidade = $modalidade;
        $this->dataAula = $dataAula;
        $this->horario = $horario;
        $this->status = $status;
        $this->dataAgendamento = $dataAgendamento;
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getAlunoId() { return $this->alunoId; }
    public function getProfessorId() { return $this->professorId; }
    public function getModalidade() { return $this->modalidade; }
    public function getDataAula() { return $this->dataAula; }
    public function getHorario() { return $this->horario; }
    public function getStatus() { return $this->status; }
    public function getDataAgendamento() { return $this->dataAgendamento; }

    // SETTERS
    public function setAlunoId($alunoId) { $this->alunoId = $alunoId; }
    public function setProfessorId($professorId) { $this->professorId = $professorId; }
    public function setModalidade($modalidade) { $this->modalidade = $modalidade; }
    public function setDataAula($dataAula) { $this->dataAula = $dataAula; }
    public function setHorario($horario) { $this->horario = $horario; }
    public function setStatus($status) { $this->status = $status; }
}
?>
