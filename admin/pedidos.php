
<?php

require dirname(__DIR__) . '/config/bootstrap.php';

requireAdmin();

// Buscar pedidos realizados
$orders = db()->query('
    SELECT
        p.id,
        p.data_pedido,
        p.forma_pagamento,
        p.total,
        u.nome,
        u.email
    FROM pedidos p
    JOIN usuarios u ON u.id = p.usuario_id
    ORDER BY p.id DESC
')->fetchAll();

$pageTitle = 'Pedidos';

require dirname(__DIR__) . '/includes/header.php';

?>

<h1>PEDIDOS REALIZADOS</h1>

<?php if (!$orders): ?>

    <div class="alert alert-secondary">
        Ainda não existem pedidos.
    </div>

<?php else: ?>

    <div class="accordion" id="orders">

        <?php foreach ($orders as $i => $o): ?>

            <div class="accordion-item">

                <h2 class="accordion-header">

                    <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#pedido<?= $o['id'] ?>"
                    >
                        Pedido #<?= $o['id'] ?>
                        — <?= e($o['nome']) ?>
                        — <?= money($o['total']) ?>
                    </button>

                </h2>

                <div
                    id="pedido<?= $o['id'] ?>"
                    class="accordion-collapse collapse"
                    data-bs-parent="#orders"
                >

                    <div class="accordion-body">

                        <p>
                            <?= e($o['email']) ?>
                            · <?= e($o['data_pedido']) ?>
                            · <?= e(strtoupper($o['forma_pagamento'])) ?>
                        </p>

                        <?php

                        $s = db()->prepare('
                            SELECT
                                pi.quantidade,
                                pi.preco_unitario,
                                pr.nome
                            FROM pedido_itens pi
                            JOIN produtos pr ON pr.id = pi.produto_id
                            WHERE pi.pedido_id = :id
                        ');

                        $s->execute([
                            'id' => $o['id']
                        ]);

                        $items = $s->fetchAll();

                        ?>

                        <ul>

                            <?php foreach ($items as $item): ?>

                                <li>
                                    <?= e($item['nome']) ?>
                                    — <?= $item['quantidade'] ?>
                                    × <?= money($item['preco_unitario']) ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

