
<?php

require __DIR__ . '/config/bootstrap.php';

// Limpar os dados da sessão
$_SESSION = [];

// Remover o cookie da sessão
if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );

}

// Encerrar a sessão
session_destroy();

// Voltar para a página inicial
redirect('index.php');
