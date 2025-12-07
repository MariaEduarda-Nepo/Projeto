<?php
session_start();

// Se já estiver logado, redireciona
if (isset($_SESSION['id'])) {
    header("Location: indexpaginainicial.php");
    exit;
}

require_once __DIR__ . '/../Controller/LoginController.php';

$controller = new LoginController();
$mensagem = "";
$classe = "";
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    [$classe, $mensagem] = $controller->login($email, $senha);

    if ($classe === "sucesso") {
        // Garantir que o nome está na sessão
        if (isset($_SESSION['nome']) && !empty($_SESSION['nome'])) {
            $redirect = true;
        } else {
            $classe = "erro";
            $mensagem = "Erro ao recuperar dados do usuário. Tente fazer login novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="cadastro.css"> 
    <title>Login - TechFit</title>
</head>

<body>

<!-- HEADER -->
<header class="navbar">
    <div class="logo">
        <img src="img/logotechfit-removebg-preview.png" alt="Logo TechFit">
        <div class="logo-text">
            <strong>TECH<span class="fit">FIT</span></strong>
            <span class="subtext">FUTURE FITNESS</span>
        </div>
    </div>

    <nav class="menu">
        <div class="utility-links">
            <a href="indexlogin.php" class="login-btn">LOGIN</a>
            <a href="indexCadastro.php" class="register-btn">CADASTRO</a>
        </div>
    </nav>
</header>

<!-- CONTEÚDO -->
<div class="Container">
    <h1>ENTRAR NA TECHFIT</h1>

    <div class="Cadastrar" style="margin-top:10px;">
        <h2>Login</h2>

        <form method="POST">
            <label class="field-label">Email:</label>
            <input type="email" name="email" placeholder="Digite seu email" required>
            
            <label class="field-label">Senha:</label>
            <input type="password" name="senha" placeholder="Digite sua senha" required>
            
            <button type="submit">Entrar</button>
        </form>

        <p style="text-align:center;margin-top:12px;">
            <a href="indexCadastro.php" style="color:#a83bd3;text-decoration:none;">
                Criar uma conta
            </a>
        </p>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem <?= htmlspecialchars($classe) ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <?php if ($redirect): ?>
            <style>
                .redirect-message {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: linear-gradient(135deg, #1f1f1f, #3a0f6f);
                    border: 3px solid #a83bd3;
                    border-radius: 20px;
                    padding: 40px;
                    text-align: center;
                    z-index: 10000;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.8);
                    animation: fadeIn 0.5s ease;
                }
                .redirect-message h2 {
                    color: #a83bd3;
                    margin-bottom: 20px;
                    font-size: 1.8em;
                }
                .redirect-message p {
                    color: #ffffff;
                    font-size: 1.1em;
                    margin: 10px 0;
                }
                .redirect-spinner {
                    border: 4px solid rgba(168, 59, 211, 0.3);
                    border-top: 4px solid #a83bd3;
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    animation: spin 1s linear infinite;
                    margin: 20px auto;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
                    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                }
                .redirect-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.8);
                    z-index: 9999;
                    animation: fadeIn 0.5s ease;
                }
            </style>
            <div class="redirect-overlay"></div>
            <div class="redirect-message">
                <h2><i class="fas fa-check-circle"></i> Login Realizado!</h2>
                <p>Olá, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>!</p>
                <p>Você será redirecionado para a página inicial em <strong id="countdown">3</strong> segundos...</p>
                <div class="redirect-spinner"></div>
            </div>
            <script>
                let countdown = 3;
                const countdownElement = document.getElementById('countdown');
                
                const interval = setInterval(() => {
                    countdown--;
                    if (countdown > 0) {
                        countdownElement.textContent = countdown;
                    } else {
                        clearInterval(interval);
                        window.location.href = 'indexpaginainicial.php';
                    }
                }, 1000);
            </script>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer>
        <h3>Desenvolvido por: Daniel Charlo e Maria Eduarda Nepomuceno</h3>
        
        <a href="https://github.com/MariaEduarda-Nepo" target="_blank">
            <img src="img/Github.png" alt="GitHub Maria Eduarda">
        </a>

        <a href="https://github.com/DanielCharlo" target="_blank">
            <img src="img/Github.png" alt="GitHub Daniel">
        </a>
    </footer>

</div>

</body>
</html>
