
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

// Normalizar caminhos das imagens
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

// Definir imagem do triângulo conforme a categoria
$categoryId = (int) $product['categoria_id'];

$sceneImages = [
    2 => 'assets/img/cenarios/campo.jpg',
    3 => 'assets/img/cenarios/society.jpg',
    4 => 'assets/img/cenarios/futsal.jpg'
];

$isBoot = isset($sceneImages[$categoryId]);

$sceneImage = $isBoot
    ? $sceneImages[$categoryId]
    : '';

// Configurar título
$pageTitle = $product['nome'];

require __DIR__ . '/includes/header.php';
?>

<style>
/* ========================================
   PÁGINA DO PRODUTO
======================================== */

.product-detail-page {
    position: relative;
    isolation: isolate;
    overflow: visible;
    padding: 35px 0 80px;
}

.product-corner {
    position: absolute;
    right: calc((100vw - 100%) / -2);
    bottom: -89px;
    width: min(50vw, 500px);
    height: min(50vh, 500px);
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
    clip-path: polygon(100% 0, 100% 100%, 0 100%);
    background: #c9f31d;
}

.product-corner-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

/* Aparência da Home para produtos que não são chuteiras */

.product-corner-accessory {
    background: #c9f31d;
}

.product-corner-accessory img {
    position: relative;
    z-index: 1;
    display: block;
    width: 130%;
    height: 110%;
    max-width: none;
    object-fit: contain;
    mix-blend-mode: multiply;
    transform: rotate(-8deg);
}

/* Cabeçalho da página */

.product-back {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 32px;
    color: #242f23;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-decoration: none;
    transition: .2s;
}

.product-back:hover {
    color: #8cae00;
    transform: translateX(-4px);
}

.product-layout {
    position: relative;
    z-index: 1;
}

/* ========================================
   GALERIA
======================================== */

.product-gallery {
    position: relative;
}

.product-gallery-main {
    position: relative;
    min-height: 470px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #f4f5f1;
    border-radius: 4px;
}

.product-gallery-main::before {
    content: "MONGAFUT / PRODUCT";
    position: absolute;
    top: 22px;
    left: 24px;
    color: #a2aa9e;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 2px;
}

.product-gallery-main img {
    width: 100%;
    height: 470px;
    padding: 35px;
    object-fit: contain;
    transition: opacity .2s;
}

.gallery-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    border: 1px solid #e5e7e1;
    border-radius: 50%;
    background: rgba(255,255,255,.92);
    color: #17251c;
    font-size: 25px;
    cursor: pointer;
    transition: .2s;
}

.gallery-arrow:hover {
    background: #17251c;
    color: #c9f31d;
    border-color: #17251c;
}

.gallery-prev {
    left: 16px;
}

.gallery-next {
    right: 16px;
}

.gallery-counter {
    position: absolute;
    right: 20px;
    bottom: 18px;
    color: #687367;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
}

.product-thumbnails {
    display: flex;
    gap: 11px;
    margin-top: 14px;
    padding-bottom: 5px;
    overflow-x: auto;
}

.product-thumbnail {
    flex: 0 0 78px;
    height: 78px;
    padding: 5px;
    overflow: hidden;
    border: 1px solid #e5e8e1;
    border-radius: 3px;
    background: #f4f5f1;
    cursor: pointer;
    transition: .2s;
}

.product-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.product-thumbnail.active {
    border: 2px solid #a5c900;
}

.product-thumbnail:hover {
    border-color: #17251c;
}

/* ========================================
   INFORMAÇÕES
======================================== */

.product-info {
    padding: 12px 10px 20px 25px;
}

.product-category {
    display: inline-block;
    margin-bottom: 17px;
    color: #829600;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.product-info h1 {
    max-width: 540px;
    margin: 0 0 19px;
    color: #17251c;
    font-family: "Barlow Condensed", sans-serif;
    font-size: clamp(36px, 4vw, 54px);
    font-weight: 900;
    line-height: .98;
    letter-spacing: -.5px;
    text-transform: uppercase;
}

.product-price {
    margin-bottom: 24px;
    color: #17251c;
    font-family: "Barlow Condensed", sans-serif;
    font-size: 35px;
    font-weight: 800;
}

.product-divider {
    width: 48px;
    height: 4px;
    margin-bottom: 24px;
    background: #c9f31d;
}

.product-description {
    max-width: 490px;
    margin-bottom: 30px;
    color: #737d72;
    font-size: 13px;
    line-height: 1.9;
}

.product-stock {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 28px;
    padding: 15px 17px;
    border: 1px solid #e9ece5;
    background: rgba(255,255,255,.78);
    color: #4e5a4d;
    font-size: 11px;
    font-weight: 700;
}

.product-stock-dot {
    width: 8px;
    height: 8px;
    flex: 0 0 8px;
    border-radius: 50%;
    background: #9dc600;
}

.product-stock.out-of-stock .product-stock-dot {
    background: #d45b4d;
}

/* ========================================
   FORMULÁRIO / CARRINHO
======================================== */

.product-buy-form {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.product-quantity {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.product-quantity label {
    color: #657064;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.product-quantity input {
    width: 85px;
    height: 51px;
    border: 1px solid #dfe4da;
    border-radius: 0;
    background: #fff;
    color: #17251c;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    box-shadow: none;
}

.product-quantity input:focus {
    border-color: #9bbd00;
    outline: none;
    box-shadow: 0 0 0 3px #c9f31d30;
}

.product-add-button {
    min-height: 51px;
    padding: 0 25px;
    border: 0;
    border-radius: 0;
    background: #17251c;
    color: #c9f31d;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1px;
    transition: .2s;
}

.product-add-button:hover {
    background: #c9f31d;
    color: #17251c;
}

.product-add-button span {
    margin-left: 14px;
    font-size: 16px;
}

.product-unavailable {
    display: inline-block;
    padding: 16px 22px;
    background: #f1f2ed;
    color: #777;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1px;
}

.product-detail-label {
    margin-top: 35px;
    padding-top: 18px;
    border-top: 1px solid #e5e8e1;
    color: #929b8e;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

/* ========================================
   RESPONSIVIDADE
======================================== */

@media (max-width: 991px) {
    .product-corner {
        width: 58vw;
        height: 48vh;
    }
}

@media (max-width: 767px) {
    .product-corner {
        width: 65vw;
        height: 35vh;
        opacity: .75;
    }
}

@media (max-width: 420px) {
    .product-corner {
        width: 68vw;
        height: 30vh;
    }
}
</style>

<div class="product-detail-page">

    <!-- Triângulo decorativo -->
    <?php if ($isBoot): ?>

        <div class="product-corner">
            <img
                class="product-corner-photo"
                src="<?= e($sceneImage) ?>"
                alt=""
            >
        </div>

    <?php else: ?>

        <div class="product-corner product-corner-accessory">
            <img
                src="assets/img/produtos/chuteira-destaque.png"
                alt=""
            >
        </div>

    <?php endif; ?>

    <a href="catalogo.php" class="product-back">
        <span>←</span> VOLTAR AO CATÁLOGO
    </a>

    <div class="row g-4 align-items-start product-layout">

        <!-- Galeria -->
        <div class="col-lg-7 col-md-6">

            <div class="product-gallery">

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

        </div>

        <!-- Informações -->
        <div class="col-lg-5 col-md-6">

            <div class="product-info">

                <span class="product-category">
                    <?= e($product['categoria_nome']) ?>
                </span>

                <h1>
                    <?= e($product['nome']) ?>
                </h1>

                <div class="product-price">
                    <?= money($product['preco']) ?>
                </div>

                <div class="product-divider"></div>

                <p class="product-description">
                    <?= nl2br(e($product['descricao'] ?? '')) ?>
                </p>

                <!-- Disponibilidade -->
                <?php if ((int) $product['estoque'] > 0): ?>

                    <div class="product-stock">
                        <span class="product-stock-dot"></span>
                        DISPONÍVEL — <?= (int) $product['estoque'] ?> UNIDADES EM ESTOQUE
                    </div>

                <?php else: ?>

                    <div class="product-stock out-of-stock">
                        <span class="product-stock-dot"></span>
                        PRODUTO ESGOTADO
                    </div>

                <?php endif; ?>

                <!-- Adicionar ao carrinho -->
                <?php if ((int) $product['estoque'] > 0): ?>

                    <form
                        method="post"
                        action="carrinho.php"
                        class="product-buy-form"
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
                            value="<?= (int) $product['id'] ?>"
                        >

                        <div class="product-quantity">
                            <label for="quantidade">Quantidade</label>

                            <input
                                id="quantidade"
                                type="number"
                                name="quantidade"
                                min="1"
                                max="<?= (int) $product['estoque'] ?>"
                                value="1"
                                required
                            >
                        </div>

                        <button class="product-add-button" type="submit">
                            ADICIONAR À BOLSA <span>↗</span>
                        </button>

                    </form>

                <?php else: ?>

                    <span class="product-unavailable">
                        INDISPONÍVEL NO MOMENTO
                    </span>

                <?php endif; ?>

                <div class="product-detail-label">
                    MONGAFUT SHOP / FOOTBALL CULTURE / 2026
                </div>

            </div>

        </div>

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

        if (counter) {
            counter.textContent =
                (currentIndex + 1) + ' / ' + thumbnails.length;
        }

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
