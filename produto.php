
<?php
require __DIR__ . '/config/bootstrap.php';

// Identificar o produto
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$stmt = db()->prepare('
    SELECT
        p.*,
        c.nome AS categoria_nome
    FROM produtos p
    INNER JOIN categorias c ON c.id = p.categoria_id
    WHERE p.id = :id
      AND p.ativo = 1
');

$stmt->execute([
    'id' => $id ?: 0
]);

$product = $stmt->fetch();

// Verificar se o produto existe
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Produto não encontrado';

    require __DIR__ . '/includes/header.php';
    ?>

    <div class="alert alert-warning">
        Produto não encontrado ou indisponível.
    </div>

    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Buscar imagens da galeria
$stmtImages = db()->prepare('
    SELECT imagem
    FROM produto_imagens
    WHERE produto_id = :produto_id
    ORDER BY ordem ASC, id ASC
');

$stmtImages->execute([
    'produto_id' => $product['id']
]);

$galleryImages = $stmtImages->fetchAll(PDO::FETCH_COLUMN);

// Normalizar caminhos e garantir a imagem principal
$normalizeImage = static function ($image) {
    if (empty($image)) {
        return 'assets/img/produtos/sem-imagem.svg';
    }

    return str_starts_with($image, 'assets/')
        ? $image
        : 'assets/img/produtos/' . $image;
};

$productImage = $normalizeImage($product['imagem'] ?? '');

$productImages = [$productImage];

foreach ($galleryImages as $image) {
    $image = $normalizeImage($image);

    if (!in_array($image, $productImages, true)) {
        $productImages[] = $image;
    }
}

// Configurar título
$pageTitle = $product['nome'];

require __DIR__ . '/includes/header.php';
?>

<style>
.product-gallery-main {
    position: relative;
    background: #f5f5f5;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 380px;
}

.product-gallery-main img {
    width: 100%;
    height: 420px;
    object-fit: contain;
    padding: 20px;
}

.gallery-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: rgba(255,255,255,.9);
    color: #111;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    transition: .2s;
}

.gallery-arrow:hover {
    background: #111;
    color: #fff;
}

.gallery-prev {
    left: 12px;
}

.gallery-next {
    right: 12px;
}

.gallery-counter {
    position: absolute;
    bottom: 12px;
    right: 14px;
    background: rgba(0,0,0,.7);
    color: white;
    padding: 5px 11px;
    border-radius: 20px;
    font-size: 12px;
}

.product-thumbnails {
    display: flex;
    gap: 10px;
    margin-top: 14px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.product-thumbnail {
    flex: 0 0 76px;
    height: 76px;
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 4px;
    background: #f5f5f5;
    cursor: pointer;
    overflow: hidden;
}

.product-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.product-thumbnail.active {
    border-color: #111;
}

.product-thumbnail:hover {
    border-color: #888;
}

@media (max-width: 767px) {
    .product-gallery-main {
        min-height: 280px;
    }

    .product-gallery-main img {
        height: 300px;
    }

    .product-thumbnail {
        flex-basis: 65px;
        height: 65px;
    }
}
</style>

<div class="row g-4 align-items-start">

    <!-- Galeria de imagens -->
    <div class="col-md-6">

        <div class="product-gallery-main">

            <img
                id="mainProductImage"
                src="<?= e($productImages[0]) ?>"
                alt="<?= e($product['nome']) ?>"
            >

            <?php if (count($productImages) > 1): ?>

                <button
                    type="button"
                    class="gallery-arrow gallery-prev"
                    id="galleryPrev"
                    aria-label="Imagem anterior"
                >
                    &#8249;
                </button>

                <button
                    type="button"
                    class="gallery-arrow gallery-next"
                    id="galleryNext"
                    aria-label="Próxima imagem"
                >
                    &#8250;
                </button>

                <span class="gallery-counter" id="galleryCounter">
                    1 / <?= count($productImages) ?>
                </span>

            <?php endif; ?>

        </div>

        <?php if (count($productImages) > 1): ?>

            <div class="product-thumbnails">

                <?php foreach ($productImages as $index => $image): ?>

                    <button
                        type="button"
                        class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                        data-index="<?= $index ?>"
                        aria-label="Ver imagem <?= $index + 1 ?>"
                    >
                        <img src="<?= e($image) ?>" alt="">
                    </button>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <!-- Informações do produto -->
    <div class="col-md-6">

        <small>
            <?= e($product['categoria_nome']) ?>
        </small>

        <h1>
            <?= e($product['nome']) ?>
        </h1>

        <h3>
            <?= money($product['preco']) ?>
        </h3>

        <p>
            <?= nl2br(e($product['descricao'] ?? '')) ?>
        </p>

        <!-- Disponibilidade -->
        <p>
            <strong>Disponibilidade:</strong>

            <?php if ((int)$product['estoque'] > 0): ?>

                Em estoque (<?= (int)$product['estoque'] ?> unidades)

            <?php else: ?>

                Produto esgotado

            <?php endif; ?>

        </p>

        <!-- Adicionar ao carrinho -->
        <?php if ((int)$product['estoque'] > 0): ?>

            <form
                method="post"
                action="carrinho.php"
                class="d-flex gap-2 align-items-end"
            >

                <input
                    type="hidden"
                    name="_csrf"
                    value="<?= e(csrfToken()) ?>"
                >

                <input
                    type="hidden"
                    name="acao"
                    value="adicionar"
                >

                <input
                    type="hidden"
                    name="produto_id"
                    value="<?= (int)$product['id'] ?>"
                >

                <div>
                    <label class="form-label" for="quantidade">
                        Quantidade
                    </label>

                    <input
                        class="form-control"
                        id="quantidade"
                        type="number"
                        name="quantidade"
                        min="1"
                        max="<?= (int)$product['estoque'] ?>"
                        value="1"
                        required
                    >
                </div>

                <button class="btn btn-dark">
                    ADICIONAR À BOLSA
                </button>

            </form>

        <?php endif; ?>

        <a class="btn btn-link mt-3" href="index.php">
            ← Voltar ao catálogo
        </a>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainImage = document.getElementById('mainProductImage');
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    const counter = document.getElementById('galleryCounter');
    const prev = document.getElementById('galleryPrev');
    const next = document.getElementById('galleryNext');

    if (!mainImage || thumbnails.length === 0) {
        return;
    }

    let currentIndex = 0;

    function showImage(index) {
        currentIndex = (index + thumbnails.length) % thumbnails.length;

        const selected = thumbnails[currentIndex];
        mainImage.src = selected.querySelector('img').src;

        thumbnails.forEach(function (thumbnail, i) {
            thumbnail.classList.toggle('active', i === currentIndex);
        });

        counter.textContent = (currentIndex + 1) + ' / ' + thumbnails.length;

        selected.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'nearest'
        });
    }

    thumbnails.forEach(function (thumbnail) {
        thumbnail.addEventListener('click', function () {
            showImage(Number(this.dataset.index));
        });
    });

    if (prev) {
        prev.addEventListener('click', function () {
            showImage(currentIndex - 1);
        });
    }

    if (next) {
        next.addEventListener('click', function () {
            showImage(currentIndex + 1);
        });
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
