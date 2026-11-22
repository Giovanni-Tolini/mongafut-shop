
<?php

require __DIR__ . '/config/bootstrap.php';

requireLogin();

// Recuperar o pedido confirmado
$id = (int) ($_SESSION['pedido_confirmado'] ?? 0);

if (!$id) {
    redirect('index.php');
}

unset($_SESSION['pedido_confirmado']);

// Buscar pedido pertencente ao usuário
$stmt = db()->prepare('
    SELECT id, data_pedido, forma_pagamento, total
    FROM pedidos
    WHERE id = :id
      AND usuario_id = :usuario
');

$stmt->execute([
    'id' => $id,
    'usuario' => currentUser()['id']
]);

$order = $stmt->fetch();

if (!$order) {
    redirect('index.php');
}

$pageTitle = 'Pedido confirmado';

require __DIR__ . '/includes/header.php';

?>

<div class="alert alert-success">

    <h1>Pedido confirmado!</h1>

    <p class="mb-0">
        Seu pedido demonstrativo nº <?= (int) $order['id'] ?>
        foi registrado com sucesso.
    </p>

</div>

<p>
    <strong>Data:</strong>
    <?= e($order['data_pedido']) ?>
</p>

<p>
    <strong>Pagamento:</strong>
    <?= e(strtoupper($order['forma_pagamento'])) ?>
</p>

<h3>
    Total: <?= money($order['total']) ?>
</h3>

<a href="index.php" class="btn btn-dark">
    VOLTAR À LOJA
</a>

<?php require __DIR__ . '/includes/footer.php'; ?>
