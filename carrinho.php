
<?php

require __DIR__ . '/config/bootstrap.php';

// Processar ações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $action = isset($_POST['remover_id'])
        ? 'remover'
        : ($_POST['acao'] ?? '');

    $id = filter_var(
        $_POST['remover_id'] ?? ($_POST['produto_id'] ?? 0),
        FILTER_VALIDATE_INT
    ) ?: 0;

    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    // Adicionar produto
    if ($action === 'adicionar' && $id > 0) {

        $quantity = max(1, (int) ($_POST['quantidade'] ?? 1));

        $stmt = db()->prepare('
            SELECT estoque, ativo
            FROM produtos
            WHERE id = :id
        ');

        $stmt->execute([
            'id' => $id
        ]);

        $product = $stmt->fetch();

        if (!$product || !$product['ativo'] || $product['estoque'] < 1) {

            setFlash('Produto indisponível.', 'warning');

        } else {

            $newQuantity = ($_SESSION['carrinho'][$id] ?? 0) + $quantity;

            $_SESSION['carrinho'][$id] = min(
                $newQuantity,
                (int) $product['estoque']
            );

            setFlash('Produto adicionado à bolsa.');

        }

    // Atualizar quantidades
    } elseif ($action === 'atualizar') {

        foreach (($_POST['quantidades'] ?? []) as $productId => $quantity) {

            $productId = (int) $productId;
            $quantity = (int) $quantity;

            $stmt = db()->prepare('
                SELECT estoque, ativo
                FROM produtos
                WHERE id = :id
            ');

            $stmt->execute([
                'id' => $productId
            ]);

            $product = $stmt->fetch();

            if (!$product || !$product['ativo'] || $quantity < 1) {

                unset($_SESSION['carrinho'][$productId]);

            } else {

                $_SESSION['carrinho'][$productId] = min(
                    $quantity,
                    (int) $product['estoque']
                );

            }
        }

        setFlash('Carrinho atualizado.');

    // Remover produto
    } elseif ($action === 'remover' && $id > 0) {

        unset($_SESSION['carrinho'][$id]);

        setFlash('Item removido da bolsa.');

    }

    redirect('carrinho.php');
}

// Recuperar produtos do carrinho
$cart = $_SESSION['carrinho'] ?? [];

$items = [];
$total = 0;

if ($cart) {

    $ids = array_keys($cart);

    $marks = implode(',', array_fill(0, count($ids), '?'));

    $stmt = db()->prepare("
        SELECT
            id,
            nome,
            preco,
            estoque,
            imagem,
            ativo
        FROM produtos
        WHERE id IN ($marks)
    ");

    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $product) {

        $quantity = min(
            (int) $cart[$product['id']],
            (int) $product['estoque']
        );

        if (!$product['ativo'] || $quantity < 1) {

            unset($_SESSION['carrinho'][$product['id']]);

            continue;

        }

        $product['quantidade'] = $quantity;

        $product['subtotal'] = $quantity * (float) $product['preco'];

        $total += $product['subtotal'];

        $items[] = $product;

    }
}

$pageTitle = 'Sua bolsa';

require __DIR__ . '/includes/header.php';

?>

<h1>SUA BOLSA</h1>

<?php if (!$items): ?>

    <div class="alert alert-secondary">

        Sua bolsa está vazia.
        <a href="index.php">Voltar ao catálogo</a>

    </div>

<?php else: ?>

    <form method="post">

        <input
            type="hidden"
            name="_csrf"
            value="<?= e(csrfToken()) ?>"
        >

        <input
            type="hidden"
            name="acao"
            value="atualizar"
        >

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th style="width: 130px;">Quantidade</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($items as $product): ?>

                        <tr>

                            <td>
                                <?= e($product['nome']) ?>
                            </td>

                            <td>
                                <?= money($product['preco']) ?>
                            </td>

                            <td>

                                <input
                                    class="form-control"
                                    type="number"
                                    min="0"
                                    max="<?= $product['estoque'] ?>"
                                    name="quantidades[<?= $product['id'] ?>]"
                                    value="<?= $product['quantidade'] ?>"
                                >

                            </td>

                            <td>
                                <?= money($product['subtotal']) ?>
                            </td>

                            <td>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    type="submit"
                                    formaction="carrinho.php"
                                    formmethod="post"
                                    name="remover_id"
                                    value="<?= $product['id'] ?>"
                                >
                                    Remover
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <button class="btn btn-outline-dark" type="submit">
            ATUALIZAR QUANTIDADES
        </button>

    </form>

    <div class="text-end mt-4">

        <h3>
            Total: <?= money($total) ?>
        </h3>

        <a class="btn btn-dark" href="finalizar.php">
            CONTINUAR PARA PAGAMENTO
        </a>

    </div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>

