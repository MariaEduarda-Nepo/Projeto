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
    <link rel="stylesheet" href="/View/cadastro.css"> 
    <title>Login - TechFit</title>
</head>

<body>

<!-- CABEÇALHO PADRONIZADO -->
<?php require_once 'include/header.php'; ?>

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
            <a href="/cadastro" style="color:#a83bd3;text-decoration:none;">
                Criar uma conta
            </a>
        </p>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem <?= $classe ?>"><?= $mensagem ?></p>
        <?php endif; ?>

        <?php if ($redirect): ?>
            <script>
                setTimeout(function() {
                    window.location.href = '/';
                }, 3000);
            </script>
        <?php endif; ?>
    </div>
</div>

<!-- RODAPÉ PADRONIZADO -->
<?php require_once 'include/footer.php'; ?>

</body>
</html>