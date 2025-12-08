<?php
require_once __DIR__ . '/../Model/ListaEsperaDAO.php';
require_once __DIR__ . '/../Model/ListaEspera.php';

class ListaEsperaController {
    private $dao;

    public function __construct() {
        $this->dao = new ListaEsperaDAO();
    }

    public function criar($alunoId, $modalidade, $dataAula, $horario) {
        if (empty($modalidade) || empty($dataAula) || empty($horario)) {
            return "Preencha todos os campos!";
        }

        if (!in_array($modalidade, ['Box', 'Muay Thai'])) {
            return "Modalidade inválida!";
        }

        $listaEspera = new ListaEspera($alunoId, $modalidade, $dataAula, $horario);
        return $this->dao->criarListaEspera($listaEspera);
    }

    public function listarPorAluno($alunoId) {
        return $this->dao->listarPorAluno($alunoId);
    }

    public function listarPorTurma($modalidade, $dataAula, $horario) {
        return $this->dao->listarPorTurma($modalidade, $dataAula, $horario);
    }

    public function remover($id, $alunoId) {
        return $this->dao->removerListaEspera($id, $alunoId);
    }

    public function buscarPrimeiroNaFila($modalidade, $dataAula, $horario) {
        return $this->dao->buscarPrimeiroNaFila($modalidade, $dataAula, $horario);
    }
}
?>

