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

    // ============================================
    // METODOS DE LISTAGEM (READ)
    // ============================================
    // Estes métodos repassam as chamadas para o DAO
    // O Controller atua como intermediário entre a View e o DAO
    
    // Lista todas as avaliações de um aluno específico
    public function listarPorAluno($alunoId) {
        return $this->dao->listarPorAluno($alunoId);
    }

    // Busca a última avaliação de um aluno específico
    public function buscarUltimaAvaliacao($alunoId) {
        return $this->dao->buscarUltimaAvaliacao($alunoId);
    }

    // Busca uma avaliação específica pelo ID
    public function buscarPorId($id) {
        return $this->dao->buscarPorId($id);
    }

    // Lista todas as avaliações de todos os alunos
    public function listarTodos() {
        return $this->dao->listarTodos();
    }

    // Lista alunos que têm próxima avaliação vencida
    public function listarAlunosComProximaAvaliacaoVencida() {
        return $this->dao->listarAlunosComProximaAvaliacaoVencida();
    }

    // Lista a última avaliação de cada aluno (com filtro opcional por nome)
    public function listarUltimasAvaliacoesAlunos($nomeFiltro = null) {
        return $this->dao->listarUltimasAvaliacoesAlunos($nomeFiltro);
    }
}
?>

