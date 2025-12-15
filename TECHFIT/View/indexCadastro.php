<?php
// ============================================
// INICIALIZACAO E CONFIGURACAO
// ============================================
// Inicia a sessão PHP se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui arquivos necessários
require_once __DIR__ . '/helpers.php'; // Funções auxiliares (redirect, url, asset, etc)
require_once __DIR__ . '/../Controller/CadastroController.php'; // Controller que processa o cadastro

// ============================================
// VERIFICACAO DE CONTEXTO (ADMIN OU PUBLICO)
// ============================================
// Verifica se quem está acessando é um administrador (Funcionário logado)
// Se for admin, pode cadastrar Professores e Funcionários
// Se for público, só pode cadastrar Alunos
$isAdmin = isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'Funcionario';

// ============================================
// VERIFICACAO SE E O PRIMEIRO ADMIN
// ============================================
// Verifica quantos funcionários existem no banco de dados
// Se não existir nenhum, significa que é o primeiro admin sendo criado
// O primeiro admin pode ter campos opcionais (nome, CPF, telefone, data de nascimento)
require_once __DIR__ . '/../Model/Connection.php';
$conn = Connection::getInstance();
// Conta quantos funcionários existem
$stmtFuncionarios = $conn->query("SELECT COUNT(*) FROM Cadastros WHERE tipo = 'Funcionario'");
$totalFuncionarios = $stmtFuncionarios->fetchColumn();
// Se não existir nenhum funcionário, é o primeiro admin
$isPrimeiroAdmin = ($totalFuncionarios == 0);

// ============================================
// PROCESSAMENTO DO FORMULARIO
// ============================================
// Cria instância do controller que processa o cadastro
$controller = new CadastroController();
// Pega a ação enviada pelo formulário (geralmente 'criar')
$acao = $_POST['acao'] ?? '';
// Variável para armazenar mensagens de erro ou sucesso
$mensagem = "";

// Verifica se o formulário foi enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Se a ação for 'criar', processa o cadastro
    if ($acao === 'criar') {
        // ===== VALIDACAO DO TIPO DE CONTA =====
        // Verifica se o tipo de conta é permitido para o contexto atual
        $tipoPermitido = false;
        
        if ($isAdmin) {
            // Se for admin, só pode cadastrar Professor ou Funcionário
            $tipoPermitido = in_array($_POST['tipo'], ['Professor', 'Funcionario']);
        } else {
            // Se for público, só pode cadastrar Aluno
            $tipoPermitido = $_POST['tipo'] === 'Aluno';
        }
        
        // Se o tipo não for permitido, exibe mensagem de erro
        if (!$tipoPermitido) {
            $mensagem = "<p class='erro'>Tipo de conta não permitido para este contexto!</p>";
        } else {
            // ===== CAPTURA DE DADOS DO FORMULARIO =====
            
            // Captura as modalidades se for Professor
            // Professores podem lecionar Boxe e/ou Muay Thai
            $modalidades = [];
            if (isset($_POST['tipo']) && $_POST['tipo'] === 'Professor' && isset($_POST['modalidades'])) {
                $modalidades = $_POST['modalidades']; // Array com as modalidades selecionadas
            }
            
            // Captura os dados pessoais do formulário
            // trim() remove espaços em branco no início e fim do nome
            $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
            $cpf = isset($_POST['cpf']) ? $_POST['cpf'] : '';
            $telefone = isset($_POST['telefone']) ? $_POST['telefone'] : '';
            $datanascimento = isset($_POST['datanascimento']) ? $_POST['datanascimento'] : '';
            
            // ===== TRATAMENTO ESPECIAL PARA PRIMEIRO ADMIN =====
            // Se for o primeiro admin (Funcionário) sendo criado, os campos pessoais são opcionais
            // Isso permite criar o primeiro admin apenas com email e senha
            if ($isPrimeiroAdmin && $_POST['tipo'] === 'Funcionario') {
                // Se os campos estiverem vazios, define como null (opcional)
                $nome = $nome ?: null;
                $cpf = $cpf ?: null;
                $telefone = $telefone ?: null;
                $datanascimento = $datanascimento ?: null;
            }
            
            // ===== CHAMA O CONTROLLER PARA CRIAR O CADASTRO =====
            // Passa todos os dados para o controller processar
            $resultado = $controller->criar(
                $_POST['tipo'],           // Tipo: Aluno, Professor ou Funcionário
                $nome,                    // Nome completo
                $_POST['email'],          // Email (único no sistema)
                $_POST['senha'],          // Senha
                $_POST['confirmarsenha'], // Confirmação da senha
                $cpf,                     // CPF
                $telefone,                // Telefone
                $datanascimento,          // Data de nascimento
                $modalidades,             // Array de modalidades (se for Professor)
                $isAdmin                  // Contexto: se é admin cadastrando ou público
            );

            // ===== TRATAMENTO DO RESULTADO =====
            // Se o cadastro foi criado com sucesso (retorna true)
            if ($resultado === true) {
                // Redireciona o usuário
                if ($isAdmin) {
                    // Se for admin, redireciona para o painel admin
                    redirect('admin');
                    exit; // Para a execução do script
                } else {
                    // Se for público, redireciona para a página de login
                    redirect('login');
                    exit; // Para a execução do script
                }
            } else {
                // Se houver erro, armazena a mensagem de erro para exibir na tela
                // $resultado contém a mensagem de erro retornada pelo controller
                $mensagem = "<p class='erro'>$resultado</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?php echo asset('header-footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('cadastro.css'); ?>">
    <title>Cadastro - TechFit</title>
</head>

<body>

<!-- HEADER -->
<header class="navbar">
    <div class="logo">
        <img src="<?php echo asset('img/logotechfit-removebg-preview.png'); ?>" alt="Logo TechFit">
        <div class="logo-text">
            <strong>TECH<span class="fit">FIT</span></strong>
            <span class="subtext">FUTURE FITNESS</span>
        </div>
    </div>

    <nav class="menu">
        <div class="utility-links">
            <a href="<?php echo url('login'); ?>" class="login-btn">LOGIN</a>
            <a href="<?php echo url('cadastro'); ?>" class="register-btn">CADASTRO</a>
        </div>
    </nav>
</header>

<!-- CONTEÚDO -->

<div class="Container">

    <h1>CADASTRE-SE NA TECHFIT</h1>

    <div class="Cadastrar">
        <h2>Criar Conta</h2>

        <!-- ============================================ -->
        <!-- INDICADORES DE PROGRESSO (ETAPAS 1 E 2) -->
        <!-- ============================================ -->
        <!-- Mostra visualmente em qual etapa o usuário está -->
        <!-- Círculo roxo = etapa ativa | Círculo cinza = etapa inativa -->
        <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 25px;">
            <!-- Indicador da Etapa 1: Dados Pessoais -->
            <!-- Inicialmente está ativo (roxo) e com sombra -->
            <div id="step-indicator-1" style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);">1</div>
            <!-- Barra conectora entre os dois indicadores -->
            <div style="width: 80px; height: 5px; background: linear-gradient(90deg, #8b5cf6, #9333ea); border-radius: 5px; margin-top: 0;"></div>
            <!-- Indicador da Etapa 2: Dados de Acesso -->
            <!-- Inicialmente está inativo (cinza) -->
            <div id="step-indicator-2" style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #5d5d5d, #6d6d6d); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; transition: all 0.3s;">2</div>
        </div>

        <!-- ============================================ -->
        <!-- FORMULARIO DE CADASTRO -->
        <!-- ============================================ -->
        <!-- method="POST" envia os dados via POST para o servidor -->
        <!-- id="cadastroForm" é usado pelo JavaScript para validação antes de enviar -->
        <form method="POST" id="cadastroForm">
            <!-- Campo oculto que identifica a ação como "criar" -->
            <input type="hidden" name="acao" value="criar">

            <!-- ============================================ -->
            <!-- ETAPA 1: DADOS PESSOAIS -->
            <!-- ============================================ -->
            <!-- Esta etapa coleta: tipo de conta, nome, CPF, telefone e modalidades (se Professor) -->
            <!-- Se for o primeiro admin, esta etapa fica oculta (display: none) -->
            <div id="etapa1" class="form-etapa" style="<?php echo ($isPrimeiroAdmin && $isAdmin) ? 'display: none;' : ''; ?>">
                <h3>Dados Pessoais</h3>

                <?php if ($isAdmin): ?>
                    <!-- ===== CAMPO DE TIPO (APENAS PARA ADMIN) ===== -->
                    <!-- Admin pode escolher entre Professor ou Funcionário -->
                    <!-- onchange="toggleModalidades()" mostra/esconde modalidades quando muda o tipo -->
                    <label class="field-label">Cadastrar como:</label>
                    <select name="tipo" id="tipo" required onchange="toggleModalidades()">
                        <option disabled selected hidden>Selecione o tipo</option>
                        <option value="Professor">Professor</option>
                        <option value="Funcionario">Funcionário</option>
                    </select>
                <?php else: ?>
                    <!-- ===== CAMPO DE TIPO (APENAS PARA PUBLICO) ===== -->
                    <!-- Público só pode cadastrar Aluno, então o campo fica oculto com valor fixo -->
                    <input type="hidden" name="tipo" id="tipo" value="Aluno">
                <?php endif; ?>

                <!-- ===== CAMPO DE MODALIDADES (APENAS PARA PROFESSOR) ===== -->
                <!-- Este campo só aparece quando o tipo selecionado é "Professor" -->
                <!-- Inicialmente está oculto (display: none) e é mostrado via JavaScript -->
                <div id="modalidades-container" style="display: none;">
                    <label class="field-label">Modalidades que você leciona:</label>
                    <div>
                        <label>
                            <input type="checkbox" name="modalidades[]" value="Boxe">
                            <span>Boxe</span>
                        </label>
                        <label>
                            <input type="checkbox" name="modalidades[]" value="Muay Thai">
                            <span>Muay Thai</span>
                        </label>
                    </div>
                </div>

                <!-- ===== CAMPO NOME COMPLETO ===== -->
                <label class="field-label">Nome Completo:</label>
                <input type="text" name="nome" id="nome" placeholder="Digite seu nome" required>

                <!-- ===== CAMPO CPF ===== -->
                <!-- maxlength="14" limita a 14 caracteres (incluindo pontos e traço) -->
                <!-- A formatação automática é feita via JavaScript -->
                <label class="field-label">CPF:</label>
                <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" maxlength="14" required>

                <!-- ===== CAMPO TELEFONE ===== -->
                <!-- maxlength="15" limita a 15 caracteres (incluindo parênteses, espaço e traço) -->
                <!-- A formatação automática é feita via JavaScript -->
                <label class="field-label">Telefone:</label>
                <input type="tel" name="telefone" id="telefone" placeholder="(00) 00000-0000" maxlength="15" required>

                <!-- ===== BOTAO AVANCAR (ETAPA 1) ===== -->
                <!-- type="button" evita que o formulário seja enviado ao clicar -->
                <!-- onclick="avancarEtapa()" chama a função JavaScript que valida e avança -->
                <div style="display: flex; gap: 10px; margin: 20px auto 0; width: 60%; min-width: 400px; max-width: 100%; justify-content: center;">
                    <button type="button" onclick="avancarEtapa()" style="flex: 1; padding: 14px; background: linear-gradient(135deg, #7c3aed, #8b5cf6, #9333ea); color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 600; box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(139, 92, 246, 0.6)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(139, 92, 246, 0.4)'">
                        Avançar →
                    </button>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- ETAPA 2: DADOS DE ACESSO -->
            <!-- ============================================ -->
            <!-- Esta etapa coleta: data de nascimento, email, senha e confirmação de senha -->
            <!-- Se for o primeiro admin, esta etapa aparece direto (display: flex) -->
            <div id="etapa2" class="form-etapa" style="<?php echo ($isPrimeiroAdmin && $isAdmin) ? 'display: flex;' : 'display: none;'; ?>">
                <h3>Dados de Acesso</h3>

                <?php if (!$isPrimeiroAdmin || !$isAdmin): ?>
                    <!-- ===== CAMPO DATA DE NASCIMENTO ===== -->
                    <!-- Só aparece se NÃO for o primeiro admin -->
                    <!-- type="date" mostra um seletor de data no navegador -->
                    <label class="field-label">Data de Nascimento:</label>
                    <input type="date" name="datanascimento" id="datanascimento" required>
                <?php else: ?>
                    <!-- ===== CAMPOS OCULTOS PARA PRIMEIRO ADMIN ===== -->
                    <!-- Se for o primeiro admin, os campos pessoais ficam ocultos e vazios -->
                    <!-- Isso permite criar o primeiro admin apenas com email e senha -->
                    <input type="hidden" name="tipo" id="tipo" value="Funcionario">
                    <input type="hidden" name="nome" id="nome" value="">
                    <input type="hidden" name="cpf" id="cpf" value="">
                    <input type="hidden" name="telefone" id="telefone" value="">
                    <input type="hidden" name="datanascimento" id="datanascimento" value="">
                <?php endif; ?>

                <!-- ===== CAMPO EMAIL ===== -->
                <!-- type="email" valida se o formato é de email válido -->
                <!-- Email deve ser único no sistema -->
                <label class="field-label">Email:</label>
                <input type="email" name="email" id="email" placeholder="Digite seu email" required>

                <!-- ===== CAMPO SENHA ===== -->
                <!-- type="password" esconde os caracteres digitados -->
                <!-- minlength="6" exige no mínimo 6 caracteres -->
                <label class="field-label">Senha (mínimo 6 caracteres):</label>
                <input type="password" name="senha" id="senha" placeholder="Digite sua senha" minlength="6" required>

                <!-- ===== CAMPO CONFIRMAR SENHA ===== -->
                <!-- O usuário deve digitar a mesma senha novamente para confirmar -->
                <!-- A validação de igualdade é feita via JavaScript antes de enviar -->
                <label class="field-label">Confirmar Senha:</label>
                <input type="password" name="confirmarsenha" id="confirmarsenha" placeholder="Confirme sua senha" required>

                <!-- ===== BOTOES DA ETAPA 2 ===== -->
                <div style="display: flex; gap: 10px; margin: 20px auto 0; width: 60%; min-width: 400px; max-width: 100%; justify-content: center;">
                    <?php if (!$isPrimeiroAdmin || !$isAdmin): ?>
                        <!-- Botão Voltar: só aparece se NÃO for o primeiro admin -->
                        <!-- onclick="voltarEtapa()" retorna para a etapa 1 -->
                        <button type="button" onclick="voltarEtapa()" style="flex: 1; padding: 14px; background: linear-gradient(135deg, #6d6d6d, #666, #777); color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 600; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0, 0, 0, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(0, 0, 0, 0.3)'">
                            ← Voltar
                        </button>
                    <?php endif; ?>
                    <!-- Botão Cadastrar: envia o formulário para o servidor -->
                    <!-- type="submit" envia o formulário quando clicado -->
                    <!-- A validação JavaScript é executada antes do envio -->
                    <button type="submit" style="flex: 1; padding: 14px; background: linear-gradient(135deg, #7c3aed, #8b5cf6, #9333ea); color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 600; box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(139, 92, 246, 0.6)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 15px rgba(139, 92, 246, 0.4)'">
                        Cadastrar
                    </button>
                </div>
            </div>
        </form>

        <p style="text-align:center;margin-top:12px;">
            <a href="<?php echo url('login'); ?>" style="color:#8b5cf6;text-decoration:none;">
                Já tenho uma conta
            </a>
        </p>

        <div class="mensagem"><?= $mensagem ?></div>
    </div>

    <!-- Container para mensagens JavaScript -->
    <div id="mensagem-js" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 10000; max-width: 90%; width: 500px;"></div>

    <script>
        // ============================================
        // FUNCAO: EXIBIR MENSAGENS DE ERRO/SUCESSO
        // ============================================
        // Esta função exibe mensagens na tela (substitui o alert padrão do navegador)
        // Parâmetros:
        //   - texto: a mensagem que será exibida
        //   - tipo: tipo da mensagem ('erro' ou 'sucesso'), padrão é 'erro'
        function exibirMensagem(texto, tipo = 'erro') {
            // Pega o elemento HTML onde a mensagem será exibida
            const container = document.getElementById('mensagem-js');
            // Se o elemento não existir, para a execução
            if (!container) return;
            
            // Define a classe CSS da mensagem (para estilização diferente de erro/sucesso)
            container.className = 'mensagem ' + tipo;
            // Define o texto da mensagem
            container.textContent = texto;
            // Mostra o container (display: block)
            container.style.display = 'block';
            // Define opacidade para 1 (totalmente visível)
            container.style.opacity = '1';
            
            // Faz o scroll da página até o topo para que o usuário veja a mensagem
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Remove a mensagem automaticamente após 5 segundos
            setTimeout(function() {
                // Primeiro, diminui a opacidade para 0 (fade out)
                container.style.opacity = '0';
                // Depois de 300ms (tempo da animação), esconde completamente
                setTimeout(function() {
                    container.style.display = 'none';
                }, 300);
            }, 5000); // 5000ms = 5 segundos
        }

        // ============================================
        // SISTEMA DE ETAPAS DO CADASTRO
        // ============================================
        // Variável que armazena qual etapa está ativa (1 ou 2)
        let etapaAtual = 1;

        // ============================================
        // FUNCAO: AVANCAR PARA ETAPA 2
        // ============================================
        // Esta função é chamada quando o usuário clica no botão "Avançar →"
        function avancarEtapa() {
            // ===== VALIDACAO DOS CAMPOS DA ETAPA 1 =====
            
            // Pega os valores digitados nos campos da etapa 1
            const tipo = document.getElementById('tipo').value;
            const nome = document.getElementById('nome').value;
            const cpf = document.getElementById('cpf').value;
            const telefone = document.getElementById('telefone').value;

            // Validação 1: Verifica se o tipo de conta foi selecionado
            if (!tipo || tipo === '') {
                exibirMensagem('Selecione o tipo de conta!', 'erro');
                return; // Para a execução da função se não passar na validação
            }

            // Validação 2: Verifica se o nome foi preenchido (remove espaços em branco)
            if (!nome || nome.trim() === '') {
                exibirMensagem('Preencha o nome completo!', 'erro');
                return;
            }

            // Validação 3: Verifica se o CPF tem 11 dígitos
            // Remove todos os caracteres não numéricos (pontos, traços) para contar apenas os dígitos
            const cpfLimpo = cpf.replace(/\D/g, '');
            if (cpfLimpo.length !== 11) {
                exibirMensagem('CPF deve ter 11 dígitos!', 'erro');
                return;
            }

            // Validação 4: Verifica se o telefone tem 10 ou 11 dígitos
            // Remove todos os caracteres não numéricos (parênteses, traços, espaços)
            const telefoneLimpo = telefone.replace(/\D/g, '');
            if (telefoneLimpo.length < 10 || telefoneLimpo.length > 11) {
                exibirMensagem('Telefone inválido!', 'erro');
                return;
            }

            // Validação 5: Se for Professor, verifica se selecionou pelo menos uma modalidade
            if (tipo === 'Professor') {
                // Busca todos os checkboxes de modalidades que estão marcados
                const checkboxes = document.querySelectorAll('input[name="modalidades[]"]:checked');
                if (checkboxes.length === 0) {
                    exibirMensagem('Professor deve selecionar pelo menos uma modalidade!', 'erro');
                    return;
                }
            }

            // ===== SE TODAS AS VALIDACOES PASSARAM, AVANCA PARA ETAPA 2 =====
            
            // Pega referência dos elementos HTML das duas etapas
            const etapa1 = document.getElementById('etapa1');
            const etapa2 = document.getElementById('etapa2');
            
            // Esconde a etapa 1 (display: none)
            etapa1.style.display = 'none';
            // Mostra a etapa 2 com layout flexbox
            etapa2.style.display = 'flex';
            etapa2.style.flexDirection = 'column'; // Coloca os elementos em coluna
            etapa2.style.alignItems = 'center'; // Centraliza os elementos
            
            // Atualiza a variável que controla qual etapa está ativa
            etapaAtual = 2;
            
            // ===== ATUALIZA OS INDICADORES VISUAIS (CIRCULOS NUMERADOS) =====
            
            // Indicador 1: muda de roxo (ativo) para cinza (inativo)
            document.getElementById('step-indicator-1').style.background = 'linear-gradient(135deg, #5d5d5d, #6d6d6d)';
            
            // Indicador 2: muda de cinza (inativo) para roxo (ativo) e adiciona sombra
            document.getElementById('step-indicator-2').style.background = 'linear-gradient(135deg, #7c3aed, #8b5cf6)';
            document.getElementById('step-indicator-2').style.boxShadow = '0 4px 15px rgba(139, 92, 246, 0.4)';
        }

        // ============================================
        // FUNCAO: VOLTAR PARA ETAPA 1
        // ============================================
        // Esta função é chamada quando o usuário clica no botão "← Voltar"
        function voltarEtapa() {
            // Pega referência dos elementos HTML das duas etapas
            const etapa1 = document.getElementById('etapa1');
            const etapa2 = document.getElementById('etapa2');
            
            // Esconde a etapa 2 (display: none)
            etapa2.style.display = 'none';
            // Mostra a etapa 1 com layout flexbox
            etapa1.style.display = 'flex';
            etapa1.style.flexDirection = 'column'; // Coloca os elementos em coluna
            etapa1.style.alignItems = 'center'; // Centraliza os elementos
            
            // Atualiza a variável que controla qual etapa está ativa
            etapaAtual = 1;
            
            // ===== ATUALIZA OS INDICADORES VISUAIS (CIRCULOS NUMERADOS) =====
            
            // Indicador 1: muda de cinza (inativo) para roxo (ativo) e adiciona sombra
            document.getElementById('step-indicator-1').style.background = 'linear-gradient(135deg, #7c3aed, #8b5cf6)';
            document.getElementById('step-indicator-1').style.boxShadow = '0 4px 15px rgba(139, 92, 246, 0.4)';
            
            // Indicador 2: muda de roxo (ativo) para cinza (inativo) e remove sombra
            document.getElementById('step-indicator-2').style.background = 'linear-gradient(135deg, #5d5d5d, #6d6d6d)';
            document.getElementById('step-indicator-2').style.boxShadow = 'none';
        }

        // ============================================
        // FUNCAO: MOSTRAR/ESCONDER CAMPO DE MODALIDADES
        // ============================================
        // Esta função mostra ou esconde o campo de modalidades baseado no tipo de usuário
        // Só aparece se o tipo for "Professor"
        function toggleModalidades() {
            // Pega o elemento que contém o tipo de usuário (pode ser um select ou input hidden)
            const tipoElement = document.getElementById('tipo');
            // Pega o container que contém os checkboxes de modalidades
            const modalidadesContainer = document.getElementById('modalidades-container');
            
            // Se o elemento de tipo não existir, para a execução
            if (!tipoElement) return;
            
            // Se for um input hidden, significa que é um Aluno (cadastro público)
            // Alunos não têm modalidades, então esconde o campo
            if (tipoElement.tagName === 'INPUT' && tipoElement.type === 'hidden') {
                modalidadesContainer.style.display = 'none';
                modalidadesContainer.classList.remove('show');
                return; // Para a execução aqui
            }
            
            // Se for um select (admin cadastrando), verifica o valor selecionado
            const tipo = tipoElement.value;
            
            // Se o tipo for "Professor", mostra o campo de modalidades
            if (tipo === 'Professor') {
                modalidadesContainer.style.display = 'block';
                modalidadesContainer.classList.add('show');
            } 
            // Se não for Professor (Funcionário ou outro), esconde o campo
            else {
                modalidadesContainer.style.display = 'none';
                modalidadesContainer.classList.remove('show');
                
                // Desmarca todos os checkboxes de modalidades quando não for Professor
                // Isso evita que fiquem marcados se o usuário mudar de Professor para outro tipo
                const checkboxes = modalidadesContainer.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => {
                    cb.checked = false;
                });
            }
        }

        // ============================================
        // EXECUTAR AO CARREGAR A PAGINA
        // ============================================
        // Quando a página termina de carregar, executa a função toggleModalidades()
        // Isso garante que o campo de modalidades apareça/esconda corretamente no início
        document.addEventListener('DOMContentLoaded', function() {
            toggleModalidades();
        });

        // ============================================
        // VALIDACAO ANTES DE ENVIAR O FORMULARIO
        // ============================================
        // Esta função é executada quando o usuário clica no botão "Cadastrar"
        // Ela valida os dados antes de enviar o formulário para o servidor
        document.getElementById('cadastroForm').addEventListener('submit', function(e) {
            // Pega o tipo de usuário (pode ser select ou input hidden)
            const tipoElement = document.getElementById('tipo');
            // Verifica se é input hidden ou select e pega o valor
            const tipo = tipoElement ? (tipoElement.type === 'hidden' ? tipoElement.value : tipoElement.value) : '';
            // Pega os valores das senhas
            const senha = document.getElementById('senha').value;
            const confirmarsenha = document.getElementById('confirmarsenha').value;

            // Validação 1: Verifica se a senha tem no mínimo 6 caracteres
            if (senha.length < 6) {
                e.preventDefault(); // Impede o envio do formulário
                exibirMensagem('A senha deve ter no mínimo 6 caracteres!', 'erro');
                return false; // Retorna false para não enviar
            }

            // Validação 2: Verifica se as duas senhas são iguais
            if (senha !== confirmarsenha) {
                e.preventDefault(); // Impede o envio do formulário
                exibirMensagem('As senhas não coincidem!', 'erro');
                return false; // Retorna false para não enviar
            }

            // Validação 3: Se for Professor, verifica se selecionou pelo menos uma modalidade
            if (tipo === 'Professor') {
                // Busca todos os checkboxes de modalidades que estão marcados
                const checkboxes = document.querySelectorAll('input[name="modalidades[]"]:checked');
                if (checkboxes.length === 0) {
                    e.preventDefault(); // Impede o envio do formulário
                    exibirMensagem('Professor deve selecionar pelo menos uma modalidade!', 'erro');
                    return false; // Retorna false para não enviar
                }
            }
            
            // Se todas as validações passarem, o formulário é enviado normalmente
        });

        // ============================================
        // FORMATACAO AUTOMATICA DE CPF
        // ============================================
        // Escuta quando o usuário digita no campo CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            // Pega o valor digitado e remove tudo que não é número (pontos, traços, espaços, letras)
            // \D significa "qualquer coisa que NÃO seja dígito", /g significa "global" (todos)
            let value = e.target.value.replace(/\D/g, '');
            
            // Limita a 11 dígitos (tamanho máximo de um CPF)
            if (value.length <= 11) {
                // Aplica a máscara conforme a quantidade de dígitos digitados
                
                // Se tiver 3 dígitos ou menos: não adiciona formatação (ex: "123")
                if (value.length <= 3) {
                    value = value;
                } 
                // Se tiver 4 a 6 dígitos: adiciona o primeiro ponto (ex: "123.456")
                else if (value.length <= 6) {
                    // Pega os 3 primeiros dígitos + ponto + o restante
                    value = value.substring(0, 3) + '.' + value.substring(3);
                } 
                // Se tiver 7 a 9 dígitos: adiciona o segundo ponto (ex: "123.456.789")
                else if (value.length <= 9) {
                    // 3 primeiros + ponto + próximos 3 + ponto + restante
                    value = value.substring(0, 3) + '.' + value.substring(3, 6) + '.' + value.substring(6);
                } 
                // Se tiver 10 ou 11 dígitos: adiciona o traço final (ex: "123.456.789-00")
                else {
                    // 3 primeiros + ponto + próximos 3 + ponto + próximos 3 + traço + últimos 2
                    value = value.substring(0, 3) + '.' + value.substring(3, 6) + '.' + value.substring(6, 9) + '-' + value.substring(9, 11);
                }
                // Atualiza o campo com o valor formatado
                e.target.value = value;
            }
        });

        // ============================================
        // FORMATACAO AUTOMATICA DE TELEFONE
        // ============================================
        // Escuta quando o usuário digita no campo Telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            // Pega o valor digitado e remove tudo que não é número (parênteses, traços, espaços, letras)
            let value = e.target.value.replace(/\D/g, '');
            
            // Limita a 11 dígitos (tamanho máximo: DDD + 9 dígitos)
            if (value.length <= 11) {
                // Aplica a máscara conforme a quantidade de dígitos digitados
                
                // Se estiver vazio: mantém vazio
                if (value.length === 0) {
                    value = '';
                } 
                // Se tiver 1 ou 2 dígitos: adiciona parêntese de abertura (ex: "(19")
                else if (value.length <= 2) {
                    value = '(' + value;
                } 
                // Se tiver 3 a 6 dígitos: fecha parêntese e adiciona espaço (ex: "(19) 9876")
                else if (value.length <= 6) {
                    // 2 primeiros dígitos (DDD) + fecha parêntese + espaço + restante
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
                } 
                // Se tiver 7 a 10 dígitos: telefone fixo com traço (ex: "(19) 9876-5432")
                else if (value.length <= 10) {
                    // DDD + fecha parêntese + espaço + 4 dígitos + traço + restante
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 6) + '-' + value.substring(6);
                } 
                // Se tiver 11 dígitos: celular com traço após o 5º dígito (ex: "(19) 98765-4321")
                else {
                    // DDD + fecha parêntese + espaço + 5 dígitos + traço + últimos 4 dígitos
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7, 11);
                }
                // Atualiza o campo com o valor formatado
                e.target.value = value;
            }
        });

        // ============================================
        // BLOQUEIO DE CARACTERES INVALIDOS
        // ============================================
        // Previne que o usuário digite letras ou símbolos no campo CPF
        document.getElementById('cpf').addEventListener('keypress', function(e) {
            // Verifica se a tecla pressionada NÃO é um número (0-9)
            // E também NÃO é uma tecla de controle (Backspace, Delete, Tab, Enter)
            // Se ambas condições forem verdadeiras, bloqueia a digitação
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                // Cancela o evento, impedindo que a tecla seja digitada
                e.preventDefault();
            }
        });

        // Previne que o usuário digite letras ou símbolos no campo Telefone
        document.getElementById('telefone').addEventListener('keypress', function(e) {
            // Mesma lógica do CPF: só permite números e teclas de controle
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                // Cancela o evento, impedindo que a tecla seja digitada
                e.preventDefault();
            }
        });
    </script>

</div>

<!-- FOOTER -->
<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
