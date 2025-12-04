<?php
// Configuração do banco de dados
$host = 'localhost';
$dbname = 'techFit';
$username = 'root';
$password = 'senaisp';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para listar registros
function listarRegistros($pdo, $tabela) {
    $stmt = $pdo->query("SELECT * FROM $tabela");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Processar ações
$acao = $_GET['acao'] ?? 'menu';
$tabela = $_GET['tabela'] ?? '';

// Deletar registro
if($acao == 'deletar' && isset($_GET['id']) && $tabela) {
    $id = $_GET['id'];
    $colunaId = match($tabela) {
        'FILIAIS' => 'ID_FILIAL',
        'USUARIOS' => 'ID_USUARIO',
        'SUPORTE' => 'ID_CHAT',
        'MENSAGEM' => 'ID_MENSAGEM',
        'PLANOS' => 'ID_PLANO',
        'PAGAMENTO' => 'ID_PAGAMENTO',
        'CALENDARIO' => 'ID_CALENDARIO',
        'AGENDAMENTOS' => 'ID_AGENDAMENTO',
        default => ''
    };
    
    if($colunaId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM $tabela WHERE $colunaId = ?");
            $stmt->execute([$id]);
            $mensagem = "Registro deletado com sucesso!";
        } catch(PDOException $e) {
            $mensagem = "Erro ao deletar: " . $e->getMessage();
        }
    }
}

// Inserir/Atualizar registros
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['salvar'])) {
    $tabela = $_POST['tabela'];
    $id = $_POST['id'] ?? null;
    
    try {
        switch($tabela) {
            case 'FILIAIS':
                if($id) {
                    $stmt = $pdo->prepare("UPDATE FILIAIS SET Endereco=?, Telefone=? WHERE ID_FILIAL=?");
                    $stmt->execute([$_POST['endereco'], $_POST['telefone'], $id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO FILIAIS (Endereco, Telefone) VALUES (?, ?)");
                    $stmt->execute([$_POST['endereco'], $_POST['telefone']]);
                }
                break;
                
            case 'USUARIOS':
                if($id) {
                    $stmt = $pdo->prepare("UPDATE USUARIOS SET Nome=?, Email=?, Senha=?, Documento=?, Tipo=?, ID_FILIAL=? WHERE ID_USUARIO=?");
                    $stmt->execute([$_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['documento'], $_POST['tipo'], $_POST['id_filial'], $id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO USUARIOS (Nome, Email, Senha, Documento, Tipo, ID_FILIAL) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['documento'], $_POST['tipo'], $_POST['id_filial']]);
                }
                break;
                
            case 'PLANOS':
                if($id) {
                    $stmt = $pdo->prepare("UPDATE PLANOS SET NomeP=?, Preco=?, Duracao=? WHERE ID_PLANO=?");
                    $stmt->execute([$_POST['nomep'], $_POST['preco'], $_POST['duracao'], $id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO PLANOS (NomeP, Preco, Duracao) VALUES (?, ?, ?)");
                    $stmt->execute([$_POST['nomep'], $_POST['preco'], $_POST['duracao']]);
                }
                break;
                
            case 'CALENDARIO':
                if($id) {
                    $stmt = $pdo->prepare("UPDATE CALENDARIO SET Horario_Inicio=?, Horario_Fim=?, Data_Aula=? WHERE ID_CALENDARIO=?");
                    $stmt->execute([$_POST['horario_inicio'], $_POST['horario_fim'], $_POST['data_aula'], $id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO CALENDARIO (Horario_Inicio, Horario_Fim, Data_Aula) VALUES (?, ?, ?)");
                    $stmt->execute([$_POST['horario_inicio'], $_POST['horario_fim'], $_POST['data_aula']]);
                }
                break;
        }
        $mensagem = "Operação realizada com sucesso!";
        $acao = 'listar';
    } catch(PDOException $e) {
        $mensagem = "Erro: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - TechFit</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 40px;
        }
        .menu-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .menu-item h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .mensagem {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏋️ TechFit</h1>
            <p>Painel Administrativo</p>
        </div>
        
        <div class="content">
            <?php if(isset($mensagem)): ?>
                <div class="mensagem"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            
            <?php if($acao == 'menu'): ?>
                <div class="menu">
                    <a href="?acao=listar&tabela=FILIAIS" class="menu-item">
                        <h3>📍 Filiais</h3>
                        <p>Gerenciar filiais</p>
                    </a>
                    <a href="?acao=listar&tabela=USUARIOS" class="menu-item">
                        <h3>👥 Usuários</h3>
                        <p>Gerenciar usuários</p>
                    </a>
                    <a href="?acao=listar&tabela=PLANOS" class="menu-item">
                        <h3>💳 Planos</h3>
                        <p>Gerenciar planos</p>
                    </a>
                    <a href="?acao=listar&tabela=CALENDARIO" class="menu-item">
                        <h3>📅 Calendário</h3>
                        <p>Gerenciar horários</p>
                    </a>
                    <a href="?acao=listar&tabela=AGENDAMENTOS" class="menu-item">
                        <h3>📋 Agendamentos</h3>
                        <p>Ver agendamentos</p>
                    </a>
                    <a href="?acao=listar&tabela=PAGAMENTO" class="menu-item">
                        <h3>💰 Pagamentos</h3>
                        <p>Ver pagamentos</p>
                    </a>
                    <a href="?acao=listar&tabela=SUPORTE" class="menu-item">
                        <h3>💬 Suporte</h3>
                        <p>Ver conversas</p>
                    </a>
                    <a href="?acao=listar&tabela=MENSAGEM" class="menu-item">
                        <h3>✉️ Mensagens</h3>
                        <p>Ver mensagens</p>
                    </a>
                </div>
                
            <?php elseif($acao == 'listar'): ?>
                <a href="?acao=menu" class="back-link">← Voltar ao Menu</a>
                <h2>Gerenciar <?php echo $tabela; ?></h2>
                
                <?php if(in_array($tabela, ['FILIAIS', 'USUARIOS', 'PLANOS', 'CALENDARIO'])): ?>
                    <a href="?acao=novo&tabela=<?php echo $tabela; ?>" class="btn btn-success">+ Adicionar Novo</a>
                <?php endif; ?>
                
                <?php
                $registros = listarRegistros($pdo, $tabela);
                if($registros):
                ?>
                    <table>
                        <thead>
                            <tr>
                                <?php foreach(array_keys($registros[0]) as $coluna): ?>
                                    <th><?php echo $coluna; ?></th>
                                <?php endforeach; ?>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($registros as $registro): ?>
                                <tr>
                                    <?php foreach($registro as $valor): ?>
                                        <td><?php echo htmlspecialchars($valor); ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <?php 
                                        $idColuna = array_keys($registro)[0];
                                        $idValor = $registro[$idColuna];
                                        ?>
                                        <?php if(in_array($tabela, ['FILIAIS', 'USUARIOS', 'PLANOS', 'CALENDARIO'])): ?>
                                            <a href="?acao=editar&tabela=<?php echo $tabela; ?>&id=<?php echo $idValor; ?>" class="btn btn-primary">Editar</a>
                                        <?php endif; ?>
                                        <a href="?acao=deletar&tabela=<?php echo $tabela; ?>&id=<?php echo $idValor; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Tem certeza que deseja deletar?')">Deletar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum registro encontrado.</p>
                <?php endif; ?>
                
            <?php elseif($acao == 'novo' || $acao == 'editar'): ?>
                <a href="?acao=listar&tabela=<?php echo $tabela; ?>" class="back-link">← Voltar</a>
                <h2><?php echo $acao == 'novo' ? 'Adicionar' : 'Editar'; ?> <?php echo $tabela; ?></h2>
                
                <?php
                $registro = null;
                if($acao == 'editar' && isset($_GET['id'])) {
                    $id = $_GET['id'];
                    $colunaId = match($tabela) {
                        'FILIAIS' => 'ID_FILIAL',
                        'USUARIOS' => 'ID_USUARIO',
                        'PLANOS' => 'ID_PLANO',
                        'CALENDARIO' => 'ID_CALENDARIO',
                        default => ''
                    };
                    $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE $colunaId = ?");
                    $stmt->execute([$id]);
                    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                ?>
                
                <form method="POST">
                    <input type="hidden" name="tabela" value="<?php echo $tabela; ?>">
                    <?php if($registro): ?>
                        <input type="hidden" name="id" value="<?php echo $registro[array_keys($registro)[0]]; ?>">
                    <?php endif; ?>
                    
                    <?php if($tabela == 'FILIAIS'): ?>
                        <div class="form-group">
                            <label>Endereço:</label>
                            <input type="text" name="endereco" value="<?php echo $registro['Endereco'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Telefone:</label>
                            <input type="text" name="telefone" value="<?php echo $registro['Telefone'] ?? ''; ?>" required>
                        </div>
                        
                    <?php elseif($tabela == 'USUARIOS'): ?>
                        <div class="form-group">
                            <label>Nome:</label>
                            <input type="text" name="nome" value="<?php echo $registro['Nome'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?php echo $registro['Email'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Senha:</label>
                            <input type="password" name="senha" value="<?php echo $registro['Senha'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Documento:</label>
                            <input type="text" name="documento" value="<?php echo $registro['Documento'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Tipo:</label>
                            <select name="tipo" required>
                                <option value="Aluno" <?php echo ($registro['Tipo'] ?? '') == 'Aluno' ? 'selected' : ''; ?>>Aluno</option>
                                <option value="Professor" <?php echo ($registro['Tipo'] ?? '') == 'Professor' ? 'selected' : ''; ?>>Professor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Filial:</label>
                            <select name="id_filial" required>
                                <?php
                                $filiais = listarRegistros($pdo, 'FILIAIS');
                                foreach($filiais as $filial):
                                ?>
                                    <option value="<?php echo $filial['ID_FILIAL']; ?>" 
                                            <?php echo ($registro['ID_FILIAL'] ?? '') == $filial['ID_FILIAL'] ? 'selected' : ''; ?>>
                                        <?php echo $filial['Endereco']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                    <?php elseif($tabela == 'PLANOS'): ?>
                        <div class="form-group">
                            <label>Nome do Plano:</label>
                            <input type="text" name="nomep" value="<?php echo $registro['NomeP'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Preço:</label>
                            <input type="number" step="0.01" name="preco" value="<?php echo $registro['Preco'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Duração (dias):</label>
                            <input type="number" name="duracao" value="<?php echo $registro['Duracao'] ?? ''; ?>" required>
                        </div>
                        
                    <?php elseif($tabela == 'CALENDARIO'): ?>
                        <div class="form-group">
                            <label>Horário Início:</label>
                            <input type="time" name="horario_inicio" value="<?php echo $registro['Horario_Inicio'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Horário Fim:</label>
                            <input type="time" name="horario_fim" value="<?php echo $registro['Horario_Fim'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Data da Aula:</label>
                            <input type="date" name="data_aula" value="<?php echo $registro['Data_Aula'] ?? ''; ?>" required>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="salvar" class="btn btn-success">Salvar</button>
                    <a href="?acao=listar&tabela=<?php echo $tabela; ?>" class="btn btn-secondary">Cancelar</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>