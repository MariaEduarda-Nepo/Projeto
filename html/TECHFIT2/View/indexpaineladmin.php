<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: indexlogin.php");
    exit;
}

// Apenas funcionários podem acessar
if ($_SESSION['tipo'] !== 'Funcionario') {
    header("Location: indexpaginainicial.php");
    exit;
}

require_once __DIR__ . '/../Controller/CadastroController.php';
require_once __DIR__ . '/../Controller/AgendamentoController.php';
require_once __DIR__ . '/../Controller/AvaliacaoFisicaController.php';
require_once __DIR__ . '/../Controller/FrequenciaController.php';
require_once __DIR__ . '/../Controller/MensagemController.php';
require_once __DIR__ . '/../Controller/ListaEsperaController.php';
require_once __DIR__ . '/../Model/CadastroDAO.php';

$cadastroController = new CadastroController();
$agendamentoController = new AgendamentoController();
$avaliacaoController = new AvaliacaoFisicaController();
$frequenciaController = new FrequenciaController();
$mensagemController = new MensagemController();
$listaEsperaController = new ListaEsperaController();
$cadastroDAO = new CadastroDAO();

$mensagem = "";
$sucesso = false;

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'enviar_mensagem':
                if (isset($_POST['destinatario_id']) && $_POST['destinatario_id'] !== 'todos') {
                    $resultado = $mensagemController->criar(
                        $_SESSION['id'],
                        $_POST['destinatario_id'],
                        $_POST['assunto'],
                        $_POST['mensagem'],
                        $_POST['tipo_destinatario']
                    );
                } else {
                    $resultado = $mensagemController->enviarParaTodos(
                        $_SESSION['id'],
                        $_POST['tipo_destinatario'],
                        $_POST['assunto'],
                        $_POST['mensagem']
                    );
                }
                if ($resultado === true) {
                    $sucesso = true;
                    $mensagem = "Mensagem enviada com sucesso!";
                } else {
                    $mensagem = $resultado;
                }
                break;
        }
    }
}

// Buscar estatísticas
$todosCadastros = $cadastroDAO->listarTodos();
$totalAlunos = count(array_filter($todosCadastros, function($c) { return $c['tipo'] === 'Aluno'; }));
$totalProfessores = count(array_filter($todosCadastros, function($c) { return $c['tipo'] === 'Professor'; }));
$totalFuncionarios = count(array_filter($todosCadastros, function($c) { return $c['tipo'] === 'Funcionario'; }));

$turmas = $agendamentoController->listarTurmas();
$totalTurmas = count($turmas);
$totalAlunosTurmas = array_sum(array_column($turmas, 'total_alunos'));

$alunosComAvaliacaoVencida = $avaliacaoController->listarAlunosComProximaAvaliacaoVencida();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Painel Administrativo</title>
    <link rel="stylesheet" href="View/header-footer.css">
    <link rel="stylesheet" href="View/agendaraulas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .admin-card {
            background-color: rgba(26, 26, 26, 0.95);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #a83bd3;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .admin-card:hover {
            transform: translateY(-5px);
        }
        .admin-card h3 {
            color: #a83bd3;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-card p {
            color: #cccccc;
            margin-top: 10px;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #a83bd3;
            margin: 10px 0;
        }
        .admin-section {
            background-color: rgba(26, 26, 26, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            margin-bottom: 30px;
        }
        .admin-section h2 {
            color: #a83bd3;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .action-btn {
            background: linear-gradient(90deg, #a83bd3, #7a2a9e);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        .action-btn:hover {
            background: linear-gradient(90deg, #7a2a9e, #a83bd3);
            transform: translateY(-2px);
        }
        .alert-box {
            background: rgba(255, 152, 0, 0.2);
            border-left: 4px solid #ff9800;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-box h4 {
            color: #ff9800;
            margin-bottom: 10px;
        }
        .aluno-list {
            list-style: none;
            padding: 0;
        }
        .aluno-list li {
            padding: 15px;
            background-color: rgba(26, 26, 26, 0.95);
            margin-bottom: 10px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 3px solid #ff9800;
        }
        .aluno-list li strong {
            color: #ffffff;
        }
        .aluno-list li small {
            color: #aaaaaa;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
        }
        .modal.mostrar {
            display: flex;
        }
        .modal-content {
            background-color: rgba(26, 26, 26, 0.98);
            padding: 30px;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
            border: 2px solid rgba(168, 59, 211, 0.3);
        }
        .modal-content h2 {
            color: #a83bd3;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-content .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }
        .modal-content .form-group label {
            color: #cccccc;
            font-weight: 500;
            font-size: 14px;
        }
        .modal-content .form-group input[type="text"],
        .modal-content .form-group select,
        .modal-content .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(168, 59, 211, 0.3);
            border-radius: 10px;
            color: #ffffff;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            transition: all 0.3s ease;
        }
        .modal-content .form-group input[type="text"]:focus,
        .modal-content .form-group select:focus,
        .modal-content .form-group textarea:focus {
            outline: none;
            border-color: #a83bd3;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 10px rgba(168, 59, 211, 0.3);
        }
        .modal-content .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23a83bd3' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }
        .modal-content .form-group select option {
            background: #1a1a1a;
            color: #ffffff;
            padding: 10px;
        }
        .modal-content .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .modal-content .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .modal-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .modal-content table th,
        .modal-content table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #cccccc;
        }
        .modal-content table th {
            background: rgba(168, 59, 211, 0.3);
            color: #a83bd3;
            font-weight: bold;
        }
        .modal-content table tbody tr:hover {
            background: rgba(168, 59, 211, 0.1);
        }
        .close-modal {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #a83bd3;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .close-modal:hover {
            color: #7a2a9e;
        }
    </style>
</head>
<body>

<?php include 'include/header.php'; ?>

<main class="admin-container">
    <h1><i class="fas fa-tachometer-alt"></i> Painel Administrativo</h1>

    <?php if (!empty($mensagem)): ?>
        <div class="mensagem <?php echo $sucesso ? 'sucesso' : 'erro'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <!-- ESTATÍSTICAS GERAIS -->
    <section class="admin-section">
        <h2><i class="fas fa-chart-line"></i> Estatísticas Gerais</h2>
        <div class="admin-grid">
            <div class="admin-card">
                <h3><i class="fas fa-users"></i> Alunos</h3>
                <div class="stat-number"><?php echo $totalAlunos; ?></div>
                <p>Total de alunos cadastrados</p>
            </div>
            <div class="admin-card">
                <h3><i class="fas fa-chalkboard-teacher"></i> Professores</h3>
                <div class="stat-number"><?php echo $totalProfessores; ?></div>
                <p>Total de professores cadastrados</p>
            </div>
            <div class="admin-card">
                <h3><i class="fas fa-calendar-alt"></i> Turmas</h3>
                <div class="stat-number"><?php echo $totalTurmas; ?></div>
                <p>Turmas ativas</p>
            </div>
            <div class="admin-card">
                <h3><i class="fas fa-user-check"></i> Alunos em Turmas</h3>
                <div class="stat-number"><?php echo $totalAlunosTurmas; ?></div>
                <p>Total de alunos agendados</p>
            </div>
        </div>
    </section>

    <!-- ALERTAS -->
    <?php if (!empty($alunosComAvaliacaoVencida)): ?>
    <section class="admin-section">
        <div class="alert-box">
            <h4><i class="fas fa-exclamation-triangle"></i> Alunos com Avaliação Física Vencida</h4>
            <ul class="aluno-list">
                <?php foreach ($alunosComAvaliacaoVencida as $aluno): ?>
                    <li>
                        <div>
                            <strong><?php echo htmlspecialchars($aluno['nome']); ?></strong><br>
                            <small><?php echo htmlspecialchars($aluno['email']); ?></small>
                        </div>
                        <div>
                            Próxima avaliação: <?php echo date('d/m/Y', strtotime($aluno['proxima_avaliacao'])); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>

    <!-- AÇÕES RÁPIDAS -->
    <section class="admin-section">
        <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
        <div class="quick-actions">
            <a href="indexavaliacaofisica.php" class="action-btn">
                <i class="fas fa-clipboard-check"></i> Registrar Avaliação Física
            </a>
            <a href="indexrelatorios.php" class="action-btn">
                <i class="fas fa-chart-bar"></i> Relatórios de Ocupação
            </a>
            <button onclick="abrirModal('modalMensagem')" class="action-btn">
                <i class="fas fa-envelope"></i> Enviar Mensagem
            </button>
            <a href="indexagendaraulas.php" class="action-btn">
                <i class="fas fa-calendar-check"></i> Gerenciar Agendamentos
            </a>
        </div>
    </section>

    <!-- GERENCIAR CADASTROS -->
    <section class="admin-section">
        <h2><i class="fas fa-user-cog"></i> Gerenciar Cadastros</h2>
        <div class="quick-actions">
            <a href="../View/indexCadastro.php" class="action-btn">
                <i class="fas fa-user-plus"></i> Cadastrar Novo Usuário
            </a>
            <button onclick="abrirModal('modalListaUsuarios')" class="action-btn">
                <i class="fas fa-list"></i> Listar Usuários
            </button>
        </div>
    </section>
</main>

<!-- MODAL DE MENSAGEM -->
<div id="modalMensagem" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="fecharModal('modalMensagem')">&times;</span>
        <h2><i class="fas fa-envelope"></i> Enviar Mensagem</h2>
        <form method="POST">
            <input type="hidden" name="acao" value="enviar_mensagem">
            <div class="form-group">
                <label class="field-label">Tipo de Destinatário:</label>
                <select name="tipo_destinatario" id="tipo_destinatario" required onchange="atualizarDestinatarios()">
                    <option value="Aluno">Aluno</option>
                    <option value="Professor">Professor</option>
                    <option value="Funcionario">Funcionário</option>
                </select>
            </div>
            <div class="form-group">
                <label class="field-label">Destinatário:</label>
                <select name="destinatario_id" id="destinatario_id" required>
                    <option value="todos">Todos</option>
                    <?php 
                    $alunos = array_filter($todosCadastros, function($c) { return $c['tipo'] === 'Aluno'; });
                    foreach ($alunos as $aluno): 
                    ?>
                        <option value="<?php echo $aluno['id']; ?>" data-tipo="Aluno">
                            <?php echo htmlspecialchars($aluno['nome']); ?> - <?php echo htmlspecialchars($aluno['email']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="field-label">Assunto:</label>
                <input type="text" name="assunto" required placeholder="Assunto da mensagem">
            </div>
            <div class="form-group">
                <label class="field-label">Mensagem:</label>
                <textarea name="mensagem" rows="6" required placeholder="Digite sua mensagem..."></textarea>
            </div>
            <button type="submit" class="btn-agendar">
                <i class="fas fa-paper-plane"></i> Enviar Mensagem
            </button>
        </form>
    </div>
</div>

<!-- MODAL DE LISTA DE USUÁRIOS -->
<div id="modalListaUsuarios" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="fecharModal('modalListaUsuarios')">&times;</span>
        <h2><i class="fas fa-list"></i> Lista de Usuários</h2>
        <div style="max-height: 400px; overflow-y: auto;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todosCadastros as $cadastro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cadastro['nome']); ?></td>
                            <td><?php echo htmlspecialchars($cadastro['email']); ?></td>
                            <td><?php echo htmlspecialchars($cadastro['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($cadastro['cpf'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($cadastro['telefone'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>

<script>
function abrirModal(id) {
    document.getElementById(id).classList.add('mostrar');
}

function fecharModal(id) {
    document.getElementById(id).classList.remove('mostrar');
}

function atualizarDestinatarios() {
    const tipo = document.getElementById('tipo_destinatario').value;
    const select = document.getElementById('destinatario_id');
    const options = select.querySelectorAll('option[data-tipo]');
    
    // Mostrar apenas opções do tipo selecionado
    options.forEach(opt => {
        if (opt.getAttribute('data-tipo') === tipo) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
    
    // Resetar para "Todos"
    select.value = 'todos';
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('mostrar');
        }
    });
}
</script>

</body>
</html>

