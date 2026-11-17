
<?php
require dirname(__DIR__) . '/config/bootstrap.php';

requireAdmin();


$products = (int) db()->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
$orders = (int) db()->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();

$pageTitle = 'Painel administrativo';

require dirname(__DIR__) . '/includes/header.php';
?>

<h1>PAINEL ADMINISTRATIVO</h1>

<p>Gerencie o catálogo e acompanhe os pedidos da loja.</p>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-body">
            <small>PRODUTOS CADASTRADOS</small>
            <h2><?= $products ?></h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-body">
            <small>PEDIDOS REGISTRADOS</small>
            <h2><?= $orders ?></h2>
        </div>
    </div>
</div>

<a class="btn btn-dark me-2"
   href="http://localhost/mongafut_shop_v1/admin/produtos.php">
    GERENCIAR PRODUTOS
</a>

<a class="btn btn-outline-dark"
   href="http://localhost/mongafut_shop_v1/admin/pedidos.php">
    VER PEDIDOS
</a>

<?php
require dirname(__DIR__) . '/includes/footer.php';
?>

