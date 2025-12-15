<?php
// Header comum para todas as páginas
?>
<header class="navbar">
    <div class="logo">
        <img src="<?php echo asset('img/logotechfit-removebg-preview.png'); ?>" alt="Logo TechFit">
        <div class="logo-text">
            <strong>TECH<span class="fit">FIT</span></strong>
            <span class="subtext">FUTURE FITNESS</span>
        </div>
    </div>

    <nav class="menu">
        <?php if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'Funcionario'): ?>
            <!-- Usuários não admin: menu normal -->
            <a href="<?php echo url('home'); ?>">INÍCIO</a>
            <a href="<?php echo url('planos'); ?>">PLANOS</a>
            <?php if (isset($_SESSION['tipo'])): ?>
                <?php if ($_SESSION['tipo'] === 'Aluno'): ?>
                    <a href="<?php echo url('agendar'); ?>">AGENDAR AULAS</a>
                    <a href="<?php echo url('avaliacao'); ?>">AVALIAÇÃO FÍSICA</a>
                <?php elseif ($_SESSION['tipo'] === 'Professor'): ?>
                    <a href="<?php echo url('agendar'); ?>">AULAS AGENDADAS</a>
                    <a href="<?php echo url('avaliacoes-professor'); ?>">AVALIAÇÕES</a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['id'])): ?>
            <div class="user-info">
                <span class="user-greeting">
                    <i class="fas fa-user-circle"></i>
                    Olá, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
                </span>
                <a href="<?php echo url('logout'); ?>" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> SAIR
                </a>
            </div>
        <?php else: ?>
            <div class="utility-links">
                <a href="<?php echo url('login'); ?>" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> ENTRAR
                </a>
                <a href="<?php echo url('cadastro'); ?>" class="register-btn">
                    <i class="fas fa-user-plus"></i> CADASTRO
                </a>
            </div>
        <?php endif; ?>
    </nav>
</header>

