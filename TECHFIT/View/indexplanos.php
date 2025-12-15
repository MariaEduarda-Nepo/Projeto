<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';

// Se NÃO estiver logado → volta pro login
if (!isset($_SESSION['id'])) {
    redirect('login');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Planos</title>
    <link rel="stylesheet" href="<?php echo asset('header-footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('planos.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php include __DIR__ . '/header.php'; ?>

    <main>
        <h1>PLANOS</h1>
        <div class="principal">
            <div class="card">
                <h2>Plano Básico</h2>
                <img src="<?php echo asset('img/plano_basico_corrigido.png'); ?>" alt="Plano Básico">
                <div class="pagamentos">
                    <h3>Formas de Pagamento</h3>
                    <div class="icones">
                        <img src="<?php echo asset('img/Cartao.png'); ?>" alt="Cartão">
                        <img src="<?php echo asset('img/pix.png'); ?>" alt="Pix">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Plano Avançado</h2>
                <img src="<?php echo asset('img/PlanoAvançado.png'); ?>" alt="Plano Avançado">
                <div class="pagamentos">
                    <h3>Formas de Pagamento</h3>
                    <div class="icones">
                        <img src="<?php echo asset('img/Cartao.png'); ?>" alt="Cartão">
                        <img src="<?php echo asset('img/pix.png'); ?>" alt="Pix">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

</body>
</html>