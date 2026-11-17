
<?php


/*
|--------------------------------------------------------------------------
| Inicialização da aplicação
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Carregamento do arquivo .env
|--------------------------------------------------------------------------
*/

function loadEnv(string $file): void
{
    if (!is_file($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if (
            $line === '' ||
            strpos($line, '#') === 0 ||
            strpos($line, '=') === false
        ) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $key = trim($key);
        $value = trim(trim($value), "\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

loadEnv(dirname(__DIR__) . '/.env');

/*
|--------------------------------------------------------------------------
| Conexão com o banco de dados
|--------------------------------------------------------------------------
*/

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $variaveis = [
        'DB_HOST',
        'DB_PORT',
        'DB_NAME',
        'DB_USER',
        'DB_PASS'
    ];

    $config = [];

    foreach ($variaveis as $variavel) {
        $valor = getenv($variavel);

        if (
            $valor === false ||
            ($variavel !== 'DB_PASS' && trim($valor) === '')
        ) {
            throw new RuntimeException(
                "A variável {$variavel} não foi configurada no arquivo .env."
            );
        }

        $config[$variavel] = $valor;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['DB_HOST'],
        $config['DB_PORT'],
        $config['DB_NAME']
    );

    $pdo = new PDO(
        $dsn,
        $config['DB_USER'],
        $config['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    return $pdo;
}

/*
|--------------------------------------------------------------------------
| Funções auxiliares
|--------------------------------------------------------------------------
*/

function e(?string $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function money(float|string $value): string
{
    return 'R$ ' . number_format(
        (float) $value,
        2,
        ',',
        '.'
    );
}

/*
|--------------------------------------------------------------------------
| Redirecionamento
|--------------------------------------------------------------------------
*/

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/*
|--------------------------------------------------------------------------
| Proteção CSRF
|--------------------------------------------------------------------------
*/

function csrfToken(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function verifyCsrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    $sessionToken = $_SESSION['_csrf'] ?? '';

    if (
        !is_string($token) ||
        !is_string($sessionToken) ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $token)
    ) {
        http_response_code(403);

        exit(
            'Solicitação inválida. Atualize a página e tente novamente.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
*/

function currentUser(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        redirect('login.php');
    }
}

function requireAdmin(): void
{
    $usuario = currentUser();

    if (!$usuario || ($usuario['tipo'] ?? '') !== 'admin') {
        http_response_code(403);

        exit('Acesso restrito a administradores.');
    }
}

/*
|--------------------------------------------------------------------------
| Carrinho
|--------------------------------------------------------------------------
*/

function cartCount(): int
{
    return array_sum(
        array_map(
            'intval',
            $_SESSION['carrinho'] ?? []
        )
    );
}

/*
|--------------------------------------------------------------------------
| Mensagens do sistema
|--------------------------------------------------------------------------
*/

function setFlash(
    string $message,
    string $type = 'success'
): void {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function showFlash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);

    echo '<div class="alert alert-' .
        e($flash['type']) .
        ' alert-dismissible fade show" role="alert">';

    echo e($flash['message']);

    echo '<button type="button" class="btn-close" ' .
        'data-bs-dismiss="alert" aria-label="Fechar"></button>';

    echo '</div>';
}

