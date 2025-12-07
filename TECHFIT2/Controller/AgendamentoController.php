<?php
require_once __DIR__ . '/../Model/AgendamentoDAO.php';
require_once __DIR__ . '/../Model/Agendamento.php';
require_once __DIR__ . '/ListaEsperaController.php';

class AgendamentoController {

    private $dao;
    private $listaEsperaController;

    public function __construct() {
        $this->dao = new AgendamentoDAO();
        $this->listaEsperaController = new ListaEsperaController();
    }

    // ------------------ CRIAR AGENDAMENTO ------------------
    public function criar($alunoId, $professorId, $modalidade, $dataAula, $horario) {
        // Validações
        if (empty($modalidade)) {
            return "Selecione uma modalidade!";
        }

        if (!in_array($modalidade, ['Box', 'Muay Thai'])) {
            return "Modalidade inválida!";
        }

        if (empty($dataAula)) {
            return "Selecione uma data!";
        }

        if (empty($horario)) {
            return "Selecione um horário!";
        }

        // Validar se o horário está na lista permitida
        $horariosPermitidos = [
            '07:00-08:00',
            '09:00-10:00',
            '11:00-12:00',
            '13:00-14:00',
            '14:00-16:00',
            '16:00-18:00',
            '18:00-20:00'
        ];

        if (!in_array($horario, $horariosPermitidos)) {
            return "Horário inválido! Selecione um horário disponível.";
        }

        // Verificar se a data não é no passado
        $dataAulaObj = new DateTime($dataAula);
        $hoje = new DateTime();
        $hoje->setTime(0, 0, 0);

        if ($dataAulaObj < $hoje) {
            return "Não é possível agendar aulas no passado!";
        }

        // Verificar se é sábado (6) ou domingo (0)
        $diaSemana = (int)$dataAulaObj->format('w');
        if ($diaSemana === 0) {
            return "Não é possível agendar aulas aos domingos!";
        }
        if ($diaSemana === 6) {
            return "Não é possível agendar aulas aos sábados!";
        }

        // Verificar se é feriado
        if ($this->ehFeriado($dataAulaObj)) {
            return "Não é possível agendar aulas em feriados!";
        }

        // Verificar se o aluno já está na lista de espera para esta turma
        $listaEspera = $this->listaEsperaController->listarPorTurma($modalidade, $dataAula, $horario);
        foreach ($listaEspera as $item) {
            if ($item['aluno_id'] == $alunoId) {
                return "Você já está na lista de espera para esta turma!";
            }
        }

        $agendamento = new Agendamento($alunoId, $professorId, $modalidade, $dataAula, $horario);
        $resultado = $this->dao->criarAgendamento($agendamento);
        
        // Se a turma estiver cheia, o DAO retorna mensagem de erro
        // A view tratará de adicionar à lista de espera
        if ($resultado === true) {
            // Verificar se há alguém na lista de espera para esta turma
            $primeiroNaFila = $this->listaEsperaController->buscarPrimeiroNaFila($modalidade, $dataAula, $horario);
            // Notificação pode ser implementada aqui no futuro
        }
        
        return $resultado;
    }

    // ------------------ LISTAR AGENDAMENTOS DO ALUNO ------------------
    public function listarPorAluno($alunoId) {
        return $this->dao->listarPorAluno($alunoId);
    }

    // ------------------ LISTAR AGENDAMENTOS DO PROFESSOR ------------------
    public function listarPorProfessor($professorId) {
        return $this->dao->listarPorProfessor($professorId);
    }

    // ------------------ LISTAR PROFESSORES ------------------
    public function listarProfessores() {
        return $this->dao->listarProfessores();
    }

    // ------------------ LISTAR PROFESSORES POR MODALIDADE ------------------
    public function listarProfessoresPorModalidade($modalidade) {
        return $this->dao->listarProfessoresPorModalidade($modalidade);
    }

    // ------------------ CONTAR ALUNOS POR AULA ------------------
    public function contarAlunosPorAula($modalidade, $dataAula, $horario) {
        return $this->dao->contarAlunosPorAula($modalidade, $dataAula, $horario);
    }

    // ------------------ LISTAR TURMAS ------------------
    public function listarTurmas($dataInicio = null, $dataFim = null) {
        return $this->dao->listarTurmas($dataInicio, $dataFim);
    }

    // ------------------ LISTAR TODOS OS AGENDAMENTOS (para funcionários) ------------------
    public function listarTodos() {
        return $this->dao->listarTodos();
    }

    // ------------------ CANCELAR AGENDAMENTO ------------------
    public function cancelar($id, $alunoId) {
        // Buscar informações do agendamento antes de cancelar
        $agendamento = $this->dao->buscarPorId($id);
        if (!$agendamento || $agendamento['aluno_id'] != $alunoId) {
            return false;
        }

        $resultado = $this->dao->cancelarAgendamento($id, $alunoId);
        
        // Se cancelou com sucesso, verificar se há alguém na lista de espera
        if ($resultado) {
            $primeiroNaFila = $this->listaEsperaController->buscarPrimeiroNaFila(
                $agendamento['modalidade'],
                $agendamento['data_aula'],
                $agendamento['horario']
            );
            
            // Se houver alguém na lista de espera, adicionar automaticamente
            if ($primeiroNaFila) {
                // Buscar professor do agendamento cancelado (ou buscar um disponível)
                $professores = $this->dao->listarProfessoresPorModalidade($agendamento['modalidade']);
                $professorId = !empty($professores) ? $professores[0]['id'] : $agendamento['professor_id'];
                
                // Criar agendamento para o primeiro da lista de espera
                $novoAgendamento = $this->criar(
                    $primeiroNaFila['aluno_id'],
                    $professorId,
                    $agendamento['modalidade'],
                    $agendamento['data_aula'],
                    $agendamento['horario']
                );
                
                // Se conseguiu agendar, remover da lista de espera
                if ($novoAgendamento === true) {
                    $this->listaEsperaController->remover($primeiroNaFila['id'], $primeiroNaFila['aluno_id']);
                }
            }
        }
        
        return $resultado;
    }

    // ------------------ VERIFICAR SE É FERIADO ------------------
    private function ehFeriado(DateTime $data) {
        $ano = (int)$data->format('Y');
        $mes = (int)$data->format('m');
        $dia = (int)$data->format('d');

        // Feriados fixos
        $feriadosFixos = [
            '01-01' => 'Ano Novo',
            '04-21' => 'Tiradentes',
            '05-01' => 'Dia do Trabalhador',
            '09-07' => 'Independência do Brasil',
            '10-12' => 'Nossa Senhora Aparecida',
            '11-02' => 'Finados',
            '11-15' => 'Proclamação da República',
            '12-25' => 'Natal'
        ];

        $dataFormatada = sprintf('%02d-%02d', $mes, $dia);
        if (isset($feriadosFixos[$dataFormatada])) {
            return true;
        }

        // Feriados móveis (Páscoa e derivados)
        $pascoa = $this->calcularPascoa($ano);
        $carnaval = clone $pascoa;
        $carnaval->modify('-47 days');
        $sextaSanta = clone $pascoa;
        $sextaSanta->modify('-2 days');
        $corpusChristi = clone $pascoa;
        $corpusChristi->modify('+60 days');

        $feriadosMoveis = [
            $carnaval->format('Y-m-d'),
            $sextaSanta->format('Y-m-d'),
            $pascoa->format('Y-m-d'),
            $corpusChristi->format('Y-m-d')
        ];

        $dataComparar = $data->format('Y-m-d');
        return in_array($dataComparar, $feriadosMoveis);
    }

    // ------------------ CALCULAR DATA DA PÁSCOA ------------------
    private function calcularPascoa($ano) {
        // Algoritmo de Meeus/Jones/Butcher
        $a = $ano % 19;
        $b = floor($ano / 100);
        $c = $ano % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = floor(($a + 11 * $h + 22 * $l) / 451);
        $mes = floor(($h + $l - 7 * $m + 114) / 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return new DateTime("$ano-$mes-$dia");
    }
}
?>
