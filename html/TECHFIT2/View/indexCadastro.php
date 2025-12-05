<?php
session_start();
require_once __DIR__ . '/../Controller/CadastroController.php';

$controller = new CadastroController();
$acao = $_POST['acao'] ?? '';
$mensagem = "";
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($acao === 'criar') {
        $resultado = $controller->criar(
            $_POST['tipo'],
            $_POST['nome'],
            $_POST['senha'],
            $_POST['confirmarsenha'],
            $_POST['email'],
            $_POST['documento'],
            $_POST['datanascimento']
        );

        if ($resultado === true) {
            $sucesso = true;
            $mensagem = "<p class='sucesso'>Cadastro concluído com sucesso!</p>";
        } else {
            $mensagem = "<p class='erro'>$resultado</p>";
        }
    }

    if ($acao === 'deletar') {
        $controller->deletar($_POST['id']);
        $sucesso = true;
        $mensagem = "<p class='sucesso'>Cadastro removido.</p>";
    }

    if ($acao === 'editar') {
        $editarCadastro = $controller->buscarPorId($_POST['id']);
    }

    if ($acao === 'atualizar') {
        $resultado = $controller->atualizar(
            $_POST['id'],
            $_POST['novotipo'],
            $_POST['novonome'],
            $_POST['novasenha'] ?? "",
            $_POST['confirmarnovasenha'] ?? "",
            $_POST['novoemail'],
            $_POST['novodocumento'],
            $_POST['novadatanascimento']
        );

        if ($resultado === true) {
            $sucesso = true;
            $mensagem = "<p class='sucesso'>Cadastro atualizado com sucesso!</p>";
        } else {
            $mensagem = "<p class='erro'>$resultado</p>";
        }
    }
}

$lista = $controller->ler();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/View/cadastro.css">
    <title>Cadastro - TechFit</title>

    <?php if($sucesso): ?>
    <script>
        setTimeout(() => { 
            window.location.href = '/login'; 
        }, 5000);
    </script>
    <?php endif; ?>
</head>

<body>

<!-- CABEÇALHO PADRONIZADO -->
<?php require_once 'include/header.php'; ?>

<div class="Container">

    <h1>FAÇA SEU CADASTRO NA TECHFIT</h1>

    <div class="Cadastrar">
        <h2>Cadastrar</h2>

        <form method="POST">
            <input type="hidden" name="acao" value="criar">

            <select name="tipo" required>
                <option disabled selected hidden>Tipo (selecione)</option>
                <option value="Aluno">Aluno</option>
                <option value="Professor">Professor</option>
            </select>

            <input type="text" name="nome" placeholder="Nome Completo:" required>
            <input type="date" name="datanascimento" required>
            <input type="password" name="senha" placeholder="Senha:" required>
            <input type="password" name="confirmarsenha" placeholder="Confirmar Senha:" required>
            <input type="email" name="email" placeholder="Email:" required>
            <input type="text" name="documento" placeholder="Documento:" required>

            <button type="submit">Cadastrar</button>
        </form>
        <p style="text-align:center;margin-top:12px;">
            <a href="/login" style="color:#a83bd3;text-decoration:none;">
                Ja tenho uma conta
            </a>
        </p>

        <div class="mensagem"><?= $mensagem ?></div>
    </div>

</div>

<!-- RODAPÉ PADRONIZADO -->
<?php require_once 'include/footer.php'; ?>

</body>
</html>