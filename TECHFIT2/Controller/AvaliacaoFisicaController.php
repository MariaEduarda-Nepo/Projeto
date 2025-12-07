<?php
require_once __DIR__ . '/../Model/AvaliacaoFisicaDAO.php';
require_once __DIR__ . '/../Model/AvaliacaoFisica.php';

class AvaliacaoFisicaController {
    private $dao;

    public function __construct() {
        $this->dao = new AvaliacaoFisicaDAO();
    }

    public function criar($alunoId, $dataAvaliacao, $peso = null, $altura = null, $percentualGordura = null, $massaMuscular = null, $circunferenciaBraco = null, $circunferenciaCintura = null, $circunferenciaQuadril = null, $observacoes = null, $proximaAvaliacao = null) {
        if (empty($dataAvaliacao)) {
            return "Data da avaliação é obrigatória!";
        }

        $avaliacao = new AvaliacaoFisica(
            $alunoId, 
            $dataAvaliacao, 
            $peso, 
            $altura, 
            null, // IMC será calculado no DAO
            $percentualGordura, 
            $massaMuscular, 
            $circunferenciaBraco, 
            $circunferenciaCintura, 
            $circunferenciaQuadril, 
            $observacoes, 
            $proximaAvaliacao
        );

        return $this->dao->criarAvaliacao($avaliacao);
    }

    public function listarPorAluno($alunoId) {
        return $this->dao->listarPorAluno($alunoId);
    }

    public function buscarUltimaAvaliacao($alunoId) {
        return $this->dao->buscarUltimaAvaliacao($alunoId);
    }

    public function buscarPorId($id) {
        return $this->dao->buscarPorId($id);
    }

    public function listarTodos() {
        return $this->dao->listarTodos();
    }

    public function listarAlunosComProximaAvaliacaoVencida() {
        return $this->dao->listarAlunosComProximaAvaliacaoVencida();
    }
}
?>

