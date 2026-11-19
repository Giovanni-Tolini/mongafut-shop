
<?php
$pageTitle = $pageTitle ?? 'Mongafut Shop';
$user = currentUser();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Mongafut Shop — equipamentos para futebol de campo, society e futsal.">
    <base href="/mongafut_shop_v1/">
    <title><?= e($pageTitle) ?> | Mongafut Shop</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=3" rel="stylesheet">
</head>
<body>

<div class="announcement">
    EQUIPAMENTO CERTO. JOGO NO SEU RITMO.
    <span>MONGAFUT SHOP · FOOTBALL CULTURE</span>
</div>

<header class="header">
    <a class="brand" href="index.php" aria-label="Mongafut Shop">
        <span>MONGA</span><strong>FUT</strong>
    </a>

    <nav id="nav">
    <a href="catalogo.php">Catálogo</a>
    <a href="catalogo.php?secao=chuteiras">Chuteiras</a>
    <a href="catalogo.php?secao=acessorios">Acessórios</a>

    <?php if ($user && $user['tipo'] === 'admin'): ?>
        <a href="admin/index.php">Administração</a>
    <?php endif; ?>
</nav>

    <div class="actions">
        <a href="carrinho.php">Bolsa <b><?= cartCount() ?></b></a>

        <?php if ($user): ?>
            <a href="#">Olá, <?= e($user['nome']) ?></a>
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="login.php">Entrar</a>
            <a href="cadastro.php">Cadastrar</a>
        <?php endif; ?>
    </div>

    <button id="menu" class="mobile" type="button" aria-label="Abrir menu">☰</button>
</header>

<main class="container py-4">
    <?php showFlash(); ?>
