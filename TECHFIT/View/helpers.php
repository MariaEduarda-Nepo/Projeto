<?php
/**
 * Funções auxiliares para views
 */

// Função para gerar URL usando o sistema de rotas
if (!function_exists('url')) {
function url($path = '') {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    // Remove /View do caminho base se existir
    $base = str_replace('/View', '', $base);
    
    // Se não tiver .htaccess, usa query string
    // Verifica se .htaccess está ativo tentando acessar uma rota de teste
    // Por padrão, vamos usar o formato com query string se não tiver certeza
    if (file_exists(__DIR__ . '/../.htaccess')) {
        // Com .htaccess: URLs limpas
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    } else {
        // Sem .htaccess: usa query string
        return $base . '/index.php' . ($path ? '?route=' . ltrim($path, '/') : '?route=home');
    }
}
}

// Função para redirecionar
if (!function_exists('redirect')) {
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}
}

// Função para verificar se está logado
function isLoggedIn() {
    return isset($_SESSION['id']);
}

// Função para verificar tipo de usuário
function isAluno() {
    return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'Aluno';
}

function isProfessor() {
    return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'Professor';
}

function isFuncionario() {
    return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'Funcionario';
}

// Função para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Função para gerar caminho de assets (CSS, JS, imagens)
function asset($path) {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    // Remove /View do caminho base se existir
    $base = str_replace('/View', '', $base);
    // Garante que o caminho começa com /
    $path = '/' . ltrim($path, '/');
    // Se o path não começar com View/ e não for uma URL externa, adiciona /View
    if (strpos($path, '/View/') === false && strpos($path, 'http') !== 0 && strpos($path, '//') !== 0) {
        $path = '/View' . $path;
    }
    return $base . $path;
}

?>

