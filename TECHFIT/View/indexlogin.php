<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';

// Se já estiver logado, redireciona
if (isset($_SESSION['id'])) {
    // Se for Funcionario (admin), redireciona para o painel admin
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'Funcionario') {
        redirect('admin');
    } else {
        redirect('home');
    }
}

require_once __DIR__ . '/../Controller/LoginController.php';

$controller = new LoginController();
$mensagem = "";
$classe = "";
$loginSucesso = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    [$classe, $mensagem] = $controller->login($email, $senha);

    if ($classe === "sucesso") {
        // O nome sempre será definido pelo controller (mesmo que seja "Administrador" para admin primário)
        $loginSucesso = true;
        // Mostra mensagem de sucesso e redireciona após 1 segundo
        if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'Funcionario') {
            $mensagem = "Login realizado com sucesso! Redirecionando...";
        } else {
            $mensagem = "Login realizado com sucesso! Redirecionando...";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?php echo asset('login.css'); ?>"> 
    <title>Login - TechFit</title>
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
            <a href="<?php echo url('cadastro'); ?>" style="color:#8b5cf6;text-decoration:none;">
                Criar uma conta
            </a>
        </p>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem <?= htmlspecialchars($classe) ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>
        
        <?php if ($loginSucesso): ?>
            <script>
                setTimeout(function() {
                    <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'Funcionario'): ?>
                        window.location.href = '<?php echo url('admin'); ?>';
                    <?php else: ?>
                        window.location.href = '<?php echo url('home'); ?>';
                    <?php endif; ?>
                }, 1000);
            </script>
        <?php endif; ?>
    </div>

</div>

<!-- FOOTER -->
<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
