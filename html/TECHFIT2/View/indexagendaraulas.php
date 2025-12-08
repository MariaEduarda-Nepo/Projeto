<?php
session_start();

// Se NÃO estiver logado → volta pro login
// if (!isset($_SESSION['id'])) {
//     header("Location: indexlogin.php");
//     exit;
// }

// Apenas alunos, professores e funcionários podem acessar
if ($_SESSION['tipo'] !== 'Aluno' && $_SESSION['tipo'] !== 'Professor' && $_SESSION['tipo'] !== 'Funcionario') {
    header("Location: /");
    exit;
}

require_once __DIR__ . '/../Controller/AgendamentoController.php';
require_once __DIR__ . '/../Controller/ListaEsperaController.php';

$controller = new AgendamentoController();
$listaEsperaController = new ListaEsperaController();
$mensagem = "";
$sucesso = false;
$isAluno = ($_SESSION['tipo'] === 'Aluno');
$isProfessor = ($_SESSION['tipo'] === 'Professor');
$isFuncionario = ($_SESSION['tipo'] === 'Funcionario');

// Processar formulário (apenas alunos podem agendar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAluno) {
    if (isset($_POST['acao']) && $_POST['acao'] === 'agendar') {
        // Buscar um professor disponível para a modalidade
        $professoresModalidade = $controller->listarProfessoresPorModalidade($_POST['modalidade']);
        
        if (empty($professoresModalidade)) {
            $mensagem = "Não há professores disponíveis para a modalidade " . htmlspecialchars($_POST['modalidade']) . ". Entre em contato com a academia.";
        } else {
            // Pega o primeiro professor disponível (pode implementar lógica de distribuição mais tarde)
            $professorId = $professoresModalidade[0]['id'];
            
            // Verificar se o professor existe
            if (!$professorId || $professorId <= 0) {
                $mensagem = "Erro ao encontrar professor. Tente novamente ou entre em contato com a academia.";
            } else {
                $resultado = $controller->criar(
                    $_SESSION['id'],
                    $professorId,
                    $_POST['modalidade'],
                    $_POST['data_aula'],
                    $_POST['horario']
                );
                
                if ($resultado === true) {
                    $sucesso = true;
                    $mensagem = "Aula agendada com sucesso!";
                } else {
                    // Verificar se foi adicionado à lista de espera
                    if (strpos($resultado, "lista de espera") !== false || strpos($resultado, "completa") !== false) {
                        // Tentar adicionar à lista de espera
                        $resultadoListaEspera = $listaEsperaController->criar(
                            $_SESSION['id'],
                            $_POST['modalidade'],
                            $_POST['data_aula'],
                            $_POST['horario']
                        );
                        if ($resultadoListaEspera === true) {
                            $sucesso = true;
                            $mensagem = "Turma completa! Você foi adicionado à lista de espera. Você será notificado quando houver uma vaga disponível.";
                        } else {
                            $mensagem = $resultadoListaEspera;
                        }
                    } else {
                        $mensagem = $resultado;
                    }
                }
            }
        }
    }

    if (isset($_POST['acao']) && $_POST['acao'] === 'cancelar') {
        $controller->cancelar($_POST['agendamento_id'], $_SESSION['id']);
        $sucesso = true;
        $mensagem = "Agendamento cancelado com sucesso!";
    }

    if (isset($_POST['acao']) && $_POST['acao'] === 'remover_lista_espera') {
        $resultado = $listaEsperaController->remover($_POST['lista_espera_id'], $_SESSION['id']);
        if ($resultado) {
            $sucesso = true;
            $mensagem = "Removido da lista de espera com sucesso!";
        } else {
            $mensagem = "Erro ao remover da lista de espera!";
        }
    }
}

// Buscar dados conforme o tipo de usuário
if ($isAluno) {
    $agendamentos = $controller->listarPorAluno($_SESSION['id']);
    $listaEspera = $listaEsperaController->listarPorAluno($_SESSION['id']);
} elseif ($isProfessor) {
    // Professor vê apenas suas aulas
    $agendamentos = $controller->listarPorProfessor($_SESSION['id']);
    $listaEspera = [];
} elseif ($isFuncionario) {
    // Funcionário vê todos os agendamentos
    $agendamentos = $controller->listarTodos();
    $listaEspera = [];
}

// Gerar próximos 30 dias
$diasDisponiveis = [];
$hoje = new DateTime();
for ($i = 0; $i < 30; $i++) {
    $data = clone $hoje;
    $data->modify("+$i days");
    $diasDisponiveis[] = $data;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Agendar Aulas</title>
    <link rel="stylesheet" href="View/header-footer.css">
    <link rel="stylesheet" href="View/agendaraulas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <!-- CABEÇALHO PADRONIZADO -->
    <?php include 'include/header.php'; ?>

<main class="agenda-container">
    <h1><i class="fas fa-calendar-alt"></i> <?php 
        if ($isFuncionario) {
            echo 'Gerenciar Agendamentos';
        } elseif ($isProfessor) {
            echo 'Minhas Aulas';
        } else {
            echo 'Agendar Aulas';
        }
    ?></h1>

    <?php if (!empty($mensagem)): ?>
        <div class="mensagem <?php echo $sucesso ? 'sucesso' : 'erro'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <!-- FORMULÁRIO DE AGENDAMENTO (APENAS PARA ALUNOS) -->
    <?php if ($isAluno): ?>
    <section class="agendar-section">
        <h2><i class="fas fa-plus-circle"></i> Nova Aula em Grupo</h2>
        <form method="POST" class="form-agendamento">
            <input type="hidden" name="acao" value="agendar">

            <div class="form-group">
                <label class="field-label">Modalidade:</label>
                <select name="modalidade" id="modalidade" required>
                    <option value="">Selecione a modalidade</option>
                    <option value="Boxe">🥊 Boxe</option>
                    <option value="Muay Thai">🥋 Muay Thai</option>
                </select>
            </div>

            <div class="form-group">
                <label class="field-label">Data da Aula:</label>
                <input type="date" name="data_aula" id="data_aula" min="<?php echo date('Y-m-d'); ?>" required>
                <small class="data-info">Apenas dias úteis (segunda a sexta, exceto feriados)</small>
            </div>

            <div class="form-group">
                <label class="field-label">Horário da Turma:</label>
                <select name="horario" required>
                    <option value="">Selecione o horário</option>
                    <option value="07:00-08:00">07:00 às 08:00</option>
                    <option value="09:00-10:00">09:00 às 10:00</option>
                    <option value="11:00-12:00">11:00 às 12:00</option>
                    <option value="13:00-14:00">13:00 às 14:00</option>
                    <option value="14:00-16:00">14:00 às 16:00</option>
                    <option value="16:00-18:00">16:00 às 18:00</option>
                    <option value="18:00-20:00">18:00 às 20:00</option>
                </select>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p>As aulas são em grupo (máximo de 20 alunos por turma). Cada horário é uma turma separada. Você será adicionado à turma da modalidade e horário selecionados.</p>
            </div>

            <button type="submit" class="btn-agendar">
                <i class="fas fa-check"></i> Agendar Aula
            </button>
        </form>
    </section>
    <?php endif; ?>

    <!-- MEUS AGENDAMENTOS -->
    <section class="agendamentos-section">
        <h2><i class="fas fa-list"></i> <?php 
            if ($isFuncionario) {
                echo 'Todos os Agendamentos';
            } elseif ($isProfessor) {
                echo 'Minhas Aulas Agendadas';
            } else {
                echo 'Meus Agendamentos';
            }
        ?></h2>
        
        <?php if (empty($agendamentos)): ?>
            <div class="sem-agendamentos">
                <i class="fas fa-calendar-times"></i>
                <p><?php 
                    if ($isFuncionario) {
                        echo 'Não há agendamentos cadastrados.';
                    } elseif ($isProfessor) {
                        echo 'Você ainda não tem aulas agendadas com alunos.';
                    } else {
                        echo 'Você ainda não tem aulas agendadas.';
                    }
                ?></p>
            </div>
        <?php else: ?>
            <div class="agendamentos-grid">
                <?php foreach ($agendamentos as $agendamento): 
                    $dataAula = new DateTime($agendamento['data_aula']);
                    $hoje = new DateTime();
                    $podeCancelar = $isAluno && $dataAula >= $hoje && $agendamento['status'] === 'Agendada';
                ?>
                    <div class="agendamento-card status-<?php echo strtolower($agendamento['status']); ?>">
                        <div class="card-header">
                            <h3><?php 
                                if ($isFuncionario) {
                                    echo htmlspecialchars($agendamento['aluno_nome']) . ' - ' . htmlspecialchars($agendamento['professor_nome']);
                                } elseif ($isProfessor) {
                                    echo htmlspecialchars($agendamento['aluno_nome']);
                                } else {
                                    echo htmlspecialchars($agendamento['professor_nome']);
                                }
                            ?></h3>
                            <span class="status-badge"><?php echo htmlspecialchars($agendamento['status']); ?></span>
                        </div>
                        
                        <div class="card-body">
                            <p><i class="fas fa-fist-raised"></i> 
                                <strong>Modalidade:</strong> 
                                <span class="modalidade-badge modalidade-<?php echo strtolower(str_replace(' ', '', $agendamento['modalidade'])); ?>">
                                    <?php echo htmlspecialchars($agendamento['modalidade']); ?>
                                </span>
                            </p>
                            <p><i class="fas fa-calendar"></i> 
                                <strong>Data:</strong> <?php echo $dataAula->format('d/m/Y'); ?>
                            </p>
                            <p><i class="fas fa-clock"></i> 
                                <strong>Horário:</strong> 
                                <?php 
                                // Formatar horário (07:00-08:00 ou apenas 07:00)
                                if (strpos($agendamento['horario'], '-') !== false) {
                                    echo htmlspecialchars($agendamento['horario']);
                                } else {
                                    echo date('H:i', strtotime($agendamento['horario']));
                                }
                                ?>
                            </p>
                            <?php if ($isFuncionario): ?>
                                <p><i class="fas fa-user"></i> 
                                    <strong>Aluno:</strong> <?php echo htmlspecialchars($agendamento['aluno_nome']); ?>
                                </p>
                                <p><i class="fas fa-user-tie"></i> 
                                    <strong>Professor:</strong> <?php echo htmlspecialchars($agendamento['professor_nome']); ?>
                                </p>
                            <?php elseif ($isProfessor): ?>
                                <p><i class="fas fa-user"></i> 
                                    <strong>Aluno:</strong> <?php echo htmlspecialchars($agendamento['aluno_nome']); ?>
                                </p>
                            <?php else: ?>
                                <p><i class="fas fa-user-tie"></i> 
                                    <strong>Professor:</strong> <?php echo htmlspecialchars($agendamento['professor_nome']); ?>
                                </p>
                            <?php endif; ?>
                            <?php 
                            // Usar total_alunos que já vem na query (otimizado)
                            $totalAlunos = isset($agendamento['total_alunos']) ? (int)$agendamento['total_alunos'] : $controller->contarAlunosPorAula(
                                $agendamento['modalidade'],
                                $agendamento['data_aula'],
                                $agendamento['horario']
                            );
                            $turmaCheia = $totalAlunos >= 20;
                            ?>
                            <p><i class="fas fa-users"></i> 
                                <strong>Alunos na turma:</strong> 
                                <span class="<?php echo $turmaCheia ? 'turma-cheia' : ''; ?>">
                                    <?php echo $totalAlunos; ?>/20
                                </span>
                                <?php if ($turmaCheia): ?>
                                    <span class="badge-cheia">TURMA CHEIA</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if ($podeCancelar): ?>
                            <div class="card-footer">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="acao" value="cancelar">
                                    <input type="hidden" name="agendamento_id" value="<?php echo $agendamento['id']; ?>">
                                    <button type="submit" class="btn-cancelar" onclick="return confirm('Deseja realmente cancelar esta aula?');">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- LISTA DE ESPERA (APENAS PARA ALUNOS) -->
    <?php if ($isAluno && !empty($listaEspera)): ?>
    <section class="lista-espera-section">
        <h2><i class="fas fa-hourglass-half"></i> Minha Lista de Espera</h2>
        <div class="agendamentos-grid">
            <?php foreach ($listaEspera as $item): 
                $dataAula = new DateTime($item['data_aula']);
            ?>
                <div class="agendamento-card status-aguardando">
                    <div class="card-header">
                        <h3>Lista de Espera</h3>
                        <span class="status-badge"><?php echo htmlspecialchars($item['status']); ?></span>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-fist-raised"></i> 
                            <strong>Modalidade:</strong> 
                            <span class="modalidade-badge modalidade-<?php echo strtolower(str_replace(' ', '', $item['modalidade'])); ?>">
                                <?php echo htmlspecialchars($item['modalidade']); ?>
                            </span>
                        </p>
                        <p><i class="fas fa-calendar"></i> 
                            <strong>Data:</strong> <?php echo $dataAula->format('d/m/Y'); ?>
                        </p>
                        <p><i class="fas fa-clock"></i> 
                            <strong>Horário:</strong> <?php echo htmlspecialchars($item['horario']); ?>
                        </p>
                        <p><i class="fas fa-info-circle"></i> 
                            <small>Você será notificado quando houver uma vaga disponível.</small>
                        </p>
                    </div>
                    <div class="card-footer">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="acao" value="remover_lista_espera">
                            <input type="hidden" name="lista_espera_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-cancelar" onclick="return confirm('Deseja realmente sair da lista de espera?');">
                                <i class="fas fa-times"></i> Remover da Lista
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

    <!-- RODAPÉ PADRONIZADO -->
    <?php include 'include/footer.php'; ?>

<script>
// Bloquear sábados e domingos no date picker
document.addEventListener('DOMContentLoaded', function() {
    const dataInput = document.getElementById('data_aula');
    
    if (dataInput) {
        dataInput.addEventListener('input', function() {
            const selectedDate = new Date(this.value);
            const dayOfWeek = selectedDate.getDay(); // 0 = Domingo, 6 = Sábado
            
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                alert('Não é possível agendar aulas aos sábados e domingos! Selecione um dia útil.');
                this.value = '';
            }
        });

        // Adicionar atributo para desabilitar fins de semana (funciona em alguns navegadores)
        dataInput.addEventListener('focus', function() {
            this.setAttribute('data-weekend-disabled', 'true');
        });
    }
});
</script>

</body>
</html>