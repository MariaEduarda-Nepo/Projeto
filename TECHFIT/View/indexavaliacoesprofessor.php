<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';

// Apenas professores podem acessar
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'Professor') {
    redirect('login');
}

require_once __DIR__ . '/../Controller/AvaliacaoFisicaController.php';
require_once __DIR__ . '/../Model/CadastroDAO.php';

$controller = new AvaliacaoFisicaController();
$cadastroDAO = new CadastroDAO();

$mensagem = "";
$sucesso = false;

// Processar criação de avaliação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar_avaliacao') {
    $alunoId = intval($_POST['aluno_id']);
    
    // Converter altura de cm para metros antes de salvar
    $altura = null;
    if (!empty($_POST['altura'])) {
        $altura = floatval($_POST['altura']) / 100; // Converte cm para metros
    }
    
    $resultado = $controller->criar(
        $alunoId,
        $_POST['data_avaliacao'],
        !empty($_POST['peso']) ? $_POST['peso'] : null,
        $altura,
        !empty($_POST['percentual_gordura']) ? $_POST['percentual_gordura'] : null,
        !empty($_POST['massa_muscular']) ? $_POST['massa_muscular'] : null,
        !empty($_POST['circunferencia_braco']) ? $_POST['circunferencia_braco'] : null,
        !empty($_POST['circunferencia_cintura']) ? $_POST['circunferencia_cintura'] : null,
        !empty($_POST['circunferencia_quadril']) ? $_POST['circunferencia_quadril'] : null,
        !empty($_POST['observacoes']) ? $_POST['observacoes'] : null,
        !empty($_POST['proxima_avaliacao']) ? $_POST['proxima_avaliacao'] : null
    );

    if ($resultado === true) {
        $sucesso = true;
        $mensagem = "Avaliação física registrada com sucesso!";
    } else {
        $mensagem = $resultado;
    }
}

// Buscar alunos para o select
$todosCadastros = $cadastroDAO->listarTodos();
$alunos = array_filter($todosCadastros, function($c) {
    return $c['tipo'] === 'Aluno';
});

// ============================================
// PROCESSAMENTO DE DADOS PARA EXIBICAO
// ============================================
// Busca e organiza as avaliações para exibir em cards agrupados por aluno

// Pega o filtro de busca do formulário (se houver)
$nomeFiltro = isset($_GET['busca']) ? trim($_GET['busca']) : null;

// ===== ETAPA 1: BUSCAR TODAS AS AVALIACOES =====
// Busca todas as avaliações físicas do banco de dados
// O método listarTodos() retorna todas as avaliações com nome e email do aluno
require_once __DIR__ . '/../Model/AvaliacaoFisicaDAO.php';
$avaliacaoDAO = new AvaliacaoFisicaDAO();
$todasAvaliacoes = $avaliacaoDAO->listarTodos();

// ===== ETAPA 2: FILTRAR POR NOME/EMAIL (SE FORNECIDO) =====
// Se o usuário digitou algo no campo de busca, filtra as avaliações
if ($nomeFiltro) {
    // array_filter() percorre todas as avaliações e mantém apenas as que correspondem ao filtro
    // stripos() faz busca case-insensitive (não diferencia maiúsculas/minúsculas)
    // Verifica tanto no nome quanto no email do aluno
    $todasAvaliacoes = array_filter($todasAvaliacoes, function($av) use ($nomeFiltro) {
        return stripos($av['aluno_nome'] ?? '', $nomeFiltro) !== false || 
               stripos($av['aluno_email'] ?? '', $nomeFiltro) !== false;
    });
}

// ===== ETAPA 3: AGRUPAR AVALIACOES POR ALUNO =====
// Organiza as avaliações em um array onde cada chave é o ID do aluno
// Isso permite exibir todas as avaliações de cada aluno juntas
$avaliacoesPorAluno = [];
foreach ($todasAvaliacoes as $avaliacao) {
    $alunoId = $avaliacao['aluno_id'];
    
    // Se é a primeira avaliação deste aluno, cria uma entrada no array
    if (!isset($avaliacoesPorAluno[$alunoId])) {
        $avaliacoesPorAluno[$alunoId] = [
            'aluno_id' => $alunoId,
            'aluno_nome' => $avaliacao['aluno_nome'],
            'aluno_email' => $avaliacao['aluno_email'] ?? '',
            'avaliacoes' => [] // Array que vai armazenar todas as avaliações deste aluno
        ];
    }
    // Adiciona esta avaliação ao array de avaliações do aluno
    $avaliacoesPorAluno[$alunoId]['avaliacoes'][] = $avaliacao;
}

// ===== ETAPA 4: ORDENAR AVALIACOES POR DATA =====
// Para cada aluno, ordena suas avaliações da mais recente para a mais antiga
// usort() ordena o array usando uma função de comparação personalizada
foreach ($avaliacoesPorAluno as &$aluno) {
    usort($aluno['avaliacoes'], function($a, $b) {
        // strtotime() converte a data em timestamp (número)
        // Subtrai para ordenar do maior (mais recente) para o menor (mais antigo)
        return strtotime($b['data_avaliacao']) - strtotime($a['data_avaliacao']);
    });
}
unset($aluno); // Remove a referência para evitar problemas
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Avaliações dos Alunos</title>
    <link rel="stylesheet" href="<?php echo asset('header-footer.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .avaliacoes-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .avaliacoes-container h1 {
            font-size: 2.5em;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            color: #8b5cf6;
        }

        .search-container {
            background: rgba(26, 26, 26, 0.95);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
        }

        .search-box {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-box input {
            flex: 1;
            padding: 15px 20px;
            background: rgba(15, 15, 30, 0.8);
            border: 2px solid rgba(168, 59, 211, 0.3);
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 15px rgba(168, 59, 211, 0.4);
            background: rgba(20, 20, 40, 0.9);
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .search-box button {
            padding: 15px 30px;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-box button:hover {
            background: linear-gradient(135deg, #8a2db8, #b84be0);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(168, 59, 211, 0.4);
        }

        .avaliacoes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .avaliacao-card {
            background: rgba(26, 26, 26, 0.95);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #8b5cf6;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .avaliacao-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(168, 59, 211, 0.3);
        }

        .avaliacao-card h3 {
            color: #8b5cf6;
            margin-bottom: 15px;
            font-size: 1.4em;
            border-bottom: 2px solid rgba(168, 59, 211, 0.3);
            padding-bottom: 10px;
        }

        .avaliacao-card .aluno-info {
            color: #cccccc;
            font-size: 0.9em;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .avaliacao-card .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 0;
            padding: 10px;
            background: rgba(15, 15, 30, 0.5);
            border-radius: 8px;
        }

        .avaliacao-card .info-label {
            color: #cccccc;
            font-weight: 500;
        }

        .avaliacao-card .info-value {
            color: #ffffff;
            font-weight: bold;
            font-size: 1.1em;
        }

        .imc-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.85em;
            margin-left: 10px;
        }

        .imc-normal { background: #4caf50; color: white; }
        .imc-sobrepeso { background: #ff9800; color: white; }
        .imc-obesidade { background: #f44336; color: white; }
        .imc-abaixo { background: #2196f3; color: white; }

        .sem-avaliacoes {
            text-align: center;
            padding: 60px 20px;
            color: #888;
            background: rgba(26, 26, 26, 0.95);
            border-radius: 15px;
        }

        .sem-avaliacoes i {
            font-size: 4em;
            margin-bottom: 20px;
            color: #555;
        }

        .data-avaliacao {
            color: #8b5cf6;
            font-size: 0.9em;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(168, 59, 211, 0.2);
        }

        .aluno-item {
            background: rgba(26, 26, 26, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #8b5cf6;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .aluno-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .aluno-header:hover {
            opacity: 0.9;
        }

        .aluno-header h3 {
            color: #8b5cf6;
            margin: 0;
            font-size: 1.3em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .aluno-header .aluno-email {
            color: #cccccc;
            font-size: 0.9em;
            margin-left: 10px;
            opacity: 0.8;
        }

        .btn-ver-avaliacoes {
            padding: 10px 20px;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-ver-avaliacoes:hover {
            background: linear-gradient(135deg, #9333ea, #a78bfa);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(168, 59, 211, 0.4);
        }

        .btn-ver-avaliacoes i {
            transition: transform 0.3s ease;
        }

        .btn-ver-avaliacoes.expanded i {
            transform: rotate(180deg);
        }

        .avaliacoes-aluno {
            display: none;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(168, 59, 211, 0.2);
        }

        .avaliacoes-aluno.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .avaliacoes-aluno .avaliacoes-grid {
            margin-top: 15px;
        }

        .avaliacoes-container {
            padding-bottom: 60px;
            min-height: calc(100vh - 200px);
        }

        .btn-nova-avaliacao {
            padding: 15px 30px;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .btn-nova-avaliacao:hover {
            background: linear-gradient(135deg, #9333ea, #a78bfa);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(168, 59, 211, 0.4);
        }

        .form-nova-avaliacao {
            background: rgba(26, 26, 26, 0.95);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            display: none;
        }

        .form-nova-avaliacao.show {
            display: block;
        }

        .form-nova-avaliacao h3 {
            color: #8b5cf6;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #cccccc;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(15, 15, 30, 0.8);
            border: 2px solid rgba(168, 59, 211, 0.3);
            border-radius: 12px;
            color: #ffffff;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 15px rgba(168, 59, 211, 0.4);
            background: rgba(20, 20, 40, 0.9);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-submit {
            padding: 12px 30px;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #9333ea, #a78bfa);
            transform: translateY(-2px);
        }

        .btn-cancel {
            padding: 12px 30px;
            background: rgba(100, 100, 100, 0.5);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: rgba(120, 120, 120, 0.7);
        }

        .mensagem {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: bold;
            text-align: center;
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

        @media (max-width: 768px) {
            .form-nova-avaliacao div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
            }

            .form-buttons {
                flex-direction: column;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
            }

            .avaliacoes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<main class="avaliacoes-container">
    <h1><i class="fas fa-clipboard-list"></i> Avaliações dos Alunos</h1>

    <?php if (!empty($mensagem)): ?>
        <div class="mensagem <?php echo $sucesso ? 'sucesso' : 'erro'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <!-- Botão para Nova Avaliação -->
    <button type="button" class="btn-nova-avaliacao" onclick="toggleFormAvaliacao()">
        <i class="fas fa-plus-circle"></i> Nova Avaliação Física
    </button>

    <!-- Formulário de Nova Avaliação -->
    <div id="form-nova-avaliacao" class="form-nova-avaliacao">
        <h3><i class="fas fa-user-plus"></i> Registrar Nova Avaliação Física</h3>
        <form method="POST" id="formAvaliacao">
            <input type="hidden" name="acao" value="criar_avaliacao">
            
            <div class="form-group">
                <label for="aluno_id">Aluno: *</label>
                <select name="aluno_id" id="aluno_id" required>
                    <option value="">Selecione o aluno</option>
                    <?php foreach ($alunos as $aluno): ?>
                        <option value="<?php echo $aluno['id']; ?>">
                            <?php echo htmlspecialchars($aluno['nome'] . ' - ' . $aluno['email']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="data_avaliacao">Data da Avaliação: *</label>
                <input type="date" name="data_avaliacao" id="data_avaliacao" required max="<?php echo date('Y-m-d'); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="peso">Peso (kg):</label>
                    <input type="number" name="peso" id="peso" step="0.01" min="0" max="999.99" placeholder="Ex: 70.5">
                    <small style="color: #888; font-size: 0.85em; margin-top: 5px; display: block;">Máximo: 999.99 kg</small>
                </div>

                <div class="form-group">
                    <label for="altura">Altura (cm):</label>
                    <input type="number" name="altura" id="altura" step="0.1" min="50" max="250" placeholder="Ex: 175">
                    <small style="color: #888; font-size: 0.85em; margin-top: 5px; display: block;">Digite a altura em centímetros (entre 50 e 250 cm)</small>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="percentual_gordura">Percentual de Gordura (%):</label>
                    <input type="number" name="percentual_gordura" id="percentual_gordura" step="0.1" min="0" max="99.99" placeholder="Ex: 15.5">
                    <small style="color: #888; font-size: 0.85em; margin-top: 5px; display: block;">Máximo: 99.99%</small>
                </div>

                <div class="form-group">
                    <label for="massa_muscular">Massa Muscular (kg):</label>
                    <input type="number" name="massa_muscular" id="massa_muscular" step="0.01" min="0" max="999.99" placeholder="Ex: 55.0">
                    <small style="color: #888; font-size: 0.85em; margin-top: 5px; display: block;">Máximo: 999.99 kg</small>
                </div>
            </div>

            <h4 style="color: #8b5cf6; margin: 20px 0 15px 0; font-size: 1.1em;">Circunferências (cm)</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="circunferencia_braco">Braço:</label>
                    <input type="number" name="circunferencia_braco" id="circunferencia_braco" step="0.1" min="0" max="99.99" placeholder="Ex: 32.5">
                    <small style="color: #888; font-size: 0.85em; margin-top: 5px; display: block;">Máximo: 99.99 cm</small>
                </div>

                <div class="form-group">
                    <label for="circunferencia_cintura">Cintura:</label>
                    <input type="number" name="circunferencia_cintura" id="circunferencia_cintura" step="0.1" min="0" max="99.99" placeholder="Ex: 80.0">
                    <small style="color: #888; font-size: 0.85em; margin-top: 5px; display: block;">Máximo: 99.99 cm</small>
                </div>

                <div class="form-group">
                    <label for="circunferencia_quadril">Quadril:</label>
                    <input type="number" name="circunferencia_quadril" id="circunferencia_quadril" step="0.1" min="0" max="99.99" placeholder="Ex: 95.0">
                    <small style="color: #888; font-size: 0.85em; margin-top: 5px; display: block;">Máximo: 99.99 cm</small>
                </div>
            </div>

            <div class="form-group">
                <label for="observacoes">Observações:</label>
                <textarea name="observacoes" id="observacoes" placeholder="Observações sobre a avaliação física..."></textarea>
            </div>

            <div class="form-group">
                <label for="proxima_avaliacao">Próxima Avaliação:</label>
                <input type="date" name="proxima_avaliacao" id="proxima_avaliacao" min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Registrar Avaliação
                </button>
                <button type="button" class="btn-cancel" onclick="toggleFormAvaliacao()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </form>
    </div>

    <!-- Busca -->
    <div class="search-container">
        <form method="GET" action="<?php echo url('avaliacoes-professor'); ?>" class="search-box">
            <input type="text" name="busca" placeholder="Pesquisar por nome do aluno..." value="<?php echo htmlspecialchars($nomeFiltro ?? ''); ?>">
            <button type="submit">
                <i class="fas fa-search"></i> Buscar
            </button>
            <?php if ($nomeFiltro): ?>
                <a href="<?php echo url('avaliacoes-professor'); ?>" style="padding: 15px 20px; background: linear-gradient(135deg, #555, #666); color: white; border: none; border-radius: 12px; text-decoration: none; font-weight: 600; transition: all 0.3s;">
                    <i class="fas fa-times"></i> Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Lista de Alunos -->
    <?php if (empty($avaliacoesPorAluno)): ?>
        <div class="sem-avaliacoes">
            <i class="fas fa-clipboard-list"></i>
            <p><?php echo $nomeFiltro ? 'Nenhum aluno encontrado com esse nome.' : 'Nenhum aluno possui avaliação física registrada.'; ?></p>
        </div>
    <?php else: ?>
        <div class="alunos-lista">
            <?php foreach ($avaliacoesPorAluno as $aluno): ?>
                <div class="aluno-item">
                    <div class="aluno-header" onclick="toggleAvaliacoes(<?php echo $aluno['aluno_id']; ?>)">
                        <div>
                            <h3>
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($aluno['aluno_nome']); ?>
                                <span class="aluno-email"><?php echo htmlspecialchars($aluno['aluno_email']); ?></span>
                            </h3>
                        </div>
                        <button type="button" class="btn-ver-avaliacoes" id="btn-<?php echo $aluno['aluno_id']; ?>" onclick="event.stopPropagation(); toggleAvaliacoes(<?php echo $aluno['aluno_id']; ?>)">
                            <span>Ver Avaliações</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    
                    <div class="avaliacoes-aluno" id="avaliacoes-<?php echo $aluno['aluno_id']; ?>">
                        <div class="avaliacoes-grid">
                            <?php foreach ($aluno['avaliacoes'] as $avaliacao): 
                                // Calcular IMC se não estiver no banco mas tiver peso e altura
                                $imc = $avaliacao['imc'];
                                if (!$imc && $avaliacao['peso'] && $avaliacao['altura']) {
                                    $imc = $avaliacao['peso'] / ($avaliacao['altura'] * $avaliacao['altura']);
                                    $imc = round($imc, 2);
                                }
                                
                                $imcClass = '';
                                $imcLabel = '';
                                if ($imc) {
                                    if ($imc < 18.5) {
                                        $imcClass = 'imc-abaixo';
                                        $imcLabel = 'Abaixo do peso';
                                    } elseif ($imc < 25) {
                                        $imcClass = 'imc-normal';
                                        $imcLabel = 'Normal';
                                    } elseif ($imc < 30) {
                                        $imcClass = 'imc-sobrepeso';
                                        $imcLabel = 'Sobrepeso';
                                    } else {
                                        $imcClass = 'imc-obesidade';
                                        $imcLabel = 'Obesidade';
                                    }
                                }
                                $dataAval = new DateTime($avaliacao['data_avaliacao']);
                            ?>
                                <div class="avaliacao-card">
                                    <h3><i class="fas fa-calendar-alt"></i> Avaliação - <?php echo $dataAval->format('d/m/Y'); ?></h3>

                                    <?php if ($avaliacao['peso']): ?>
                                        <div class="info-row">
                                            <span class="info-label"><i class="fas fa-weight"></i> Peso:</span>
                                            <span class="info-value"><?php echo number_format($avaliacao['peso'], 2); ?> kg</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($avaliacao['altura']): ?>
                                        <div class="info-row">
                                            <span class="info-label"><i class="fas fa-ruler-vertical"></i> Altura:</span>
                                            <span class="info-value"><?php echo number_format($avaliacao['altura'] * 100, 0); ?> cm</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="info-row">
                                        <span class="info-label"><i class="fas fa-calculator"></i> IMC:</span>
                                        <span class="info-value">
                                            <?php if ($imc): ?>
                                                <?php echo number_format($imc, 2); ?>
                                                <span class="imc-badge <?php echo $imcClass; ?>"><?php echo $imcLabel; ?></span>
                                            <?php else: ?>
                                                <span style="color: #888; font-style: italic;">Não calculado</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <?php if ($avaliacao['percentual_gordura']): ?>
                                        <div class="info-row">
                                            <span class="info-label"><i class="fas fa-percentage"></i> % Gordura:</span>
                                            <span class="info-value"><?php echo number_format($avaliacao['percentual_gordura'], 2); ?>%</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($avaliacao['massa_muscular']): ?>
                                        <div class="info-row">
                                            <span class="info-label"><i class="fas fa-dumbbell"></i> Massa Muscular:</span>
                                            <span class="info-value"><?php echo number_format($avaliacao['massa_muscular'], 2); ?> kg</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($avaliacao['circunferencia_braco'] || $avaliacao['circunferencia_cintura'] || $avaliacao['circunferencia_quadril']): ?>
                                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(168, 59, 211, 0.2);">
                                            <strong style="color: #8b5cf6; display: block; margin-bottom: 10px;">Circunferências:</strong>
                                            <?php if ($avaliacao['circunferencia_braco']): ?>
                                                <div class="info-row">
                                                    <span class="info-label">Braço:</span>
                                                    <span class="info-value"><?php echo number_format($avaliacao['circunferencia_braco'], 2); ?> cm</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($avaliacao['circunferencia_cintura']): ?>
                                                <div class="info-row">
                                                    <span class="info-label">Cintura:</span>
                                                    <span class="info-value"><?php echo number_format($avaliacao['circunferencia_cintura'], 2); ?> cm</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($avaliacao['circunferencia_quadril']): ?>
                                                <div class="info-row">
                                                    <span class="info-label">Quadril:</span>
                                                    <span class="info-value"><?php echo number_format($avaliacao['circunferencia_quadril'], 2); ?> cm</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($avaliacao['observacoes']): ?>
                                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(168, 59, 211, 0.2);">
                                            <strong style="color: #8b5cf6; display: block; margin-bottom: 10px;">Observações:</strong>
                                            <p style="color: #cccccc; font-size: 0.9em; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($avaliacao['observacoes'])); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($avaliacao['proxima_avaliacao']): ?>
                                        <div class="data-avaliacao">
                                            <i class="fas fa-calendar-check"></i> Próxima Avaliação: <?php echo (new DateTime($avaliacao['proxima_avaliacao']))->format('d/m/Y'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>

<script>
function toggleFormAvaliacao() {
    const form = document.getElementById('form-nova-avaliacao');
    form.classList.toggle('show');
    
    if (form.classList.contains('show')) {
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function toggleAvaliacoes(alunoId) {
    const container = document.getElementById('avaliacoes-' + alunoId);
    const button = document.getElementById('btn-' + alunoId);
    
    if (container.classList.contains('show')) {
        container.classList.remove('show');
        button.classList.remove('expanded');
        button.querySelector('span').textContent = 'Ver Avaliações';
    } else {
        container.classList.add('show');
        button.classList.add('expanded');
        button.querySelector('span').textContent = 'Ocultar Avaliações';
    }
}

// Validação do formulário
document.getElementById('formAvaliacao').addEventListener('submit', function(e) {
    const alunoId = document.getElementById('aluno_id').value;
    const dataAvaliacao = document.getElementById('data_avaliacao').value;
    
    if (!alunoId || !dataAvaliacao) {
        return; // Deixa a validação HTML5 funcionar
    }
    
    // Verificar se já existe avaliação nesta data (validação básica)
    // A validação completa será feita no backend
    
    const alturaInput = document.getElementById('altura');
    const pesoInput = document.getElementById('peso');
    const altura = alturaInput.value ? parseFloat(alturaInput.value) : null;
    const peso = pesoInput.value ? parseFloat(pesoInput.value) : null;
    
    // Validar altura se preenchida (deve estar entre 50 e 250 cm)
    if (altura !== null && (altura < 50 || altura > 250)) {
        e.preventDefault();
        alert('A altura deve estar entre 50 e 250 centímetros!');
        alturaInput.focus();
        return false;
    }
    
    // Validar peso se preenchido (deve estar entre 0 e 999.99 kg)
    if (peso !== null && (peso < 0 || peso > 999.99)) {
        e.preventDefault();
        alert('O peso deve estar entre 0 e 999.99 quilogramas!');
        pesoInput.focus();
        return false;
    }
    
    // Validar percentual de gordura (máximo 99.99%)
    const percentualGordura = document.getElementById('percentual_gordura').value ? parseFloat(document.getElementById('percentual_gordura').value) : null;
    if (percentualGordura !== null && (percentualGordura < 0 || percentualGordura > 99.99)) {
        e.preventDefault();
        alert('O percentual de gordura deve estar entre 0 e 99.99%!');
        document.getElementById('percentual_gordura').focus();
        return false;
    }
    
    // Validar massa muscular (máximo 999.99 kg)
    const massaMuscular = document.getElementById('massa_muscular').value ? parseFloat(document.getElementById('massa_muscular').value) : null;
    if (massaMuscular !== null && (massaMuscular < 0 || massaMuscular > 999.99)) {
        e.preventDefault();
        alert('A massa muscular deve estar entre 0 e 999.99 kg!');
        document.getElementById('massa_muscular').focus();
        return false;
    }
    
    // Validar circunferências (máximo 99.99 cm cada)
    const circunferenciaBraco = document.getElementById('circunferencia_braco').value ? parseFloat(document.getElementById('circunferencia_braco').value) : null;
    if (circunferenciaBraco !== null && (circunferenciaBraco < 0 || circunferenciaBraco > 99.99)) {
        e.preventDefault();
        alert('A circunferência do braço deve estar entre 0 e 99.99 cm!');
        document.getElementById('circunferencia_braco').focus();
        return false;
    }
    
    const circunferenciaCintura = document.getElementById('circunferencia_cintura').value ? parseFloat(document.getElementById('circunferencia_cintura').value) : null;
    if (circunferenciaCintura !== null && (circunferenciaCintura < 0 || circunferenciaCintura > 99.99)) {
        e.preventDefault();
        alert('A circunferência da cintura deve estar entre 0 e 99.99 cm!');
        document.getElementById('circunferencia_cintura').focus();
        return false;
    }
    
    const circunferenciaQuadril = document.getElementById('circunferencia_quadril').value ? parseFloat(document.getElementById('circunferencia_quadril').value) : null;
    if (circunferenciaQuadril !== null && (circunferenciaQuadril < 0 || circunferenciaQuadril > 99.99)) {
        e.preventDefault();
        alert('A circunferência do quadril deve estar entre 0 e 99.99 cm!');
        document.getElementById('circunferencia_quadril').focus();
        return false;
    }
    
    // Se altura e peso estão presentes, validar IMC resultante
    if (altura !== null && peso !== null) {
        const alturaMetros = altura / 100;
        const imc = peso / (alturaMetros * alturaMetros);
        
        if (imc > 99.99) {
            e.preventDefault();
            alert('Os valores de peso e altura resultam em um IMC inválido (maior que 99.99). Verifique os dados inseridos.');
            return false;
        }
        
        if (imc < 0 || !isFinite(imc)) {
            e.preventDefault();
            alert('Os valores de peso e altura resultam em um IMC inválido. Verifique os dados inseridos.');
            return false;
        }
    }
});

// Fechar formulário após sucesso
<?php if ($sucesso): ?>
    setTimeout(function() {
        document.getElementById('form-nova-avaliacao').classList.remove('show');
        // Resetar formulário
        document.getElementById('formAvaliacao').reset();
    }, 2000);
<?php endif; ?>
</script>

</body>
</html>

