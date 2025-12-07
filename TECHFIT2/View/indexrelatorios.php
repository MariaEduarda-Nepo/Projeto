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

require_once __DIR__ . '/../Controller/FrequenciaController.php';
require_once __DIR__ . '/../Controller/AgendamentoController.php';

$frequenciaController = new FrequenciaController();
$agendamentoController = new AgendamentoController();

$dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

$relatorioOcupacao = $frequenciaController->gerarRelatorioOcupacao($dataInicio, $dataFim);
$turmas = $agendamentoController->listarTurmas($dataInicio, $dataFim);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Relatórios de Ocupação</title>
    <link rel="stylesheet" href="header-footer.css">
    <link rel="stylesheet" href="agendaraulas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .relatorios-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .filtros-box {
            background-color: rgba(26, 26, 26, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            margin-bottom: 30px;
        }
        .filtros-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        .filtros-form .form-group {
            flex: 1;
            min-width: 200px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .filtros-form .form-group input[type="date"] {
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
        .filtros-form .form-group input[type="date"]:focus {
            outline: none;
            border-color: #a83bd3;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 10px rgba(168, 59, 211, 0.3);
        }
        .filtros-form .form-group button {
            width: 100%;
            padding: 12px 20px;
        }
        .relatorio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .relatorio-card {
            background-color: rgba(26, 26, 26, 0.95);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #a83bd3;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .relatorio-card:hover {
            transform: translateY(-5px);
        }
        .relatorio-card h3 {
            color: #a83bd3;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #a83bd3;
            margin: 10px 0;
        }
        .relatorio-card p {
            color: #cccccc;
            margin-top: 10px;
        }
        .turma-card {
            background-color: rgba(26, 26, 26, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #a83bd3;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .turma-card h3 {
            color: #a83bd3;
            margin-bottom: 10px;
        }
        .turma-card p {
            color: #cccccc;
            margin: 5px 0;
        }
        .ocupacao-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            margin-left: 10px;
            font-size: 0.9em;
        }
        .ocupacao-baixa { background: #4caf50; color: white; }
        .ocupacao-media { background: #ff9800; color: white; }
        .ocupacao-alta { background: #f44336; color: white; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: rgba(26, 26, 26, 0.95);
            border-radius: 10px;
            overflow: hidden;
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #cccccc;
        }
        table th {
            background: rgba(168, 59, 211, 0.3);
            color: #a83bd3;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 1px;
        }
        table tbody tr:hover {
            background: rgba(168, 59, 211, 0.1);
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">
        <img src="img/logotechfit-removebg-preview.png" alt="Logo TechFit">
        <div class="logo-text">
            <strong>TECH<span class="fit">FIT</span></strong>
            <span class="subtext">FUTURE FITNESS</span>
        </div>
    </div>
    <nav class="menu">
        <a href="indexpaginainicial.php">INÍCIO</a>
        <a href="indexplanos.php">PLANOS</a>
        <a href="indexpaineladmin.php">PAINEL ADMIN</a>
        <div class="user-info">
            <span class="user-greeting">
                <i class="fas fa-user-circle"></i>
                Olá, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
            </span>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> SAIR
            </a>
        </div>
    </nav>
</header>

<main class="relatorios-container">
    <h1><i class="fas fa-chart-bar"></i> Relatórios de Ocupação</h1>

    <!-- FILTROS -->
    <div class="filtros-box">
        <form method="GET" class="filtros-form">
            <div class="form-group">
                <label class="field-label">Data Início:</label>
                <input type="date" name="data_inicio" value="<?php echo htmlspecialchars($dataInicio); ?>" required>
            </div>
            <div class="form-group">
                <label class="field-label">Data Fim:</label>
                <input type="date" name="data_fim" value="<?php echo htmlspecialchars($dataFim); ?>" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-agendar">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- ESTATÍSTICAS GERAIS -->
    <section class="agendamentos-section">
        <h2><i class="fas fa-chart-pie"></i> Estatísticas Gerais</h2>
        <div class="relatorio-grid">
            <?php
            $totalAcessos = count($relatorioOcupacao);
            $totalAlunosUnicos = count(array_unique(array_column($relatorioOcupacao, 'alunos_unicos')));
            $totalTurmas = count($turmas);
            $totalAlunosTurmas = array_sum(array_column($turmas, 'total_alunos'));
            ?>
            <div class="relatorio-card">
                <h3>Total de Acessos</h3>
                <div class="stat-number"><?php echo $totalAcessos; ?></div>
                <p>Período: <?php echo date('d/m/Y', strtotime($dataInicio)); ?> a <?php echo date('d/m/Y', strtotime($dataFim)); ?></p>
            </div>
            <div class="relatorio-card">
                <h3>Alunos Únicos</h3>
                <div class="stat-number"><?php echo $totalAlunosUnicos; ?></div>
                <p>Alunos que acessaram a academia</p>
            </div>
            <div class="relatorio-card">
                <h3>Turmas Ativas</h3>
                <div class="stat-number"><?php echo $totalTurmas; ?></div>
                <p>Total de turmas com alunos</p>
            </div>
            <div class="relatorio-card">
                <h3>Total de Alunos em Turmas</h3>
                <div class="stat-number"><?php echo $totalAlunosTurmas; ?></div>
                <p>Alunos agendados nas turmas</p>
            </div>
        </div>
    </section>

    <!-- OCUPAÇÃO POR HORÁRIO -->
    <section class="agendamentos-section">
        <h2><i class="fas fa-clock"></i> Ocupação por Horário</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Total de Acessos</th>
                    <th>Alunos Únicos</th>
                    <th>Ocupação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($relatorioOcupacao)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">
                            Nenhum dado encontrado para o período selecionado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($relatorioOcupacao as $item): 
                        $ocupacao = $item['total_acessos'];
                        $ocupacaoClass = 'ocupacao-baixa';
                        if ($ocupacao > 50) $ocupacaoClass = 'ocupacao-alta';
                        elseif ($ocupacao > 20) $ocupacaoClass = 'ocupacao-media';
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($item['data'])); ?></td>
                            <td><?php echo $item['hora']; ?>h</td>
                            <td><?php echo $item['total_acessos']; ?></td>
                            <td><?php echo $item['alunos_unicos']; ?></td>
                            <td>
                                <span class="ocupacao-badge <?php echo $ocupacaoClass; ?>">
                                    <?php 
                                    if ($ocupacao > 50) echo 'Alta';
                                    elseif ($ocupacao > 20) echo 'Média';
                                    else echo 'Baixa';
                                    ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- OCUPAÇÃO DAS TURMAS -->
    <section class="agendamentos-section">
        <h2><i class="fas fa-users"></i> Ocupação das Turmas</h2>
        <?php if (empty($turmas)): ?>
            <div class="sem-agendamentos">
                <i class="fas fa-calendar-times"></i>
                <p>Nenhuma turma encontrada para o período selecionado.</p>
            </div>
        <?php else: ?>
            <?php foreach ($turmas as $turma): 
                $dataAula = new DateTime($turma['data_aula']);
                $ocupacaoPercent = ($turma['total_alunos'] / 20) * 100;
                $ocupacaoClass = 'ocupacao-baixa';
                if ($ocupacaoPercent >= 100) $ocupacaoClass = 'ocupacao-alta';
                elseif ($ocupacaoPercent >= 80) $ocupacaoClass = 'ocupacao-media';
            ?>
                <div class="turma-card">
                    <h3>
                        <?php echo htmlspecialchars($turma['modalidade']); ?> - 
                        <?php echo $dataAula->format('d/m/Y'); ?> - 
                        <?php echo htmlspecialchars($turma['horario']); ?>
                    </h3>
                    <p>
                        <strong>Alunos:</strong> <?php echo $turma['total_alunos']; ?>/20
                        <span class="ocupacao-badge <?php echo $ocupacaoClass; ?>">
                            <?php echo number_format($ocupacaoPercent, 1); ?>%
                        </span>
                    </p>
                    <?php if ($turma['total_alunos'] >= 20): ?>
                        <p style="color: #ff5555;"><i class="fas fa-exclamation-triangle"></i> Turma completa!</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<footer>
    <h3>Desenvolvido por: Daniel Charlo e Maria Eduarda Nepomuceno</h3>
    <a href="https://github.com/MariaEduarda-Nepo" target="_blank"><img src="img/Github.png" alt="GitHub Maria Eduarda"></a>
    <a href="https://github.com/DanielCharlo" target="_blank"><img src="img/Github.png" alt="GitHub Daniel"></a>
</footer>

</body>
</html>

