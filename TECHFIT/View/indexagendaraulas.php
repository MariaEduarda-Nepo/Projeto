<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';

// Se NÃO estiver logado → volta pro login
if (!isset($_SESSION['id'])) {
    redirect('login');
}

// Apenas alunos, professores e funcionários podem acessar
if ($_SESSION['tipo'] !== 'Aluno' && $_SESSION['tipo'] !== 'Professor' && $_SESSION['tipo'] !== 'Funcionario') {
    redirect('home');
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

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Apenas alunos podem agendar
    if (isset($_POST['acao']) && $_POST['acao'] === 'agendar' && $isAluno) {
        // Verificar se o professor foi selecionado
        if (empty($_POST['professor_id'])) {
            $mensagem = "Selecione um professor!";
        } else {
            $professorId = intval($_POST['professor_id']);
            
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

    // Alunos e professores podem cancelar
    if (isset($_POST['acao']) && $_POST['acao'] === 'cancelar') {
        if (!isset($_POST['agendamento_id']) || empty($_POST['agendamento_id'])) {
            $mensagem = "ID do agendamento não informado!";
        } else {
            $agendamentoId = intval($_POST['agendamento_id']);
            
            if ($isAluno) {
                $resultado = $controller->cancelar($agendamentoId, $_SESSION['id']);
            } elseif ($isProfessor) {
                $resultado = $controller->cancelarPorProfessor($agendamentoId, $_SESSION['id']);
            } else {
                $resultado = false;
            }
            
            if ($resultado) {
                $sucesso = true;
                if ($isAluno) {
                    $mensagem = "Você saiu da aula com sucesso!";
                } else {
                    $mensagem = "Aula cancelada com sucesso!";
                }
                // Redirecionar para evitar reenvio do formulário
                header("Location: " . url('agendar') . "?sucesso=1&msg=" . urlencode($mensagem));
                exit;
            } else {
                if ($isAluno) {
                    $mensagem = "Erro ao sair da aula. Verifique se você tem permissão para cancelar sua participação.";
                } else {
                    $mensagem = "Erro ao cancelar a aula. Verifique se você tem permissão para cancelar esta aula.";
                }
            }
        }
    }

    // Apenas alunos podem remover da lista de espera
    if (isset($_POST['acao']) && $_POST['acao'] === 'remover_lista_espera' && $isAluno) {
        $resultado = $listaEsperaController->remover($_POST['lista_espera_id'], $_SESSION['id']);
        if ($resultado) {
            $sucesso = true;
            $mensagem = "Removido da lista de espera com sucesso!";
        } else {
            $mensagem = "Erro ao remover da lista de espera!";
        }
    }
}

// Endpoint AJAX para buscar professores por modalidade
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'buscar_professores' && isset($_GET['modalidade'])) {
    header('Content-Type: application/json');
    $professores = $controller->listarProfessoresPorModalidade($_GET['modalidade']);
    echo json_encode([
        'success' => true,
        'professores' => $professores
    ]);
    exit;
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
    <link rel="stylesheet" href="<?php echo asset('header-footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('agendaraulas.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

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
    
    <!-- Container para mensagens JavaScript -->
    <div id="mensagem-js" style="display: none;"></div>

    <!-- FORMULÁRIO DE AGENDAMENTO (APENAS PARA ALUNOS) -->
    <?php if ($isAluno): ?>
    <section class="agendar-section">
        <h2><i class="fas fa-plus-circle"></i> Nova Aula em Grupo</h2>
        <form method="POST" class="form-agendamento">
            <input type="hidden" name="acao" value="agendar">

            <div class="form-group">
                <label class="field-label">Modalidade:</label>
                <select name="modalidade" id="modalidade" required onchange="carregarProfessores()">
                    <option value="">Selecione a modalidade</option>
                    <option value="Boxe">🥊 Boxe</option>
                    <option value="Muay Thai">🥋 Muay Thai</option>
                </select>
            </div>

            <div class="form-group" id="professor-group">
                <label class="field-label">Professor:</label>
                <select name="professor_id" id="professor_id" required disabled>
                    <option value="">Selecione a modalidade primeiro</option>
                </select>
            </div>

            <div class="form-group">
                <label class="field-label">Data da Aula:</label>
                <input type="date" name="data_aula" id="data_aula" min="<?php echo date('Y-m-d'); ?>" required>
                <small class="data-info">Apenas dias úteis (segunda a sexta, exceto feriados)</small>
            </div>

            <div class="form-group">
                <label class="field-label">Horário da Turma:</label>
                <select name="horario" id="horario" required>
                    <option value="">Selecione o horário</option>
                    <option value="07:00-08:00">07:00 às 08:00</option>
                    <option value="09:00-10:00">09:00 às 10:00</option>
                    <option value="11:00-12:00">11:00 às 12:00</option>
                    <option value="14:00-15:00">14:00 às 15:00</option>
                    <option value="16:00-17:00">16:00 às 17:00</option>
                    <option value="18:00-19:00">18:00 às 19:00</option>
                    <option value="20:00-21:00">20:00 às 21:00</option>
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
                    
                    // Verificar se a aula já passou
                    $aulaPassou = false;
                    if (strpos($agendamento['horario'], '-') !== false) {
                        // Formato: 07:00-08:00
                        $horarioFim = substr($agendamento['horario'], strpos($agendamento['horario'], '-') + 1);
                        $dataHoraFim = new DateTime($agendamento['data_aula'] . ' ' . $horarioFim);
                        $aulaPassou = $dataHoraFim < $hoje;
                    } else {
                        // Formato: apenas horário inicial, assumir 1 hora de duração
                        $dataHoraInicio = new DateTime($agendamento['data_aula'] . ' ' . $agendamento['horario']);
                        $dataHoraFim = clone $dataHoraInicio;
                        $dataHoraFim->modify('+1 hour');
                        $aulaPassou = $dataHoraFim < $hoje;
                    }
                    
                    $aulaCancelada = ($agendamento['status'] === 'Cancelada');
                    $podeFechar = $aulaPassou || $aulaCancelada;
                    
                    // Alunos podem cancelar se a aula ainda não passou
                    // Professores podem cancelar sempre que a aula estiver agendada
                    $podeCancelar = false;
                    if ($isAluno && $dataAula >= $hoje && $agendamento['status'] === 'Agendada') {
                        $podeCancelar = true;
                    } elseif ($isProfessor && $agendamento['status'] === 'Agendada') {
                        $podeCancelar = true;
                    }
                ?>
                    <div class="agendamento-card status-<?php echo strtolower($agendamento['status']); ?>" data-card-id="<?php echo $agendamento['id']; ?>">
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
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="status-badge"><?php echo htmlspecialchars($agendamento['status']); ?></span>
                                <?php if ($podeFechar): ?>
                                    <button type="button" class="btn-fechar-card" onclick="fecharCard(<?php echo $agendamento['id']; ?>)" title="Fechar card">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
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
                                    <button type="submit" class="btn-cancelar" onclick="return confirm('<?php echo $isAluno ? 'Deseja realmente sair desta aula? Você será removido da turma.' : 'Deseja realmente cancelar esta aula?'; ?>');">
                                        <i class="fas fa-times"></i> <?php echo $isAluno ? 'Sair da Aula' : 'Cancelar Aula'; ?>
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

<?php include __DIR__ . '/footer.php'; ?>

<script>
// Validação de sábados e domingos apenas no submit do formulário
document.addEventListener('DOMContentLoaded', function() {
    const formAgendamento = document.querySelector('.form-agendamento');
    const dataInput = document.getElementById('data_aula');
    
    if (formAgendamento && dataInput) {
        formAgendamento.addEventListener('submit', function(e) {
            const selectedDate = new Date(dataInput.value);
            
            // Verificar se a data é válida
            if (dataInput.value && !isNaN(selectedDate.getTime())) {
                const dayOfWeek = selectedDate.getDay(); // 0 = Domingo, 6 = Sábado
                
                if (dayOfWeek === 0 || dayOfWeek === 6) {
                    e.preventDefault();
                    exibirMensagem('Não é possível agendar aulas aos sábados e domingos! Selecione um dia útil.', 'erro');
                    dataInput.focus();
                    return false;
                }
            }
        });
    }
});

// Função para exibir mensagens (substitui alert)
function exibirMensagem(texto, tipo = 'erro') {
    const container = document.getElementById('mensagem-js');
    if (!container) return;
    
    container.className = 'mensagem ' + tipo;
    container.textContent = texto;
    container.style.display = 'block';
    container.style.opacity = '1';
    
    // Scroll para a mensagem
    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Remover após 5 segundos
    setTimeout(function() {
        container.style.opacity = '0';
        setTimeout(function() {
            container.style.display = 'none';
        }, 300);
    }, 5000);
}

// Função para carregar professores baseado na modalidade selecionada
function carregarProfessores() {
    const modalidadeSelect = document.getElementById('modalidade');
    const professorSelect = document.getElementById('professor_id');
    
    const modalidade = modalidadeSelect.value;
    
    // Limpar o select de professores
    professorSelect.innerHTML = '<option value="">Selecione o professor</option>';
    
    if (!modalidade) {
        professorSelect.innerHTML = '<option value="">Selecione a modalidade primeiro</option>';
        professorSelect.required = false;
        professorSelect.disabled = true;
        return;
    }
    
    // Habilitar o campo de professor
    professorSelect.required = true;
    professorSelect.disabled = false;
    
    // Fazer requisição AJAX para buscar professores
    fetch('<?php echo url('agendar'); ?>?acao=buscar_professores&modalidade=' + encodeURIComponent(modalidade))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.professores && data.professores.length > 0) {
                data.professores.forEach(professor => {
                    const option = document.createElement('option');
                    option.value = professor.id;
                    option.textContent = professor.nome;
                    professorSelect.appendChild(option);
                });
            } else {
                professorSelect.innerHTML = '<option value="">Nenhum professor disponível para esta modalidade</option>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar professores:', error);
            professorSelect.innerHTML = '<option value="">Erro ao carregar professores</option>';
        });
}

// Função para fechar card de aula passada ou cancelada
function fecharCard(cardId) {
    const card = document.querySelector(`[data-card-id="${cardId}"]`);
    if (card) {
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        setTimeout(function() {
            card.style.display = 'none';
        }, 300);
    }
}
</script>

</body>
</html>