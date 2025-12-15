<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['id'])) {
    redirect('login');
}

require_once __DIR__ . '/../Controller/AvaliacaoFisicaController.php';

$controller = new AvaliacaoFisicaController();
$mensagem = "";
$sucesso = false;
$isAluno = ($_SESSION['tipo'] === 'Aluno');
$isFuncionario = ($_SESSION['tipo'] === 'Funcionario');

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'criar') {
        $alunoId = $isAluno ? $_SESSION['id'] : $_POST['aluno_id'];
        
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
            $_SESSION['avaliacao_sucesso'] = "Avaliação física registrada com sucesso!";
            redirect('avaliacao');
            exit;
        } else {
            $mensagem = $resultado;
        }
    }
}

// Verificar se há mensagem de sucesso na sessão
if (isset($_SESSION['avaliacao_sucesso'])) {
    $sucesso = true;
    $mensagem = $_SESSION['avaliacao_sucesso'];
    unset($_SESSION['avaliacao_sucesso']);
}

// Buscar avaliações
if ($isAluno) {
    $avaliacoes = $controller->listarPorAluno($_SESSION['id']);
    $ultimaAvaliacao = $controller->buscarUltimaAvaliacao($_SESSION['id']);
} else {
    $avaliacoes = [];
    $ultimaAvaliacao = null;
}

// Buscar alunos para funcionários
$alunos = [];
if ($isFuncionario) {
    require_once __DIR__ . '/../Model/CadastroDAO.php';
    $cadastroDAO = new CadastroDAO();
    $todosCadastros = $cadastroDAO->listarTodos();
    $alunos = array_filter($todosCadastros, function($c) {
        return $c['tipo'] === 'Aluno';
    });
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Avaliação Física</title>
    <link rel="stylesheet" href="<?php echo asset('header-footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('agendaraulas.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .avaliacao-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-avaliacao {
            background-color: rgba(26, 26, 26, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            margin-bottom: 30px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
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
        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="date"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8b5cf6;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 10px rgba(168, 59, 211, 0.3);
        }
        .form-group input[type="text"]::placeholder,
        .form-group input[type="number"]::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23a83bd3' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }
        .form-group select option {
            background: #1a1a1a;
            color: #ffffff;
            padding: 10px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .avaliacoes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .avaliacao-card {
            background: rgba(26, 26, 26, 0.95);
            padding: 20px;
            border-radius: 15px;
            border-left: 4px solid #8b5cf6;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .avaliacao-card:hover {
            transform: translateY(-5px);
        }
        .avaliacao-card h3 {
            color: #8b5cf6;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        .avaliacao-card p {
            margin: 8px 0;
            color: #cccccc;
        }
        .imc-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            margin-left: 10px;
            font-size: 0.9em;
        }
        .imc-normal { background: #4caf50; color: white; }
        .imc-sobrepeso { background: #ff9800; color: white; }
        .imc-obesidade { background: #f44336; color: white; }
        .btn-agendar {
            background: linear-gradient(90deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-agendar:hover {
            background: linear-gradient(90deg, #7c3aed, #8b5cf6);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(168, 59, 211, 0.4);
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<main class="avaliacao-container">
    <h1><i class="fas fa-clipboard-check"></i> Avaliação Física</h1>

    <?php if (!empty($mensagem)): ?>
        <div class="mensagem <?php echo $sucesso ? 'sucesso' : 'erro'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <!-- FORMULÁRIO DE AVALIAÇÃO -->
    <?php if ($isAluno || $isFuncionario): ?>
    <section class="agendar-section">
        <h2><i class="fas fa-plus-circle"></i> <?php echo $isAluno ? 'Nova Avaliação' : 'Registrar Avaliação'; ?></h2>
        <form method="POST" class="form-avaliacao">
            <input type="hidden" name="acao" value="criar">

            <?php if ($isFuncionario): ?>
            <div class="form-group">
                <label class="field-label">Aluno:</label>
                <select name="aluno_id" required>
                    <option value="">Selecione o aluno</option>
                    <?php foreach ($alunos as $aluno): ?>
                        <option value="<?php echo $aluno['id']; ?>">
                            <?php echo htmlspecialchars($aluno['nome']); ?> - <?php echo htmlspecialchars($aluno['email']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="field-label">Data da Avaliação:</label>
                <input type="date" name="data_avaliacao" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="field-label">Peso (kg):</label>
                    <input type="number" name="peso" step="0.01" min="0" placeholder="Ex: 75.5">
                </div>
                <div class="form-group">
                    <label class="field-label">Altura (cm):</label>
                    <input type="number" name="altura" step="1" min="0" max="300" placeholder="Ex: 175">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="field-label">Percentual de Gordura (%):</label>
                    <input type="number" name="percentual_gordura" step="0.01" min="0" max="100" placeholder="Ex: 15.5">
                </div>
                <div class="form-group">
                    <label class="field-label">Massa Muscular (kg):</label>
                    <input type="number" name="massa_muscular" step="0.01" min="0" placeholder="Ex: 60.0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="field-label">Circunferência Braço (cm):</label>
                    <input type="number" name="circunferencia_braco" step="0.01" min="0" placeholder="Ex: 35.0">
                </div>
                <div class="form-group">
                    <label class="field-label">Circunferência Cintura (cm):</label>
                    <input type="number" name="circunferencia_cintura" step="0.01" min="0" placeholder="Ex: 85.0">
                </div>
                <div class="form-group">
                    <label class="field-label">Circunferência Quadril (cm):</label>
                    <input type="number" name="circunferencia_quadril" step="0.01" min="0" placeholder="Ex: 95.0">
                </div>
            </div>

            <div class="form-group">
                <label class="field-label">Observações:</label>
                <textarea name="observacoes" rows="4" placeholder="Observações sobre a avaliação..."></textarea>
            </div>

            <div class="form-group">
                <label class="field-label">Próxima Avaliação:</label>
                <input type="date" name="proxima_avaliacao">
            </div>

            <button type="submit" class="btn-agendar">
                <i class="fas fa-check"></i> Registrar Avaliação
            </button>
        </form>
    </section>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HISTORICO DE AVALIACOES (CARDS) -->
    <!-- ============================================ -->
    <!-- Esta seção exibe as avaliações físicas do aluno em formato de cards -->
    <?php if ($isAluno): ?>
    <section class="agendamentos-section">
        <h2><i class="fas fa-history"></i> Histórico de Avaliações</h2>
        
        <!-- Se não houver avaliações, exibe mensagem -->
        <?php if (empty($avaliacoes)): ?>
            <div class="sem-agendamentos">
                <i class="fas fa-clipboard-list"></i>
                <p>Você ainda não possui avaliações físicas registradas.</p>
            </div>
        <?php else: ?>
        <!-- Grid de cards: exibe as avaliações em formato de cartões -->
        <div class="avaliacoes-grid">
            <?php foreach ($avaliacoes as $avaliacao): 
                // ===== PREPARACAO DOS DADOS =====
                // Converte a data da avaliação para objeto DateTime para facilitar formatação
                $dataAval = new DateTime($avaliacao['data_avaliacao']);
                
                // Pega o IMC da avaliação
                $imc = $avaliacao['imc'];
                
                // Define a classe CSS do badge de IMC baseado no valor
                // As classes definem a cor do badge (verde, laranja, vermelho)
                $imcClass = '';
                if ($imc) {
                    if ($imc < 18.5) $imcClass = 'imc-normal'; // Abaixo do peso
                    elseif ($imc < 25) $imcClass = 'imc-normal'; // Normal
                    elseif ($imc < 30) $imcClass = 'imc-sobrepeso'; // Sobrepeso
                    else $imcClass = 'imc-obesidade'; // Obesidade
                }
            ?>
                <!-- ===== CARD DE AVALIACAO ===== -->
                <!-- Cada card representa uma avaliação física -->
                <div class="avaliacao-card">
                    <!-- Data da avaliação formatada (dia/mês/ano) -->
                    <h3><?php echo $dataAval->format('d/m/Y'); ?></h3>
                    
                    <!-- Peso: só exibe se tiver valor -->
                    <?php if ($avaliacao['peso']): ?>
                        <!-- number_format() formata o número com 2 casas decimais -->
                        <p><strong>Peso:</strong> <?php echo number_format($avaliacao['peso'], 2); ?> kg</p>
                    <?php endif; ?>
                    
                    <!-- Altura: só exibe se tiver valor -->
                    <?php if ($avaliacao['altura']): ?>
                        <!-- Multiplica por 100 para converter de metros para centímetros -->
                        <!-- number_format() com 0 casas decimais para exibir como inteiro -->
                        <p><strong>Altura:</strong> <?php echo number_format($avaliacao['altura'] * 100, 0); ?> cm</p>
                    <?php endif; ?>
                    
                    <!-- IMC: só exibe se tiver valor -->
                    <?php if ($imc): ?>
                        <p><strong>IMC:</strong> <?php echo number_format($imc, 2); ?>
                            <!-- Badge colorido que indica a classificação do IMC -->
                            <span class="imc-badge <?php echo $imcClass; ?>">
                                <?php 
                                // Define o texto do badge baseado no valor do IMC
                                if ($imc < 18.5) echo 'Abaixo do peso';
                                elseif ($imc < 25) echo 'Normal';
                                elseif ($imc < 30) echo 'Sobrepeso';
                                else echo 'Obesidade';
                                ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    
                    <!-- Percentual de gordura: só exibe se tiver valor -->
                    <?php if ($avaliacao['percentual_gordura']): ?>
                        <p><strong>% Gordura:</strong> <?php echo number_format($avaliacao['percentual_gordura'], 2); ?>%</p>
                    <?php endif; ?>
                    
                    <!-- Massa muscular: só exibe se tiver valor -->
                    <?php if ($avaliacao['massa_muscular']): ?>
                        <p><strong>Massa Muscular:</strong> <?php echo number_format($avaliacao['massa_muscular'], 2); ?> kg</p>
                    <?php endif; ?>
                    
                    <!-- Observações: só exibe se tiver valor -->
                    <?php if ($avaliacao['observacoes']): ?>
                        <!-- htmlspecialchars() protege contra XSS (injeção de código HTML) -->
                        <p><strong>Observações:</strong> <?php echo htmlspecialchars($avaliacao['observacoes']); ?></p>
                    <?php endif; ?>
                    
                    <!-- Próxima avaliação: só exibe se tiver valor -->
                    <?php if ($avaliacao['proxima_avaliacao']): ?>
                        <?php 
                        // Converte a data da próxima avaliação para objeto DateTime
                        $proxima = new DateTime($avaliacao['proxima_avaliacao']);
                        $hoje = new DateTime(); // Data atual
                        // Verifica se a data da próxima avaliação já passou (está vencida)
                        $vencida = $proxima < $hoje;
                        ?>
                        <p><strong>Próxima Avaliação:</strong> 
                            <!-- Muda a cor baseado se está vencida ou não -->
                            <!-- Vermelho se vencida, verde se ainda não venceu -->
                            <span style="color: <?php echo $vencida ? '#ff5555' : '#4caf50'; ?>;">
                                <?php echo $proxima->format('d/m/Y'); ?>
                                <!-- Mostra ícone de alerta se estiver vencida -->
                                <?php if ($vencida): ?>
                                    <i class="fas fa-exclamation-triangle"></i> Vencida
                                <?php endif; ?>
                            </span>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>

