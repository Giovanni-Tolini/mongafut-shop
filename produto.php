
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

// Configurar título da página
$pageTitle = $product['nome'];

require __DIR__ . '/includes/header.php';

// Caminho da imagem
$productImage = !empty($product['imagem'])
    ? 'assets/img/produtos/' . $product['imagem']
    : 'assets/img/produtos/sem-imagem.svg';
?>

<div class="row g-4 align-items-start">

    <!-- Imagem do produto -->
    <div class="col-md-6">

        <img
            class="img-fluid rounded"
            src="<?= e($productImage) ?>"
            alt="<?= e($product['nome']) ?>"
        >

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

        <!-- Voltar ao catálogo -->
        <a class="btn btn-link mt-3" href="index.php">
            ← Voltar ao catálogo
        </a>

    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

