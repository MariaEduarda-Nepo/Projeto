<?php
require_once __DIR__ . '/../Model/FrequenciaDAO.php';
require_once __DIR__ . '/../Model/Frequencia.php';

class FrequenciaController {
    private $dao;

    public function __construct() {
        $this->dao = new FrequenciaDAO();
    }

    public function registrar($alunoId, $modalidade = null, $observacoes = null) {
        $dataAcesso = date('Y-m-d');
        $horaAcesso = date('H:i:s');
        
        $frequencia = new Frequencia($alunoId, $dataAcesso, $horaAcesso, 'Entrada', $modalidade, $observacoes);
        return $this->dao->registrarFrequencia($frequencia);
    }

    public function listarPorAluno($alunoId, $dataInicio = null, $dataFim = null) {
        return $this->dao->listarPorAluno($alunoId, $dataInicio, $dataFim);
    }

    public function listarTodos($dataInicio = null, $dataFim = null) {
        return $this->dao->listarTodos($dataInicio, $dataFim);
    }

    public function contarFrequenciaPorAluno($alunoId, $dataInicio = null, $dataFim = null) {
        return $this->dao->contarFrequenciaPorAluno($alunoId, $dataInicio, $dataFim);
    }

    public function gerarRelatorioOcupacao($dataInicio = null, $dataFim = null) {
        return $this->dao->gerarRelatorioOcupacao($dataInicio, $dataFim);
    }
}
?>

