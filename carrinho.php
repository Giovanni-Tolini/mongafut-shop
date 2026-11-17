<?php
require __DIR__ . '/config/bootstrap.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf(); $action=isset($_POST['remover_id']) ? 'remover' : ($_POST['acao'] ?? '');
    $id=filter_var($_POST['remover_id'] ?? ($_POST['produto_id'] ?? 0),FILTER_VALIDATE_INT) ?: 0;
    if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho']=[];
    if ($action==='adicionar' && $id>0) {
        $q=max(1,(int)($_POST['quantidade'] ?? 1)); $s=db()->prepare('SELECT estoque,ativo FROM produtos WHERE id=:id'); $s->execute(['id'=>$id]); $p=$s->fetch();
        if (!$p || !$p['ativo'] || $p['estoque']<1) setFlash('Produto indisponível.','warning');
        else { $new=($_SESSION['carrinho'][$id] ?? 0)+$q; $_SESSION['carrinho'][$id]=min($new,(int)$p['estoque']); setFlash('Produto adicionado à bolsa.'); }
    } elseif ($action==='atualizar') {
        foreach (($_POST['quantidades'] ?? []) as $pid=>$quantity) {
            $pid=(int)$pid; $quantity=(int)$quantity; $s=db()->prepare('SELECT estoque,ativo FROM produtos WHERE id=:id'); $s->execute(['id'=>$pid]); $p=$s->fetch();
            if (!$p || !$p['ativo'] || $quantity<1) unset($_SESSION['carrinho'][$pid]); else $_SESSION['carrinho'][$pid]=min($quantity,(int)$p['estoque']);
        } setFlash('Carrinho atualizado.');
    } elseif ($action==='remover' && $id>0) { unset($_SESSION['carrinho'][$id]); setFlash('Item removido da bolsa.'); }
    redirect('carrinho.php');
}
$cart=$_SESSION['carrinho'] ?? []; $items=[]; $total=0;
if ($cart) { $ids=array_keys($cart); $marks=implode(',',array_fill(0,count($ids),'?')); $s=db()->prepare("SELECT id,nome,preco,estoque,imagem,ativo FROM produtos WHERE id IN ($marks)"); $s->execute($ids); foreach($s->fetchAll() as $p){$q=min((int)$cart[$p['id']],(int)$p['estoque']); if(!$p['ativo']||$q<1){unset($_SESSION['carrinho'][$p['id']]);continue;} $p['quantidade']=$q;$p['subtotal']=$q*(float)$p['preco'];$total+=$p['subtotal'];$items[]=$p;} }
$pageTitle='Sua bolsa'; require __DIR__.'/includes/header.php';
?>
<h1>SUA BOLSA</h1><?php if(!$items): ?><div class="alert alert-secondary">Sua bolsa está vazia. <a href="index.php">Voltar ao catálogo</a></div><?php else: ?><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrfToken())?>"><input type="hidden" name="acao" value="atualizar"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Produto</th><th>Preço</th><th style="width:130px">Quantidade</th><th>Subtotal</th><th></th></tr></thead><tbody><?php foreach($items as $p): ?><tr><td><?=e($p['nome'])?></td><td><?=money($p['preco'])?></td><td><input class="form-control" type="number" min="0" max="<?=$p['estoque']?>" name="quantidades[<?=$p['id']?>]" value="<?=$p['quantidade']?>"></td><td><?=money($p['subtotal'])?></td><td><button class="btn btn-sm btn-outline-danger" type="submit" formaction="carrinho.php" formmethod="post" name="remover_id" value="<?=$p['id']?>">Remover</button></td></tr><?php endforeach; ?></tbody></table></div><button class="btn btn-outline-dark" type="submit">ATUALIZAR QUANTIDADES</button></form><div class="text-end mt-4"><h3>Total: <?=money($total)?></h3><a class="btn btn-dark" href="finalizar.php">CONTINUAR PARA PAGAMENTO</a></div><?php endif; ?>
<?php require __DIR__.'/includes/footer.php'; ?>
