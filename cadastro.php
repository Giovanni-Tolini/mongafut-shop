
<?php

require __DIR__ . '/config/bootstrap.php';

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Validar os dados informados
    if (
        $nome === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($senha) < 8
    ) {

        setFlash(
            'Informe nome, e-mail válido e senha com pelo menos 8 caracteres.',
            'danger'
        );

    } else {

        try {

            $stmt = db()->prepare("
                INSERT INTO usuarios (nome, email, senha_hash, tipo)
                VALUES (:nome, :email, :senha, 'cliente')
            ");

            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT)
            ]);

            // Iniciar sessão do novo usuário
            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id' => (int) db()->lastInsertId(),
                'nome' => $nome,
                'email' => $email,
                'tipo' => 'cliente'
            ];

            setFlash('Cadastro realizado com sucesso.');

            redirect('index.php');

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                setFlash(
                    'Este e-mail já está cadastrado.',
                    'danger'
                );

            } else {

                throw $e;

            }
        }
    }
}

$pageTitle = 'Criar conta';

require __DIR__ . '/includes/header.php';

?>

<div class="row justify-content-center">

    <div class="col-md-6 col-lg-5">

        <h1>CRIAR CONTA</h1>

        <form method="post" class="card card-body gap-3">

            <input
                type="hidden"
                name="_csrf"
                value="<?= e(csrfToken()) ?>"
            >

            <div>

                <label class="form-label">
                    Nome
                </label>

                <input
                    class="form-control"
                    name="nome"
                    maxlength="100"
                    required
                >

            </div>

            <div>

                <label class="form-label">
                    E-mail
                </label>

                <input
                    class="form-control"
                    type="email"
                    name="email"
                    maxlength="150"
                    required
                >

            </div>

            <div>

                <label class="form-label">
                    Senha (mínimo 8 caracteres)
                </label>

                <input
                    class="form-control"
                    type="password"
                    name="senha"
                    minlength="8"
                    required
                >

            </div>

            <button class="btn btn-dark">
                CADASTRAR
            </button>

            <p class="mb-0">
                Já tem conta?
                <a href="login.php">Entrar</a>
            </p>

        </form>

    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

