<?php
$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);

switch($request) {
    case '/':
        require __DIR__ . '/View/indexpaginainicial.php';
        break;
    case '/cadastro':
        require __DIR__ . '/View/indexCadastro.php';
        break;
    case '/login':
        require __DIR__ . '/View/indexlogin.php';
        break;
    case '/planos':
        require __DIR__ . '/View/indexplanos.php';
        break;
    case '/agendaraulas':
        require __DIR__ . '/View/indexagendaraulas.php';
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/View/404.php';
        break;
}

?>