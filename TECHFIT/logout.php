<?php
// Iniciar sessão apenas se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir helpers para função url()
require_once __DIR__ . '/View/helpers.php';

// Verifica se está logado
if (isset($_SESSION['nome'])) {
    $nomeUsuario = htmlspecialchars($_SESSION['nome']);
} else {
    $nomeUsuario = "Usuário";
}

// Destroi a sessão
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - TechFit</title>
</head>
<body>
    <script>
        window.location.href = '<?php echo url('login'); ?>';
    </script>
</body>
</html>
