<?php
session_start();

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
    <link rel="stylesheet" href="View/cadastro.css">
    <style>
        .logout-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }
        .logout-message {
            background: rgba(26, 26, 26, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            max-width: 500px;
            width: 100%;
        }
        .logout-message h2 {
            color: #a83bd3;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        .logout-message p {
            color: #cccccc;
            font-size: 1.1em;
            margin-bottom: 30px;
        }
        .spinner {
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
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-message">
            <h2>Até logo, <?php echo $nomeUsuario; ?>!</h2>
            <p>Você foi desconectado com sucesso.</p>
            <div class="spinner"></div>
            <p style="font-size: 0.9em; color: #888;">Redirecionando para a página de login...</p>
        </div>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = 'View/indexlogin.php';
        }, 2000);
    </script>
</body>
</html>
