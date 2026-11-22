
<?php

require __DIR__ . '/config/bootstrap.php';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Buscar usuário pelo e-mail
    $stmt = db()->prepare('
        SELECT id, nome, email, senha_hash, tipo
        FROM usuarios
        WHERE email = :email
        LIMIT 1
    ');

    $stmt->execute([
        'email' => $email
    ]);

    $user = $stmt->fetch();

    // Verificar credenciais
    if ($user && password_verify($senha, $user['senha_hash'])) {

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id' => (int) $user['id'],
            'nome' => $user['nome'],
            'email' => $user['email'],
            'tipo' => $user['tipo']
        ];

        setFlash('Login realizado.');

        redirect('index.php');

    }

    setFlash('E-mail ou senha incorretos.', 'danger');

}

$pageTitle = 'Entrar';

require __DIR__ . '/includes/header.php';

?>

<div class="row justify-content-center">

    <div class="col-md-6 col-lg-5">

        <h1>ENTRAR</h1>

        <form method="post" class="card card-body gap-3">

            <input
                type="hidden"
                name="_csrf"
                value="<?= e(csrfToken()) ?>"
            >

            <div>

                <label class="form-label">
                    E-mail
                </label>

                <input
                    class="form-control"
                    type="email"
                    name="email"
                    required
                >

            </div>

            <div>

                <label class="form-label">
                    Senha
                </label>

                <input
                    class="form-control"
                    type="password"
                    name="senha"
                    required
                >

            </div>

            <button class="btn btn-dark">
                ENTRAR
            </button>

            <p class="mb-0">
                Ainda não tem conta?
                <a href="cadastro.php">Cadastre-se</a>
            </p>

        </form>

    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

