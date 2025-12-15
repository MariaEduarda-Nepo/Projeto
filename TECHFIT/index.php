<?php
/**
 * Sistema de Roteamento - TechFit
 * Ponto de entrada principal da aplicação
 */

session_start();

// Define constantes úteis
define('BASE_PATH', __DIR__);
define('VIEW_PATH', BASE_PATH . '/View');
define('CONTROLLER_PATH', BASE_PATH . '/Controller');
define('MODEL_PATH', BASE_PATH . '/Model');

// Inicializar Connection imediatamente para criar admin se necessário
require_once MODEL_PATH . '/Connection.php';
Connection::getInstance(); // Isso garante que o admin seja criado ao acessar o site

// Função para gerar URL
if (!function_exists('url')) {
    function url($path = '') {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }
}

// Função para redirecionar
if (!function_exists('redirect')) {
    function redirect($path) {
        header('Location: ' . url($path));
        exit;
    }
}

// Função para incluir view
function view($viewName, $data = []) {
    extract($data);
    $viewFile = VIEW_PATH . '/' . $viewName . '.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        die("View não encontrada: {$viewName}");
    }
}

// Obter a rota atual
// Primeiro tenta pegar da query string (se não tiver .htaccess)
if (isset($_GET['route']) && !empty($_GET['route'])) {
    $route = $_GET['route'];
} else {
    // Se não tiver na query string, tenta pegar da URL (com .htaccess)
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    
    // Remove query string
    $requestUri = strtok($requestUri, '?');
    
    // Remove o diretório base da URL
    $basePath = dirname($scriptName);
    if ($basePath !== '/' && $basePath !== '\\') {
        $requestUri = str_replace($basePath, '', $requestUri);
    }
    
    $route = trim($requestUri, '/');
    
    // Se a rota contém index.php, remove
    $route = str_replace('index.php', '', $route);
    $route = trim($route, '/');
}

// Se estiver vazio, define como home
if (empty($route)) {
    $route = 'home';
}

// Sistema de rotas
$routes = [
    // Rotas públicas
    'home' => 'indexpaginainicial.php',
    'login' => 'indexlogin.php',
    'cadastro' => 'indexCadastro.php',
    'planos' => 'indexplanos.php',
    'logout' => '../logout.php',
    
    // Rotas autenticadas
    'agendar' => 'indexagendaraulas.php',
    'avaliacao' => 'indexavaliacaofisica.php',
    'avaliacoes-professor' => 'indexavaliacoesprofessor.php',
    'admin' => 'indexpaineladmin.php',
    'relatorios' => 'indexrelatorios.php',
];

// Verificar se a rota existe
if (isset($routes[$route])) {
    $file = $routes[$route];
    
    // Se começar com ../, é um arquivo na raiz
    if (strpos($file, '../') === 0) {
        require BASE_PATH . '/' . str_replace('../', '', $file);
    } else {
        // Caso contrário, está na pasta View
        require VIEW_PATH . '/' . $file;
    }
} else {
    // Rota não encontrada - 404
    http_response_code(404);
    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Página não encontrada - TechFit</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #1f1f1f, #3a0f6f);
                color: white;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                text-align: center;
            }
            .error-container {
                background: rgba(24, 24, 24, 0.95);
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            }
            h1 {
                color: #a83bd3;
                font-size: 48px;
                margin: 0;
            }
            p {
                font-size: 18px;
                margin: 20px 0;
            }
            a {
                color: #a83bd3;
                text-decoration: none;
                font-weight: bold;
            }
            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <h1>404</h1>
            <p>Página não encontrada</p>
            <p><a href='" . url('home') . "'>Voltar para a página inicial</a></p>
        </div>
    </body>
    </html>";
    exit;
}
?>

