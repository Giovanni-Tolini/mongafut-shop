
<?php
require __DIR__ . '/config/bootstrap.php';

$pdo = db();

// Buscar categorias
$categories = $pdo->query(
    'SELECT id, nome FROM categorias ORDER BY nome'
)->fetchAll();

// Filtros
$categoryId = filter_input(
    INPUT_GET,
    'categoria',
    FILTER_VALIDATE_INT
) ?: 0;

$search = trim((string)($_GET['busca'] ?? ''));

// Consulta dos produtos
$sql = '
    SELECT
        p.*,
        c.nome AS categoria_nome
    FROM produtos p
    INNER JOIN categorias c ON c.id = p.categoria_id
    WHERE p.ativo = 1
';

$params = [];

if ($categoryId > 0) {
    $sql .= ' AND p.categoria_id = :categoria';
    $params['categoria'] = $categoryId;
}

if ($search !== '') {
    $sql .= '
        AND (
            p.nome LIKE :busca_nome
            OR p.descricao LIKE :busca_desc
            OR c.nome LIKE :busca_categoria
        )
    ';

    $params['busca_nome'] = '%' . $search . '%';
    $params['busca_desc'] = '%' . $search . '%';
    $params['busca_categoria'] = '%' . $search . '%';
}

$sql .= ' ORDER BY p.criado_em DESC, p.nome';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Seu jogo. Seu equipamento.';

require __DIR__ . '/includes/header.php';
?>

<!-- Banner principal -->
<section class="hero mb-4">

    <div class="hero-copy">
        <small>MONGAFUT SHOP / FUTEBOL EM PRIMEIRO LUGAR</small>

        <h1>
            O SEU JOGO.<br>
            <em>SEU EQUIPAMENTO.</em>
        </h1>

        <p>
            Chuteiras e acessórios para acompanhar cada movimento,
            em qualquer tipo de campo.
        </p>

        <a href="#catalogo">EXPLORAR CATÁLOGO ↗</a>
    </div>

    <div class="stamp">
        PLAY
        <strong>YOUR<br>GAME</strong>
        EST. 2026
    </div>

    <footer>
        01 — PERFORMANCE
        <span>CAMPO · SOCIETY · FUTSAL</span>
    </footer>

</section>

<!-- Catálogo -->
<section id="catalogo">

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">

        <div>
            <small>ENCONTRE SEU PRÓXIMO EQUIPAMENTO</small>
            <h2 class="mb-0">
                CATÁLOGO <span>DE PRODUTOS</span>
            </h2>
        </div>

        <!-- Busca -->
        <form class="d-flex gap-2" method="get">

            <input
                class="form-control"
                name="busca"
                value="<?= e($search) ?>"
                placeholder="Buscar produto..."
            >

            <button class="btn btn-dark">
                Buscar
            </button>

        </form>

    </div>

    <!-- Filtro por categoria -->
    <form method="get" class="row g-2 mb-4">

        <div class="col-sm-8 col-md-5">

            <select class="form-select" name="categoria">

                <option value="0">Todas as categorias</option>

                <?php foreach ($categories as $c): ?>

                    <option
                        value="<?= (int)$c['id'] ?>"
                        <?= $categoryId === (int)$c['id'] ? 'selected' : '' ?>
                    >
                        <?= e($c['nome']) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="col-auto">

            <button class="btn btn-outline-dark">
                Filtrar
            </button>

            <a class="btn btn-link" href="index.php#catalogo">
                Limpar
            </a>

        </div>

    </form>

    <!-- Resultados -->
    <?php if (!$products): ?>

        <div class="alert alert-secondary">
            Nenhum produto encontrado. Tente outra busca ou categoria.
        </div>

    <?php else: ?>

        <div class="row g-4">

            <?php foreach ($products as $p): ?>

                <div class="col-12 col-sm-6 col-lg-4">

                    <article class="card-product h-100">

                        <!-- Imagem do produto -->
                        <a
                            class="photo d-block"
                            href="produto.php?id=<?= (int)$p['id'] ?>"
                        >

                            <img
                                src="<?= e(
                                    !empty($p['imagem'])
                                        ? 'assets/img/produtos/' . $p['imagem']
                                        : 'assets/img/produtos/sem-imagem.svg'
                                ) ?>"
                                alt="<?= e($p['nome']) ?>"
                                loading="lazy"
                            >

                        </a>

                        <div class="info">

                            <small>
                                <?= e($p['categoria_nome']) ?>

                                <i>
                                    <?= (int)$p['estoque'] > 0
                                        ? '● EM ESTOQUE'
                                        : '● ESGOTADO'
                                    ?>
                                </i>
                            </small>

                            <h3>
                                <?= e($p['nome']) ?>
                            </h3>

                            <strong>
                                <?= money($p['preco']) ?>
                            </strong>

                            <a
                                class="btn btn-dark w-100 mt-3"
                                href="produto.php?id=<?= (int)$p['id'] ?>"
                            >
                                VER PRODUTO
                            </a>

                        </div>

                    </article>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
 
