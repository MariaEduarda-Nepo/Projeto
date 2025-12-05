<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Planos</title>
    <link rel="stylesheet" href="View/header-footer.css">
    <link rel="stylesheet" href="View/planos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- CABEÇALHO PADRONIZADO -->
<?php require_once 'include/header.php'; ?>

<main>
    <h1>PLANOS</h1>
    <div class="principal">
        <div class="card">
            <h2>Plano Básico</h2>
            <img src="View/img/plano_basico_corrigido.png" alt="Plano Básico">
            <div class="pagamentos">
                <h3>Formas de Pagamento</h3>
                <div class="icones">
                    <img src="View/img/Cartao.png" alt="Cartão">
                    <img src="View/img/pix.png" alt="Pix">
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Plano Avançado</h2>
            <img src="View/img/PlanoAvançado.png" alt="Plano Avançado">
            <div class="pagamentos">
                <h3>Formas de Pagamento</h3>
                <div class="icones">
                    <img src="View/img/Cartao.png" alt="Cartão">
                    <img src="View/img/pix.png" alt="Pix">
                </div>
            </div>
        </div>
    </div>
</main>

<!-- RODAPÉ PADRONIZADO -->
<?php require_once 'include/footer.php'; ?>

</body>
</html>
