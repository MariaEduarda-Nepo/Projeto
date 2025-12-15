<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['id'])) {
    redirect('login');
}

// Apenas funcionários podem acessar
if ($_SESSION['tipo'] !== 'Funcionario') {
    redirect('home');
}

require_once __DIR__ . '/../Controller/CadastroController.php';
require_once __DIR__ . '/../Controller/AgendamentoController.php';
require_once __DIR__ . '/../Controller/FrequenciaController.php';
require_once __DIR__ . '/../Controller/ListaEsperaController.php';
require_once __DIR__ . '/../Model/CadastroDAO.php';

$cadastroController = new CadastroController();
$agendamentoController = new AgendamentoController();
$frequenciaController = new FrequenciaController();
$listaEsperaController = new ListaEsperaController();
$cadastroDAO = new CadastroDAO();

$mensagem = "";
$sucesso = false;

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'editar_usuario':
                $modalidades = [];
                if (isset($_POST['tipo']) && $_POST['tipo'] === 'Professor' && isset($_POST['modalidades'])) {
                    $modalidades = $_POST['modalidades'];
                }
                $resultado = $cadastroController->atualizar(
                    $_POST['usuario_id'],
                    $_POST['tipo'],
                    trim($_POST['nome']),
                    $_POST['email'],
                    !empty($_POST['nova_senha']) ? $_POST['nova_senha'] : null,
                    !empty($_POST['nova_senha']) ? $_POST['confirmar_nova_senha'] : null,
                    $_POST['cpf'],
                    $_POST['telefone'],
                    $_POST['datanascimento']
                );
                if ($resultado === true) {
                    // Atualizar modalidades se for Professor
                    if ($_POST['tipo'] === 'Professor' && !empty($modalidades)) {
                        require_once __DIR__ . '/../Model/Connection.php';
                        $conn = Connection::getInstance();
                        // Remover modalidades antigas
                        $stmt = $conn->prepare("DELETE FROM ProfessorModalidade WHERE professor_id = :professor_id");
                        $stmt->execute([':professor_id' => $_POST['usuario_id']]);
                        // Adicionar novas modalidades
                        foreach ($modalidades as $modalidade) {
                            $cadastroDAO->adicionarProfessorModalidade($_POST['usuario_id'], $modalidade);
                        }
                    }
                    $sucesso = true;
                    $mensagem = "Usuário atualizado com sucesso!";
                } else {
                    $mensagem = $resultado;
                }
                break;
            case 'excluir_usuario':
                if (isset($_POST['usuario_id'])) {
                    $cadastroController->deletar($_POST['usuario_id']);
                    $sucesso = true;
                    $mensagem = "Usuário excluído com sucesso!";
                }
                break;
        }
    }
}

// Endpoint AJAX para buscar alunos de uma turma
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'buscar_alunos_turma' && isset($_GET['modalidade']) && isset($_GET['data_aula']) && isset($_GET['horario'])) {
    header('Content-Type: application/json');
    $alunos = $agendamentoController->listarAlunosPorTurma($_GET['modalidade'], $_GET['data_aula'], $_GET['horario']);
    echo json_encode([
        'success' => true,
        'alunos' => $alunos
    ]);
    exit;
}

// Endpoint AJAX para buscar dados de um usuário
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'buscar_usuario' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $usuario = $cadastroDAO->buscarPorId($_GET['id']);
    if ($usuario) {
        // Buscar modalidades se for Professor
        $modalidades = [];
        if ($usuario->getTipo() === 'Professor') {
            require_once __DIR__ . '/../Model/Connection.php';
            $conn = Connection::getInstance();
            $stmt = $conn->prepare("SELECT modalidade FROM ProfessorModalidade WHERE professor_id = :professor_id");
            $stmt->execute([':professor_id' => $usuario->getId()]);
            $modalidades = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'modalidade');
        }
        
        echo json_encode([
            'success' => true,
            'usuario' => [
                'id' => $usuario->getId(),
                'tipo' => $usuario->getTipo(),
                'nome' => $usuario->getNome(),
                'email' => $usuario->getEmail(),
                'cpf' => $usuario->getCpf(),
                'telefone' => $usuario->getTelefone(),
                'datanascimento' => $usuario->getDataNascimento(),
                'modalidades' => $modalidades
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'mensagem' => 'Usuário não encontrado']);
    }
    exit;
}

// ============================================
// BUSCAR DADOS PARA EXIBICAO
// ============================================
// Busca todos os cadastros do banco de dados para exibir nas tabelas e estatísticas
$todosCadastros = $cadastroDAO->listarTodos();

// ===== CALCULAR ESTATISTICAS POR TIPO =====
// Filtra e conta quantos usuários existem de cada tipo
// array_filter() mantém apenas os cadastros que correspondem ao tipo
// count() conta quantos elementos restaram após o filtro
$totalAlunos = count(array_filter($todosCadastros, function($c) { return $c['tipo'] === 'Aluno'; }));
$totalProfessores = count(array_filter($todosCadastros, function($c) { return $c['tipo'] === 'Professor'; }));
$totalFuncionarios = count(array_filter($todosCadastros, function($c) { return $c['tipo'] === 'Funcionario'; }));

// ===== BUSCAR DADOS DE TURMAS =====
// Busca todas as turmas agendadas
$turmas = $agendamentoController->listarTurmas();
$totalTurmas = count($turmas); // Conta quantas turmas existem

// Calcula o total de alunos em todas as turmas
// array_column() extrai apenas a coluna 'total_alunos' de cada turma
// array_sum() soma todos os valores
$totalAlunosTurmas = array_sum(array_column($turmas, 'total_alunos'));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Painel Administrativo</title>
    <link rel="stylesheet" href="<?php echo asset('header-footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('agendaraulas.css'); ?>">
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
            border-left: 4px solid #8b5cf6;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .admin-card:hover {
            transform: translateY(-5px);
        }
        .admin-card h3 {
            color: #8b5cf6;
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
            color: #8b5cf6;
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
            color: #8b5cf6;
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
            background: linear-gradient(90deg, #8b5cf6, #7c3aed);
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
            background: linear-gradient(90deg, #7c3aed, #8b5cf6);
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
            overflow-x: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
            border: 2px solid rgba(168, 59, 211, 0.3);
        }
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }
        .modal-content::-webkit-scrollbar-track {
            background: rgba(15, 15, 30, 0.8);
            border-radius: 4px;
        }
        .modal-content::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #8b5cf6, #9333ea);
            border-radius: 4px;
        }
        .modal-content h2 {
            color: #8b5cf6;
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
        .modal-content .form-group input[type="email"],
        .modal-content .form-group input[type="tel"],
        .modal-content .form-group input[type="date"],
        .modal-content .form-group input[type="password"],
        .modal-content .form-group select,
        .modal-content .form-group textarea {
            width: 100%;
            padding: 0 20px;
            height: 50px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(240, 240, 240, 0.95));
            border: 2px solid rgba(168, 59, 211, 0.3);
            border-radius: 12px;
            color: #4d4d4d;
            font-size: 15px;
            font-family: 'Roboto', sans-serif;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .modal-content .form-group select {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 45px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(240, 240, 240, 0.95)),
                        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%231a1a2e' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat, no-repeat;
            background-position: center, right 15px center;
        }
        .modal-content .form-group select:focus {
            background: linear-gradient(145deg, rgba(255, 255, 255, 1), rgba(250, 250, 250, 1)),
                        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%231a1a2e' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat, no-repeat;
            background-position: center, right 15px center;
        }
        .modal-content .form-group select option {
            background: #ffffff;
            color: #4d4d4d;
            padding: 10px;
        }
        .modal-content .form-group input[type="text"]:focus,
        .modal-content .form-group input[type="email"]:focus,
        .modal-content .form-group input[type="tel"]:focus,
        .modal-content .form-group input[type="date"]:focus,
        .modal-content .form-group input[type="password"]:focus,
        .modal-content .form-group select:focus,
        .modal-content .form-group textarea:focus {
            outline: none;
            border: 2px solid #8b5cf6;
            background: linear-gradient(145deg, rgba(255, 255, 255, 1), rgba(250, 250, 250, 1));
            box-shadow: 0 0 15px rgba(168, 59, 211, 0.4);
            transform: translateY(-2px);
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
            table-layout: auto;
        }
        #tabelaUsuarios {
            width: 100%;
            display: table;
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
            color: #8b5cf6;
            font-weight: bold;
        }
        .modal-content table tbody tr:hover {
            background: rgba(168, 59, 211, 0.1);
        }
        .close-modal {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #8b5cf6;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .close-modal:hover {
            color: #7c3aed;
        }
        /* Mensagens JavaScript */
        .mensagem {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: bold;
            text-align: center;
            transition: opacity 0.3s ease;
        }
        .mensagem.sucesso {
            background: rgba(76, 209, 55, 0.2);
            border: 2px solid #4cd137;
            color: #4cd137;
        }
        .mensagem.erro {
            background: rgba(255, 107, 107, 0.2);
            border: 2px solid #ff6b6b;
            color: #ff6b6b;
        }
        #mensagem-js {
            position: relative;
            margin: 20px auto;
            max-width: 90%;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<main class="admin-container">
    <h1><i class="fas fa-tachometer-alt"></i> Painel Administrativo</h1>

    <?php if (!empty($mensagem)): ?>
        <div class="mensagem <?php echo $sucesso ? 'sucesso' : 'erro'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>
    
    <!-- Container para mensagens JavaScript -->
    <div id="mensagem-js" style="display: none;"></div>

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


    <!-- AÇÕES RÁPIDAS -->
    <section class="admin-section">
        <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
        <div class="quick-actions">
            <a href="<?php echo url('relatorios'); ?>" class="action-btn">
                <i class="fas fa-chart-bar"></i> Relatórios de Ocupação
            </a>
        </div>
    </section>

    <!-- GERENCIAR AGENDAMENTOS -->
    <section class="admin-section">
        <h2><i class="fas fa-calendar-check"></i> Gerenciar Agendamentos</h2>
        <?php if (empty($turmas)): ?>
            <p style="color: #cccccc; text-align: center; padding: 20px;">Nenhuma turma agendada no momento.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background: rgba(168, 59, 211, 0.2);">
                            <th style="padding: 15px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Turma</th>
                            <th style="padding: 15px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Horário</th>
                            <th style="padding: 15px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Dia</th>
                            <th style="padding: 15px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Professor</th>
                            <th style="padding: 15px; text-align: center; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($turmas as $turma): 
                            // Buscar nome do professor (pegar o primeiro ID da lista)
                            $professoresIds = explode(',', $turma['professores_ids']);
                            $professorId = $professoresIds[0];
                            $professor = $cadastroDAO->buscarPorId($professorId);
                            // buscarPorId retorna um objeto Cadastro, não um array
                            $professorNome = $professor ? $professor->getNome() : 'N/A';
                            $dataFormatada = date('d/m/Y', strtotime($turma['data_aula']));
                        ?>
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                <td style="padding: 15px; color: #ffffff;"><?php echo htmlspecialchars($turma['modalidade']); ?></td>
                                <td style="padding: 15px; color: #ffffff;"><?php echo htmlspecialchars($turma['horario']); ?></td>
                                <td style="padding: 15px; color: #ffffff;"><?php echo $dataFormatada; ?></td>
                                <td style="padding: 15px; color: #ffffff;"><?php echo htmlspecialchars($professorNome); ?></td>
                                <td style="padding: 15px; text-align: center;">
                                    <button onclick="visualizarAlunos('<?php echo htmlspecialchars($turma['modalidade']); ?>', '<?php echo $turma['data_aula']; ?>', '<?php echo htmlspecialchars($turma['horario']); ?>')" 
                                            class="action-btn" style="padding: 8px 15px; font-size: 0.9em;">
                                        <i class="fas fa-eye"></i> Visualizar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- GERENCIAR CADASTROS -->
    <section class="admin-section">
        <h2><i class="fas fa-user-cog"></i> Gerenciar Cadastros</h2>
        <div class="quick-actions">
            <a href="<?php echo url('cadastro'); ?>" class="action-btn">
                <i class="fas fa-user-plus"></i> Cadastrar Novo Usuário
            </a>
            <button onclick="abrirModal('modalListaUsuarios')" class="action-btn">
                <i class="fas fa-list"></i> Listar Usuários
            </button>
        </div>
    </section>
</main>

<!-- MODAL DE LISTA DE USUÁRIOS -->
<div id="modalListaUsuarios" class="modal">
    <div class="modal-content" style="max-width: 900px; width: 95%;">
        <span class="close-modal" onclick="fecharModal('modalListaUsuarios')">&times;</span>
        <h2><i class="fas fa-list"></i> Lista de Usuários</h2>
        
        <!-- Campo de pesquisa -->
        <div class="form-group" style="margin-bottom: 20px;">
            <input type="text" id="pesquisaUsuario" placeholder="🔍 Pesquisar por nome..." 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 2px solid rgba(168, 59, 211, 0.3); background: rgba(15, 15, 30, 0.8); color: #ffffff; font-size: 14px;"
                   onkeyup="filtrarUsuarios()">
        </div>
        
        <div style="max-height: 60vh; overflow-y: auto; overflow-x: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(168, 59, 211, 0.2); position: sticky; top: 0; z-index: 10;">
                        <th style="padding: 12px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Nome</th>
                        <th style="padding: 12px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Email</th>
                        <th style="padding: 12px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Tipo</th>
                        <th style="padding: 12px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">CPF</th>
                        <th style="padding: 12px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Telefone</th>
                        <th style="padding: 12px; text-align: center; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaUsuarios">
                    <?php foreach ($todosCadastros as $cadastro): ?>
                        <tr class="linha-usuario" data-nome="<?php echo strtolower(htmlspecialchars($cadastro['nome'])); ?>">
                            <td style="padding: 12px; color: #ffffff; border-bottom: 1px solid rgba(255, 255, 255, 0.1);"><?php echo htmlspecialchars($cadastro['nome']); ?></td>
                            <td style="padding: 12px; color: #ffffff; border-bottom: 1px solid rgba(255, 255, 255, 0.1);"><?php echo htmlspecialchars($cadastro['email']); ?></td>
                            <td style="padding: 12px; color: #ffffff; border-bottom: 1px solid rgba(255, 255, 255, 0.1);"><?php echo htmlspecialchars($cadastro['tipo']); ?></td>
                            <td style="padding: 12px; color: #ffffff; border-bottom: 1px solid rgba(255, 255, 255, 0.1);"><?php echo htmlspecialchars($cadastro['cpf'] ?? '-'); ?></td>
                            <td style="padding: 12px; color: #ffffff; border-bottom: 1px solid rgba(255, 255, 255, 0.1);"><?php echo htmlspecialchars($cadastro['telefone'] ?? '-'); ?></td>
                            <td style="padding: 12px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                <button onclick="editarUsuario(<?php echo $cadastro['id']; ?>)" 
                                        style="padding: 6px 12px; margin: 0 8px; background: linear-gradient(135deg, #4CAF50, #45a049); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85em;">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button onclick="confirmarExclusao(<?php echo $cadastro['id']; ?>, '<?php echo htmlspecialchars(addslashes($cadastro['nome'])); ?>')" 
                                        style="padding: 6px 12px; margin: 0 8px; background: linear-gradient(135deg, #f44336, #da190b); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85em;">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DE EDIÇÃO DE USUÁRIO -->
<div id="modalEditarUsuario" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <span class="close-modal" onclick="fecharModal('modalEditarUsuario')">&times;</span>
        <h2><i class="fas fa-user-edit"></i> Editar Usuário</h2>
        <form method="POST" id="formEditarUsuario">
            <input type="hidden" name="acao" value="editar_usuario">
            <input type="hidden" name="usuario_id" id="editar_usuario_id">
            
            <div class="form-group">
                <label class="field-label">Tipo:</label>
                <select name="tipo" id="editar_tipo" required onchange="toggleModalidadesEdicao()">
                    <option value="Aluno">Aluno</option>
                    <option value="Professor">Professor</option>
                    <option value="Funcionario">Funcionário</option>
                </select>
            </div>
            
            <div id="modalidades-edicao-container" style="display: none; margin-bottom: 20px;">
                <label class="field-label">Modalidades:</label>
                <div style="display: flex; flex-direction: column; gap: 10px; padding: 15px; background: rgba(168, 59, 211, 0.1); border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="modalidades[]" value="Boxe" id="editar_modalidade_boxe">
                        <span>Boxe</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="modalidades[]" value="Muay Thai" id="editar_modalidade_muay">
                        <span>Muay Thai</span>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="field-label">Nome:</label>
                <input type="text" name="nome" id="editar_nome" required>
            </div>
            
            <div class="form-group">
                <label class="field-label">Email:</label>
                <input type="email" name="email" id="editar_email" required>
            </div>
            
            <div class="form-group">
                <label class="field-label">CPF:</label>
                <input type="text" name="cpf" id="editar_cpf" placeholder="000.000.000-00" maxlength="14">
            </div>
            
            <div class="form-group">
                <label class="field-label">Telefone:</label>
                <input type="tel" name="telefone" id="editar_telefone" placeholder="(00) 00000-0000" maxlength="15">
            </div>
            
            <div class="form-group">
                <label class="field-label">Data de Nascimento:</label>
                <input type="date" name="datanascimento" id="editar_datanascimento">
            </div>
            
            <div class="form-group">
                <label class="field-label">Nova Senha (deixe em branco para manter a atual):</label>
                <input type="password" name="nova_senha" id="editar_nova_senha" minlength="6">
            </div>
            
            <div class="form-group">
                <label class="field-label">Confirmar Nova Senha:</label>
                <input type="password" name="confirmar_nova_senha" id="editar_confirmar_senha" minlength="6">
            </div>
            
            <button type="submit" class="btn-agendar">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>
    </div>
</div>

<!-- MODAL DE ALUNOS DA TURMA -->
<div id="modalAlunosTurma" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="fecharModal('modalAlunosTurma')">&times;</span>
        <h2><i class="fas fa-users"></i> Alunos da Turma</h2>
        <div id="alunos-turma-content" style="max-height: 500px; overflow-y: auto;">
            <p style="text-align: center; color: #cccccc; padding: 20px;">Carregando...</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

<script>
function abrirModal(id) {
    document.getElementById(id).classList.add('mostrar');
}

function fecharModal(id) {
    document.getElementById(id).classList.remove('mostrar');
}

// ============================================
// FUNCAO: FILTRAR USUARIOS NA TABELA
// ============================================
// Esta função é chamada quando o usuário digita no campo de pesquisa
// Filtra as linhas da tabela mostrando apenas as que correspondem à busca
function filtrarUsuarios() {
    // Pega o texto digitado no campo de pesquisa e converte para minúsculas
    // toLowerCase() permite busca case-insensitive (não diferencia maiúsculas/minúsculas)
    const pesquisa = document.getElementById('pesquisaUsuario').value.toLowerCase();
    
    // Seleciona todas as linhas da tabela que têm a classe 'linha-usuario'
    // Cada linha representa um usuário cadastrado
    const linhas = document.querySelectorAll('#tabelaUsuarios .linha-usuario');
    
    // Percorre cada linha da tabela
    linhas.forEach(linha => {
        // Pega o nome do usuário que está armazenado no atributo data-nome
        // O atributo data-nome foi definido no HTML com o nome em minúsculas
        const nome = linha.getAttribute('data-nome');
        
        // Verifica se o nome contém o texto pesquisado
        // includes() verifica se uma string contém outra string
        if (nome.includes(pesquisa)) {
            // Se encontrou, mostra a linha (display: '')
            linha.style.display = '';
        } else {
            // Se não encontrou, esconde a linha (display: 'none')
            linha.style.display = 'none';
        }
    });
}

function editarUsuario(id) {
    // Buscar dados do usuário via AJAX
    fetch('<?php echo url('admin'); ?>?acao=buscar_usuario&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.usuario) {
                const u = data.usuario;
                document.getElementById('editar_usuario_id').value = u.id;
                document.getElementById('editar_tipo').value = u.tipo;
                document.getElementById('editar_nome').value = u.nome;
                document.getElementById('editar_email').value = u.email;
                // Exibir CPF e telefone com formatação
                let cpfValue = (u.cpf || '').replace(/\D/g, '');
                if (cpfValue.length === 11) {
                    cpfValue = cpfValue.substring(0, 3) + '.' + cpfValue.substring(3, 6) + '.' + cpfValue.substring(6, 9) + '-' + cpfValue.substring(9, 11);
                }
                document.getElementById('editar_cpf').value = cpfValue;
                
                let telefoneValue = (u.telefone || '').replace(/\D/g, '');
                if (telefoneValue.length === 10) {
                    telefoneValue = '(' + telefoneValue.substring(0, 2) + ') ' + telefoneValue.substring(2, 6) + '-' + telefoneValue.substring(6);
                } else if (telefoneValue.length === 11) {
                    telefoneValue = '(' + telefoneValue.substring(0, 2) + ') ' + telefoneValue.substring(2, 7) + '-' + telefoneValue.substring(7, 11);
                }
                document.getElementById('editar_telefone').value = telefoneValue;
                document.getElementById('editar_datanascimento').value = u.datanascimento || '';
                
                // Se for Professor, buscar modalidades
                if (u.tipo === 'Professor' && u.modalidades) {
                    toggleModalidadesEdicao();
                    u.modalidades.forEach(mod => {
                        if (mod === 'Boxe') document.getElementById('editar_modalidade_boxe').checked = true;
                        if (mod === 'Muay Thai') document.getElementById('editar_modalidade_muay').checked = true;
                    });
                } else {
                    document.getElementById('modalidades-edicao-container').style.display = 'none';
                }
                
                abrirModal('modalEditarUsuario');
            }
        })
        .catch(error => {
            console.error('Erro ao carregar usuário:', error);
            exibirMensagem('Erro ao carregar dados do usuário.', 'erro');
        });
}

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

function toggleModalidadesEdicao() {
    const tipo = document.getElementById('editar_tipo').value;
    const container = document.getElementById('modalidades-edicao-container');
    if (tipo === 'Professor') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        document.getElementById('editar_modalidade_boxe').checked = false;
        document.getElementById('editar_modalidade_muay').checked = false;
    }
}

function confirmarExclusao(id, nome) {
    if (confirm('Tem certeza que deseja excluir o usuário "' + nome + '"?\n\nEsta ação não pode ser desfeita!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="acao" value="excluir_usuario"><input type="hidden" name="usuario_id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function visualizarAlunos(modalidade, dataAula, horario) {
    // Abrir modal
    abrirModal('modalAlunosTurma');
    
    // Mostrar loading
    document.getElementById('alunos-turma-content').innerHTML = '<p style="text-align: center; color: #cccccc; padding: 20px;">Carregando...</p>';
    
    // Fazer requisição AJAX
    fetch('<?php echo url('admin'); ?>?acao=buscar_alunos_turma&modalidade=' + encodeURIComponent(modalidade) + '&data_aula=' + encodeURIComponent(dataAula) + '&horario=' + encodeURIComponent(horario))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.alunos && data.alunos.length > 0) {
                let html = '<table style="width: 100%; border-collapse: collapse;">';
                html += '<thead><tr style="background: rgba(168, 59, 211, 0.2);">';
                html += '<th style="padding: 12px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Nome</th>';
                html += '<th style="padding: 12px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Email</th>';
                html += '<th style="padding: 12px; text-align: left; color: #8b5cf6; border-bottom: 2px solid #8b5cf6;">Professor</th>';
                html += '</tr></thead><tbody>';
                
                data.alunos.forEach(function(aluno) {
                    html += '<tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">';
                    html += '<td style="padding: 12px; color: #ffffff;">' + aluno.aluno_nome + '</td>';
                    html += '<td style="padding: 12px; color: #ffffff;">' + aluno.aluno_email + '</td>';
                    html += '<td style="padding: 12px; color: #ffffff;">' + aluno.professor_nome + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                html += '<p style="margin-top: 15px; color: #cccccc; text-align: center;">Total: ' + data.alunos.length + ' aluno(s)</p>';
                document.getElementById('alunos-turma-content').innerHTML = html;
            } else {
                document.getElementById('alunos-turma-content').innerHTML = '<p style="text-align: center; color: #cccccc; padding: 20px;">Nenhum aluno encontrado nesta turma.</p>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar alunos:', error);
            document.getElementById('alunos-turma-content').innerHTML = '<p style="text-align: center; color: #ff4444; padding: 20px;">Erro ao carregar alunos da turma.</p>';
        });
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

// Formatação automática de CPF no modal de edição
document.addEventListener('DOMContentLoaded', function() {
    const cpfInput = document.getElementById('editar_cpf');
    const telefoneInput = document.getElementById('editar_telefone');
    
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            
            if (value.length <= 11) {
                // Aplica máscara: 000.000.000-00
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.substring(0, 3) + '.' + value.substring(3);
                } else if (value.length <= 9) {
                    value = value.substring(0, 3) + '.' + value.substring(3, 6) + '.' + value.substring(6);
                } else {
                    value = value.substring(0, 3) + '.' + value.substring(3, 6) + '.' + value.substring(6, 9) + '-' + value.substring(9, 11);
                }
                e.target.value = value;
            }
        });
        
        // Previne digitação de caracteres não numéricos
        cpfInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }
    
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
            
            if (value.length <= 11) {
                // Aplica máscara: (00) 00000-0000 ou (00) 0000-0000
                if (value.length === 0) {
                    value = '';
                } else if (value.length <= 2) {
                    value = '(' + value;
                } else if (value.length <= 6) {
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
                } else if (value.length <= 10) {
                    // Telefone fixo: (00) 0000-0000
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 6) + '-' + value.substring(6);
                } else {
                    // Celular: (00) 00000-0000
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7, 11);
                }
                e.target.value = value;
            }
        });
        
        // Previne digitação de caracteres não numéricos
        telefoneInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }
});

</script>

</body>
</html>

