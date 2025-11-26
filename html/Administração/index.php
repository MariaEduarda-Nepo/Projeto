<?php
// Inclui o arquivo de CRUD para processar requisições
require_once 'crud_usuarios.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD TechFit - Usuários</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #000000; 
        }
        .container { 
            max-width: 1000px; 
            margin: auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        h1, h2 { 
            color: #34495e; 
            text-align: center; 
        }
        form { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
            margin-bottom: 30px; 
            padding: 15px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
        }
        input[type="text"], 
        input[type="email"], 
        input[type="password"], 
        select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box; 
        }
        button { 
            background-color: #a83bd3; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
            transition: background-color 0.3s; 
        }
        button:hover { 
            background-color: #bb2aaf; 
        }
        .full-width { 
            grid-column: 1 / 3; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            padding: 12px; 
            border: 1px solid #ddd; 
            text-align: left; 
        }
        th { 
            background-color: #a83bd3; 
            color: white; 
        }
        .edit-btn, .delete-btn { 
            padding: 5px 10px; 
            margin: 2px; 
        }
        .edit-btn { 
            background-color: #3498db; 
        }
        .edit-btn:hover { 
            background-color: #2980b9; 
        }
        .delete-btn { 
            background-color: #e74c3c; 
        }
        .delete-btn:hover { 
            background-color: #c0392b; 
        }
        .message { 
            text-align: center; 
            color: #9d0dba; 
            font-weight: bold; 
            margin-bottom: 15px; 
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Gestão de Usuários - TechFit</h1>
    <div id="mensagem" class="message"></div>

    <h2>Cadastrar/Atualizar Usuário</h2>
    <form id="userForm" method="POST" action="crud_usuarios.php">
        <input type="hidden" id="id_usuario" name="id_usuario">

        <input type="text" id="nome" name="nome" placeholder="Nome" required>
        <input type="email" id="email" name="email" placeholder="Email" required>
        
        <input type="text" id="documento" name="documento" placeholder="Documento" required>
        <select id="tipo" name="tipo" required>
            <option value="">Selecione o Tipo</option>
            <option value="Aluno">Aluno</option>
            <option value="Professor">Professor</option>
            <option value="Administrador">Administrador</option>
        </select>

        <input type="password" id="senha" name="senha" placeholder="Senha (Para Novo Usuário)">
        <input type="text" id="id_filial" name="id_filial" placeholder="ID da Filial (Ex: 1)" value="1" required>

        <button type="submit" name="acao" value="cadastrar" id="submitBtn" class="full-width">Cadastrar</button>
    </form>

    <hr>

    <h2>Lista de Usuários</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Tipo</th>
                <th>Filial (ID)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <?php lerUsuarios($usuarioDAO); ?>
        </tbody>
    </table>
</div>

<script>
    // Exibe mensagem de sucesso/erro se houver
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg) {
        document.getElementById('mensagem').innerText = decodeURIComponent(msg);
        // Limpa o parâmetro da URL para não reexibir ao recarregar
        history.replaceState(null, '', window.location.pathname);
    }

    // Função para preencher o formulário ao clicar em "Editar"
    function preencherFormulario(id, nome, email, documento, tipo, filial) {
        document.getElementById('id_usuario').value = id;
        document.getElementById('nome').value = nome;
        document.getElementById('email').value = email;
        document.getElementById('documento').value = documento;
        document.getElementById('tipo').value = tipo;
        document.getElementById('id_filial').value = filial;
        document.getElementById('senha').placeholder = 'Nova Senha (Opcional)';
        
        // Altera o valor e o texto do botão para "Atualizar"
        document.getElementById('submitBtn').value = 'atualizar';
        document.getElementById('submitBtn').innerText = 'Atualizar Usuário';
        
        // Rola a página para o formulário
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Função para confirmar e enviar a exclusão
    function excluirUsuario(id) {
        if (confirm('Tem certeza que deseja excluir o usuário ID ' + id + '?')) {
            window.location.href = 'crud_usuarios.php?acao=excluir&id_usuario=' + id;
        }
    }
</script>

</body>
</html>