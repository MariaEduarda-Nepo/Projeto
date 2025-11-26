<?php
// Inclui o arquivo DAO
require_once 'UsuarioDAO.php';

// Cria uma instância do DAO
$usuarioDAO = new UsuarioDAO();

// --- LER/READ (Lista e exibe todos os usuários) ---
function lerUsuarios($dao) {
    $usuarios = $dao->lerTodos();
    
    if (count($usuarios) > 0) {
        foreach($usuarios as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['ID_USUARIO']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Nome']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Tipo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ID_FILIAL']) . "</td>";
            echo "<td>";
            // Os botões de ação chamam a função JS do index.html
            echo "<button class='edit-btn' onclick=\"preencherFormulario('" 
                . htmlspecialchars($row['ID_USUARIO']) . "', '" 
                . htmlspecialchars($row['Nome']) . "', '" 
                . htmlspecialchars($row['Email']) . "', '" 
                . htmlspecialchars($row['Documento']) . "', '" 
                . htmlspecialchars($row['Tipo']) . "', '" 
                . htmlspecialchars($row['ID_FILIAL']) . "')\">Editar</button>";
            echo "<button class='delete-btn' onclick='excluirUsuario(" 
                . htmlspecialchars($row['ID_USUARIO']) . ")'>Excluir</button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>Nenhum usuário encontrado.</td></tr>";
    }
}

// --- PROCESSAMENTO DA REQUISIÇÃO (GET/POST) ---

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
    $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $documento = $_POST['documento'];
    $tipo = $_POST['tipo'];
    $id_filial = (int)$_POST['id_filial'];

    if ($acao == 'cadastrar') {
        if ($usuarioDAO->cadastrar($nome, $email, $senha, $documento, $tipo, $id_filial)) {
            $mensagem = "Novo usuário '$nome' cadastrado com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar usuário.";
        }
    } elseif ($acao == 'atualizar' && $id_usuario > 0) {
        if ($usuarioDAO->atualizar($id_usuario, $nome, $email, $senha, $documento, $tipo, $id_filial)) {
            $mensagem = "Usuário ID $id_usuario atualizado com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar usuário.";
        }
    }
    
    header("Location: index.php?msg=" . urlencode($mensagem));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['acao']) && $_GET['acao'] == 'excluir' && isset($_GET['id_usuario'])) {
    $id_usuario = (int)$_GET['id_usuario'];
    
    if ($usuarioDAO->excluir($id_usuario)) {
        $mensagem = "Usuário ID $id_usuario excluído com sucesso!";
    } else {
        $mensagem = "Erro ao excluir. Verifique se há registros dependentes (Foreign Keys).";
    }
    
    header("Location: index.php?msg=" . urlencode($mensagem));
    exit();
}

?>