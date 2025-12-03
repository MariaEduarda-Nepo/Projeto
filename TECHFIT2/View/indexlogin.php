<?php
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
        $redirect = true;
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
            <input type="email" name="email" placeholder="Email:" required>
            <input type="password" name="senha" placeholder="Senha:" required>
            <button type="submit">Entrar</button>
        </form>

        <p style="text-align:center;margin-top:12px;">
            <a href="indexCadastro.php" style="color:#a83bd3;text-decoration:none;">
                Criar uma conta
            </a>
        </p>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem <?= $classe ?>"><?= $mensagem ?></p>
        <?php endif; ?>

        <?php if ($redirect): ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'indexpaginainicial.php';
                }, 3000);
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
