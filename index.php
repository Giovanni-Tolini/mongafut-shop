
<?php
require __DIR__ . '/config/bootstrap.php';

$user = currentUser();

$pageTitle = 'Seu jogo. Sua identidade.';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mongafut Shop — equipamentos para quem vive o futebol.">

    <title><?= e($pageTitle) ?> | Mongafut Shop</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="/mongafut_shop_v1/assets/css/style.css?v=2">
</head>

<body class="home-page">

    <div class="home-background">

        <main class="home-wrapper">

            <section class="home-panel">

                <header class="home-header">

                    <a href="index.php" class="home-brand">
                        <span>MONGA</span>
                        <strong>FUT</strong>
                    </a>

                    <nav class="home-nav">
    <a href="catalogo.php">Catálogo</a>
    <a href="catalogo.php?secao=chuteiras">Chuteiras</a>
    <a href="catalogo.php?secao=acessorios">Acessórios</a>
</nav>

                    <div class="home-actions">

                        <form action="catalogo.php" method="get" class="home-search">
                            <input
                                type="search"
                                name="busca"
                                placeholder="Buscar na loja..."
                                aria-label="Buscar produtos"
                            >
                            <button type="submit" aria-label="Buscar">
                                ⌕
                            </button>
                        </form>

                        <a href="carrinho.php" class="home-cart">
                            Bolsa <span><?= cartCount() ?></span>
                        </a>

                        <?php if ($user): ?>

                            <a href="#" class="home-account">
                                <?= e($user['nome']) ?>
                            </a>

                            <?php if ($user['tipo'] === 'admin'): ?>
                                <a href="admin/index.php" class="home-account">
                                    Admin
                                </a>
                            <?php endif; ?>

                            <a href="logout.php" class="home-account">
                                Sair
                            </a>

                        <?php else: ?>

                            <a href="login.php" class="home-account">
                                Entrar
                            </a>

                            <a href="cadastro.php" class="home-account">
                                Cadastrar
                            </a>

                        <?php endif; ?>

                    </div>

                </header>

                <div class="home-content">

                    <div class="home-copy">

                        <small class="home-eyebrow">
                            MONGAFUT SHOP / FOOTBALL CULTURE
                        </small>

                        <h1>
                            SEU JOGO.<br>
                            <span>SUA IDENTIDADE.</span>
                        </h1>

                        <p>
                            O futebol faz parte de quem você é.
                            Encontre os equipamentos certos para cada
                            partida, cada desafio e cada conquista.
                        </p>

                        <a href="catalogo.php" class="home-cta">
                            EXPLORAR CATÁLOGO
                            <span>↗</span>
                        </a>

                        <div class="home-features">
                            <span>CAMPO</span>
                            <i></i>
                            <span>SOCIETY</span>
                            <i></i>
                            <span>FUTSAL</span>
                        </div>

                    </div>

                    <div class="home-visual">

                        <div class="home-visual-frame">

                            <span class="home-visual-label">
                                MONGAFUT / PERFORMANCE
                            </span>

                            <img
    src="assets/img/produtos/chuteira-destaque.png"
    alt="Chuteira Adidas em destaque"
    class="home-boot"
>

                            <span class="home-visual-number">
                                01
                            </span>

                        </div>

                    </div>

                </div>

                <footer class="home-footer">
                    <span>01 — PERFORMANCE</span>
                    <span>SEU JOGO. SEU EQUIPAMENTO.</span>
                    <span>EST. 2026</span>
                </footer>

            </section>

        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
