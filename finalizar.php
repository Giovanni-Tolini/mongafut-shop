
<?php

require __DIR__ . '/config/bootstrap.php';

requireLogin();

// Verificar se existem produtos no carrinho
$cart = $_SESSION['carrinho'] ?? [];

if (!$cart) {

    setFlash('Sua bolsa está vazia.', 'warning');

    redirect('carrinho.php');

}

// Processar finalização do pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $payment = $_POST['forma_pagamento'] ?? '';

    // Validar forma de pagamento
    if (!in_array($payment, ['pix', 'boleto', 'cartao'], true)) {

        setFlash('Selecione uma forma de pagamento válida.', 'danger');

        redirect('finalizar.php');

    }

    $pdo = db();

    try {

        $pdo->beginTransaction();

        $total = 0;
        $locked = [];

        // Conferir e bloquear o estoque dos produtos
        foreach ($cart as $id => $quantity) {

            $stmt = $pdo->prepare('
                SELECT id, nome, preco, estoque, ativo
                FROM produtos
                WHERE id = :id
                FOR UPDATE
            ');

            $stmt->execute([
                'id' => (int) $id
            ]);

            $product = $stmt->fetch();

            $quantity = (int) $quantity;

            if (
                !$product ||
                !$product['ativo'] ||
                $quantity < 1 ||
                $quantity > (int) $product['estoque']
            ) {

                throw new RuntimeException(
                    'Estoque insuficiente ou produto indisponível: ' .
                    ($product['nome'] ?? 'produto')
                );

            }

            $product['quantidade'] = $quantity;

            $total += (float) $product['preco'] * $quantity;

            $locked[] = $product;

        }

        // Criar o pedido
        $stmt = $pdo->prepare('
            INSERT INTO pedidos (usuario_id, forma_pagamento, total)
            VALUES (:usuario, :pagamento, :total)
        ');

        $stmt->execute([
            'usuario' => currentUser()['id'],
            'pagamento' => $payment,
            'total' => $total
        ]);

        $orderId = (int) $pdo->lastInsertId();

        // Preparar registro dos itens e atualização do estoque
        $item = $pdo->prepare('
            INSERT INTO pedido_itens (
                pedido_id,
                produto_id,
                quantidade,
                preco_unitario
            )
            VALUES (
                :pedido,
                :produto,
                :quantidade,
                :preco
            )
        ');

        $stock = $pdo->prepare('
            UPDATE produtos
            SET estoque = estoque - :quantidade
            WHERE id = :id
        ');

        // Registrar itens do pedido e baixar o estoque
        foreach ($locked as $product) {

            $item->execute([
                'pedido' => $orderId,
                'produto' => $product['id'],
                'quantidade' => $product['quantidade'],
                'preco' => $product['preco']
            ]);

            $stock->execute([
                'quantidade' => $product['quantidade'],
                'id' => $product['id']
            ]);

        }

        // Confirmar a transação
        $pdo->commit();

        $_SESSION['carrinho'] = [];
        $_SESSION['pedido_confirmado'] = $orderId;

        redirect('pedido_confirmado.php');

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        setFlash(
            $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Não foi possível concluir o pedido.',
            'danger'
        );

        redirect('carrinho.php');

    }
}

$pageTitle = 'Finalizar compra';

require __DIR__ . '/includes/header.php';

?>

<h1>FINALIZAR COMPRA</h1>

<p>
    Escolha uma forma de pagamento para simular a confirmação do pedido.
</p>

<form method="post" class="card card-body gap-3">

    <input
        type="hidden"
        name="_csrf"
        value="<?= e(csrfToken()) ?>"
    >

    <label class="form-label">
        Forma de pagamento
    </label>

    <div class="form-check">

        <input
            class="form-check-input"
            type="radio"
            name="forma_pagamento"
            value="pix"
            id="pix"
            required
        >

        <label class="form-check-label" for="pix">
            PIX
        </label>

    </div>

    <div class="form-check">

        <input
            class="form-check-input"
            type="radio"
            name="forma_pagamento"
            value="boleto"
            id="boleto"
        >

        <label class="form-check-label" for="boleto">
            Boleto
        </label>

    </div>

    <div class="form-check">

        <input
            class="form-check-input"
            type="radio"
            name="forma_pagamento"
            value="cartao"
            id="cartao"
        >

        <label class="form-check-label" for="cartao">
            Cartão
        </label>

    </div>

    <p class="text-muted">
        Pagamento demonstrativo: nenhum valor real será cobrado.
    </p>

    <button class="btn btn-dark">
        CONFIRMAR PEDIDO
    </button>

</form>

<?php require __DIR__ . '/includes/footer.php'; ?>

