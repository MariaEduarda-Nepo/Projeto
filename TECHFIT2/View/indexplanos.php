<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Planos</title>
    <link rel="stylesheet" href="header-footer.css">
    <link rel="stylesheet" href="planos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <!-- CABEÇALHO PADRONIZADO -->
    <header class="navbar">
        <div class="logo">
            <img src="img/logotechfit-removebg-preview.png" alt="Logo TechFit">
            <div class="logo-text">
                <strong>TECH<span class="fit">FIT</span></strong>
                <span class="subtext">FUTURE FITNESS</span>
            </div>
        </div>
        <nav class="menu">
            <a href="indexpaginainicial.php">INÍCIO</a>
            <a href="indexplanos.php">PLANOS</a>
            <a href="">AGENDAR AULAS</a>
        </nav>
    </header>

    <main>
        <h1>PLANOS</h1>
        <div class="principal">
            <div class="card">
                <h2>Plano Básico</h2>
                <img src="img/plano_basico_corrigido.png" alt="Plano Básico">
                <div class="pagamentos">
                    <h3>Formas de Pagamento</h3>
                    <div class="icones">
                        <img src="img/Cartao.png" alt="Cartão">
                        <img src="img/pix.png" alt="Pix">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Plano Avançado</h2>
                <img src="img/PlanoAvançado.png" alt="Plano Avançado">
                <div class="pagamentos">
                    <h3>Formas de Pagamento</h3>
                    <div class="icones">
                        <img src="img/Cartao.png" alt="Cartão">
                        <img src="img/pix.png" alt="Pix">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- RODAPÉ PADRONIZADO -->
    <footer>
        <h3>Desenvolvido por: Daniel Charlo e Maria Eduarda Nepomuceno</h3>
        <a href="https://github.com/MariaEduarda-Nepo" target="_blank"><img src="img/Github.png" alt="GitHub Maria Eduarda"></a>
        <a href="https://github.com/DanielCharlo" target="_blank"><img src="img/Github.png" alt="img/GitHub Daniel"></a>
    </footer>

    <script>
        // Menu mobile toggle
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.menu').classList.toggle('active');
        });
    </script>

</body>
</html>