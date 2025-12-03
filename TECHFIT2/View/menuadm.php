<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Agendar Aulas</title>
    <link rel="stylesheet" href="header-footer.css">
    <link rel="stylesheet" href="agendar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

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
        <a href="indexagendaraulas.php">AGENDAR AULAS</a>
    </nav>
</header>

<main class="agenda-container">
    <h1>Agendar Aulas</h1>

    <div class="calendario">
        <h2>Selecione o Dia</h2>
        <div class="dias-grid">
            <button class="dia">01</button>
            <button class="dia">02</button>
            <button class="dia">03</button>
            <button class="dia">04</button>
            <button class="dia">05</button>
            <button class="dia">06</button>
            <button class="dia">07</button>
            <button class="dia">08</button>
            <button class="dia">09</button>
            <button class="dia">10</button>
            <button class="dia">11</button>
            <button class="dia">12</button>
        </div>
    </div>
</main>

<!-- MODAL -->
<div class="modal" id="modal">
    <div class="modal-content">
        <span class="close" id="fechar">&times;</span>
        <h2>Agendar Aula</h2>

        <label>Professor:</label>
        <select id="professor">
            <option value="">Selecione</option>
            <option>João Personal</option>
            <option>Maria Funcional</option>
            <option>Carlos Musculação</option>
        </select>

        <label>Horário:</label>
        <select id="horario">
            <option value="">Selecione</option>
            <option>07:00</option>
            <option>08:00</option>
            <option>09:00</option>
            <option>10:00</option>
            <option>15:00</option>
            <option>18:00</option>
        </select>

        <button class="confirmar-btn">Confirmar</button>
    </div>
</div>

<footer>
    <h3>Desenvolvido por: Daniel Charlo e Maria Eduarda Nepomuceno</h3>
    <a href="https://github.com/MariaEduarda-Nepo" target="_blank"><img src="img/Github.png"></a>
    <a href="https://github.com/DanielCharlo" target="_blank"><img src="img/Github.png"></a>
</footer>

<script>
    const botoesDia = document.querySelectorAll('.dia');
    const modal = document.getElementById('modal');
    const fechar = document.getElementById('fechar');

    botoesDia.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.add('mostrar');
        });
    });

    fechar.addEventListener('click', () => {
        modal.classList.remove('mostrar');
    });

    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('mostrar');
    });
</script>

</body>
</html>