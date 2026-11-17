

<?php
require dirname(__DIR__) . '/config/bootstrap.php';
requireAdmin();

$mensagem = '';
$erro = '';

$categorias = db()->query(
    'SELECT id, nome FROM categorias ORDER BY nome'
)->fetchAll(PDO::FETCH_ASSOC);

$produtoEdicao = null;

/*
|--------------------------------------------------------------------------
| Processamento dos formulários
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['_csrf'] ?? '';

    if (!hash_equals($_SESSION['_csrf'] ?? '', $csrf)) {
        $erro = 'Solicitação inválida. Atualize a página e tente novamente.';
    } else {
        $acao = $_POST['acao'] ?? '';

        $pdo = db();
        $arquivosSalvos = [];
        $arquivosParaExcluir = [];

        try {

            /*
            |--------------------------------------------------------------
            | Excluir produto
            |--------------------------------------------------------------
            */
            if ($acao === 'excluir') {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

                if (!$id || $id < 1) {
                    throw new RuntimeException('Produto inválido.');
                }

                $consulta = $pdo->prepare(
                    'SELECT COUNT(*) FROM pedido_itens WHERE produto_id = :id'
                );
                $consulta->execute(['id' => $id]);

                if ((int) $consulta->fetchColumn() > 0) {
                    throw new RuntimeException(
                        'Este produto já está vinculado a pedidos. Desative-o em vez de excluí-lo.'
                    );
                }

                // Buscar todas as imagens antes de excluir o produto.
                $consulta = $pdo->prepare(
                    'SELECT imagem FROM produtos WHERE id = :id'
                );
                $consulta->execute(['id' => $id]);
                $produto = $consulta->fetch(PDO::FETCH_ASSOC);

                if (!$produto) {
                    throw new RuntimeException('Produto não encontrado.');
                }

                $arquivosParaExcluir[] = $produto['imagem'];

                $consulta = $pdo->prepare(
                    'SELECT imagem FROM produto_imagens WHERE produto_id = :id'
                );
                $consulta->execute(['id' => $id]);

                foreach ($consulta->fetchAll(PDO::FETCH_COLUMN) as $imagem) {
                    $arquivosParaExcluir[] = $imagem;
                }

                // A chave estrangeira remove os registros das fotos.
                $consulta = $pdo->prepare(
                    'DELETE FROM produtos WHERE id = :id'
                );
                $consulta->execute(['id' => $id]);

                $mensagem = 'Produto excluído com sucesso.';
            }

            /*
            |--------------------------------------------------------------
            | Ativar ou desativar produto
            |--------------------------------------------------------------
            */
            elseif ($acao === 'alternar_status') {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

                if (!$id || $id < 1) {
                    throw new RuntimeException('Produto inválido.');
                }

                $consulta = $pdo->prepare(
                    'UPDATE produtos
                     SET ativo = IF(ativo = 1, 0, 1)
                     WHERE id = :id'
                );
                $consulta->execute(['id' => $id]);

                if ($consulta->rowCount() === 0) {
                    $verifica = $pdo->prepare(
                        'SELECT id FROM produtos WHERE id = :id'
                    );
                    $verifica->execute(['id' => $id]);

                    if (!$verifica->fetch()) {
                        throw new RuntimeException('Produto não encontrado.');
                    }
                }

                $mensagem = 'Status do produto atualizado.';
            }

            /*
            |--------------------------------------------------------------
            | Excluir uma imagem individual
            |--------------------------------------------------------------
            */
            elseif ($acao === 'excluir_imagem') {
                $produtoId = filter_input(
                    INPUT_POST,
                    'produto_id',
                    FILTER_VALIDATE_INT
                );

                $imagemId = filter_input(
                    INPUT_POST,
                    'imagem_id',
                    FILTER_VALIDATE_INT
                );

                if (!$produtoId || !$imagemId) {
                    throw new RuntimeException('Imagem ou produto inválido.');
                }

                $pdo->beginTransaction();

                $consulta = $pdo->prepare(
                    'SELECT imagem
                     FROM produto_imagens
                     WHERE id = :imagem_id
                       AND produto_id = :produto_id'
                );

                $consulta->execute([
                    'imagem_id' => $imagemId,
                    'produto_id' => $produtoId
                ]);

                $imagem = $consulta->fetch(PDO::FETCH_ASSOC);

                if (!$imagem) {
                    throw new RuntimeException('Imagem não encontrada.');
                }

                $consulta = $pdo->prepare(
                    'DELETE FROM produto_imagens
                     WHERE id = :imagem_id
                       AND produto_id = :produto_id'
                );

                $consulta->execute([
                    'imagem_id' => $imagemId,
                    'produto_id' => $produtoId
                ]);

                // Buscar a próxima imagem para ser a principal.
                $consulta = $pdo->prepare(
                    'SELECT imagem
                     FROM produto_imagens
                     WHERE produto_id = :produto_id
                     ORDER BY ordem ASC, id ASC
                     LIMIT 1'
                );

                $consulta->execute(['produto_id' => $produtoId]);

                $proximaImagem = $consulta->fetchColumn();

                $imagemPrincipal = $proximaImagem ?: 'sem-imagem.svg';

                $consulta = $pdo->prepare(
                    'UPDATE produtos
                     SET imagem = :imagem
                     WHERE id = :id'
                );

                $consulta->execute([
                    'imagem' => $imagemPrincipal,
                    'id' => $produtoId
                ]);

                $pdo->commit();

                $arquivosParaExcluir[] = $imagem['imagem'];

                $mensagem = 'Imagem excluída com sucesso.';
            }

            /*
            |--------------------------------------------------------------
            | Cadastrar ou editar produto
            |--------------------------------------------------------------
            */
            elseif ($acao === 'salvar') {

                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

                $nome = trim($_POST['nome'] ?? '');

                $categoriaId = filter_input(
                    INPUT_POST,
                    'categoria_id',
                    FILTER_VALIDATE_INT
                );

                $descricao = trim($_POST['descricao'] ?? '');

                $preco = str_replace(
                    ',',
                    '.',
                    trim($_POST['preco'] ?? '')
                );

                $estoque = filter_input(
                    INPUT_POST,
                    'estoque',
                    FILTER_VALIDATE_INT
                );

                // Validar informações do produto.
                if ($nome === '') {
                    throw new RuntimeException('Informe o nome do produto.');
                }

                if (!$categoriaId || $categoriaId < 1) {
                    throw new RuntimeException('Selecione uma categoria.');
                }

                if (!is_numeric($preco) || (float) $preco <= 0) {
                    throw new RuntimeException('Informe um preço válido.');
                }

                if ($estoque === false || $estoque === null || $estoque < 0) {
                    throw new RuntimeException('Informe um estoque válido.');
                }

                $consulta = $pdo->prepare(
                    'SELECT id FROM categorias WHERE id = :id'
                );

                $consulta->execute(['id' => $categoriaId]);

                if (!$consulta->fetch()) {
                    throw new RuntimeException('Categoria não encontrada.');
                }

                // Iniciar transação.
                $pdo->beginTransaction();

                /*
                |----------------------------------------------------------
                | Atualizar ou cadastrar produto
                |----------------------------------------------------------
                */

                if ($id && $id > 0) {

                    $consulta = $pdo->prepare(
                        'SELECT imagem FROM produtos WHERE id = :id'
                    );

                    $consulta->execute(['id' => $id]);

                    $produtoAtual = $consulta->fetch(PDO::FETCH_ASSOC);

                    if (!$produtoAtual) {
                        throw new RuntimeException('Produto não encontrado.');
                    }

                    $imagemPrincipal = $produtoAtual['imagem']
                        ?: 'sem-imagem.svg';

                    $consulta = $pdo->prepare(
                        'UPDATE produtos
                         SET categoria_id = :categoria_id,
                             nome = :nome,
                             descricao = :descricao,
                             preco = :preco,
                             estoque = :estoque
                         WHERE id = :id'
                    );

                    $consulta->execute([
                        'categoria_id' => $categoriaId,
                        'nome' => $nome,
                        'descricao' => $descricao,
                        'preco' => $preco,
                        'estoque' => $estoque,
                        'id' => $id
                    ]);

                    $produtoId = $id;

                } else {

                    $imagemPrincipal = 'sem-imagem.svg';

                    $consulta = $pdo->prepare(
                        'INSERT INTO produtos
                         (categoria_id, nome, descricao, preco, estoque, imagem)
                         VALUES
                         (:categoria_id, :nome, :descricao, :preco, :estoque, :imagem)'
                    );

                    $consulta->execute([
                        'categoria_id' => $categoriaId,
                        'nome' => $nome,
                        'descricao' => $descricao,
                        'preco' => $preco,
                        'estoque' => $estoque,
                        'imagem' => $imagemPrincipal
                    ]);

                    $produtoId = (int) $pdo->lastInsertId();
                }

                /*
                |----------------------------------------------------------
                | Preparar envio de várias imagens
                |----------------------------------------------------------
                */

                $diretorio = dirname(__DIR__)
                    . '/assets/img/produtos/';

                if (!is_dir($diretorio)) {
                    if (!mkdir($diretorio, 0755, true)) {
                        throw new RuntimeException(
                            'Não foi possível criar a pasta de imagens.'
                        );
                    }
                }

                $tiposPermitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                $finfo = new finfo(FILEINFO_MIME_TYPE);

                // Descobrir a próxima posição das imagens.
                $consulta = $pdo->prepare(
                    'SELECT COALESCE(MAX(ordem), 0)
                     FROM produto_imagens
                     WHERE produto_id = :produto_id'
                );

                $consulta->execute(['produto_id' => $produtoId]);

                $ordem = (int) $consulta->fetchColumn() + 1;

                /*
                |----------------------------------------------------------
                | Processar cada imagem enviada
                |----------------------------------------------------------
                */

                if (
                    isset($_FILES['imagens']) &&
                    is_array($_FILES['imagens']['name'])
                ) {

                    $totalArquivos = count($_FILES['imagens']['name']);

                    for ($i = 0; $i < $totalArquivos; $i++) {

                        $erroUpload = $_FILES['imagens']['error'][$i];

                        // Nenhum arquivo selecionado.
                        if ($erroUpload === UPLOAD_ERR_NO_FILE) {
                            continue;
                        }

                        if ($erroUpload !== UPLOAD_ERR_OK) {
                            throw new RuntimeException(
                                'Ocorreu um erro ao enviar uma das imagens.'
                            );
                        }

                        $arquivoTemporario = $_FILES['imagens']['tmp_name'][$i];

                        $tamanho = (int) $_FILES['imagens']['size'][$i];

                        if ($tamanho > 3 * 1024 * 1024) {
                            throw new RuntimeException(
                                'Cada imagem deve ter no máximo 3 MB.'
                            );
                        }

                        $tipo = $finfo->file($arquivoTemporario);

                        if (!isset($tiposPermitidos[$tipo])) {
                            throw new RuntimeException(
                                'Formato inválido. Use JPG, PNG ou WEBP.'
                            );
                        }

                        $nomeArquivo = bin2hex(random_bytes(16))
                            . '.' . $tiposPermitidos[$tipo];

                        if (!move_uploaded_file(
                            $arquivoTemporario,
                            $diretorio . $nomeArquivo
                        )) {
                            throw new RuntimeException(
                                'Não foi possível salvar uma das imagens.'
                            );
                        }

                        // Guardar o arquivo para eventual limpeza em caso de erro.
                        $arquivosSalvos[] = $diretorio . $nomeArquivo;

                        // Registrar imagem no banco.
                        $consulta = $pdo->prepare(
                            'INSERT INTO produto_imagens
                             (produto_id, imagem, ordem)
                             VALUES
                             (:produto_id, :imagem, :ordem)'
                        );

                        $consulta->execute([
                            'produto_id' => $produtoId,
                            'imagem' => $nomeArquivo,
                            'ordem' => $ordem
                        ]);

                        // A primeira foto será a imagem principal.
                        if (
                            $imagemPrincipal === 'sem-imagem.svg' ||
                            empty($imagemPrincipal)
                        ) {
                            $imagemPrincipal = $nomeArquivo;
                        }

                        $ordem++;
                    }
                }

                /*
                |----------------------------------------------------------
                | Atualizar imagem principal do produto
                |----------------------------------------------------------
                */

                $consulta = $pdo->prepare(
                    'UPDATE produtos
                     SET imagem = :imagem
                     WHERE id = :id'
                );

                $consulta->execute([
                    'imagem' => $imagemPrincipal,
                    'id' => $produtoId
                ]);

                $pdo->commit();

                $mensagem = $id
                    ? 'Produto atualizado com sucesso.'
                    : 'Produto cadastrado com sucesso.';
            }

            else {
                throw new RuntimeException('Ação não reconhecida.');
            }

        } catch (RuntimeException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Remover arquivos enviados se a operação falhar.
            foreach ($arquivosSalvos as $arquivo) {
                if (is_file($arquivo)) {
                    unlink($arquivo);
                }
            }

            $erro = $e->getMessage();

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            foreach ($arquivosSalvos as $arquivo) {
                if (is_file($arquivo)) {
                    unlink($arquivo);
                }
            }

            $erro = 'Não foi possível realizar a operação no banco de dados.';
        }

        // Excluir arquivos físicos após a operação no banco.
        $diretorioImagens = dirname(__DIR__) . '/assets/img/produtos/';

        foreach (array_unique($arquivosParaExcluir) as $arquivo) {

            if (
                $arquivo &&
                $arquivo !== 'sem-imagem.svg' &&
                basename($arquivo) === $arquivo
            ) {
                $caminho = $diretorioImagens . $arquivo;

                if (is_file($caminho)) {
                    unlink($caminho);
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Redirecionamento
    |--------------------------------------------------------------------------
    */

    $_SESSION['admin_mensagem'] = $mensagem;
    $_SESSION['admin_erro'] = $erro;

    header('Location: /mongafut_shop_v1/admin/produtos.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Recuperar mensagens
|--------------------------------------------------------------------------
*/

$mensagem = $_SESSION['admin_mensagem'] ?? '';
$erro = $_SESSION['admin_erro'] ?? '';

unset($_SESSION['admin_mensagem'], $_SESSION['admin_erro']);

/*
|--------------------------------------------------------------------------
| Carregar produto para edição
|--------------------------------------------------------------------------
*/

$imagensEdicao = [];

if (isset($_GET['editar'])) {

    $idEdicao = filter_input(
        INPUT_GET,
        'editar',
        FILTER_VALIDATE_INT
    );

    if ($idEdicao && $idEdicao > 0) {

        $consulta = db()->prepare(
            'SELECT * FROM produtos WHERE id = :id'
        );

        $consulta->execute(['id' => $idEdicao]);

        $produtoEdicao = $consulta->fetch(PDO::FETCH_ASSOC) ?: null;

        if ($produtoEdicao) {

            $consulta = db()->prepare(
                'SELECT id, imagem, ordem
                 FROM produto_imagens
                 WHERE produto_id = :produto_id
                 ORDER BY ordem ASC, id ASC'
            );

            $consulta->execute([
                'produto_id' => $idEdicao
            ]);

            $imagensEdicao = $consulta->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

/*
|--------------------------------------------------------------------------
| Listar produtos
|--------------------------------------------------------------------------
*/

$produtos = db()->query(
    'SELECT
        p.*,
        c.nome AS categoria_nome,
        (
            SELECT COUNT(*)
            FROM produto_imagens pi
            WHERE pi.produto_id = p.id
        ) AS total_imagens
     FROM produtos p
     INNER JOIN categorias c ON c.id = p.categoria_id
     ORDER BY p.id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Gerenciar produtos';

require dirname(__DIR__) . '/includes/header.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="h3">Gerenciar produtos</h1>

        <a
            href="/mongafut_shop_v1/admin/index.php"
            class="btn btn-outline-secondary"
        >
            Voltar ao painel
        </a>

    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-success">
            <?= e($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <?= e($erro) ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de cadastro e edição -->

    <div class="card mb-4">

        <div class="card-body">

            <h2 class="h5 mb-3">
                <?= $produtoEdicao ? 'Editar produto' : 'Cadastrar produto' ?>
            </h2>

            <form
                method="POST"
                action="/mongafut_shop_v1/admin/produtos.php"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="_csrf"
                    value="<?= e($_SESSION['_csrf']) ?>"
                >

                <input type="hidden" name="acao" value="salvar">

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) ($produtoEdicao['id'] ?? 0) ?>"
                >

                <div class="mb-3">

                    <label class="form-label">Nome do produto</label>

                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        required
                        value="<?= e($produtoEdicao['nome'] ?? '') ?>"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">Categoria</label>

                    <select
                        name="categoria_id"
                        class="form-select"
                        required
                    >

                        <option value="">Selecione uma categoria</option>

                        <?php foreach ($categorias as $categoria): ?>

                            <option
                                value="<?= (int) $categoria['id'] ?>"
                                <?= (
                                    (int) ($produtoEdicao['categoria_id'] ?? 0)
                                    === (int) $categoria['id']
                                ) ? 'selected' : '' ?>
                            >
                                <?= e($categoria['nome']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="mb-3">

                    <label class="form-label">Descrição</label>

                    <textarea
                        name="descricao"
                        class="form-control"
                        rows="3"
                    ><?= e($produtoEdicao['descricao'] ?? '') ?></textarea>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Preço (R$)</label>

                        <input
                            type="number"
                            name="preco"
                            class="form-control"
                            min="0.01"
                            step="0.01"
                            required
                            value="<?= e(
                                isset($produtoEdicao['preco'])
                                    ? (string) $produtoEdicao['preco']
                                    : ''
                            ) ?>"
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Estoque</label>

                        <input
                            type="number"
                            name="estoque"
                            class="form-control"
                            min="0"
                            step="1"
                            required
                            value="<?= e(
                                isset($produtoEdicao['estoque'])
                                    ? (string) $produtoEdicao['estoque']
                                    : '0'
                            ) ?>"
                        >

                    </div>

                </div>

                <!-- Seleção de várias imagens -->

                <div class="mb-3">

                    <label class="form-label">
                        Imagens do produto
                    </label>

                    <input
                        type="file"
                        name="imagens[]"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple
                    >

                    <small class="text-muted">
                        Você pode selecionar várias imagens de uma vez.
                        Formatos permitidos: JPG, PNG e WEBP.
                        Limite de 3 MB por imagem.
                    </small>

                </div>

                <button type="submit" class="btn btn-dark">

                    <?= $produtoEdicao
                        ? 'Salvar alterações'
                        : 'Cadastrar produto'
                    ?>

                </button>

                <?php if ($produtoEdicao): ?>

                    <a
                        href="/mongafut_shop_v1/admin/produtos.php"
                        class="btn btn-outline-secondary"
                    >
                        Cancelar edição
                    </a>

                <?php endif; ?>

            </form>

            <!-- Imagens já cadastradas -->

            <?php if ($produtoEdicao): ?>

                <hr class="my-4">

                <h3 class="h6 mb-3">
                    Fotos cadastradas
                </h3>

                <?php if (empty($imagensEdicao)): ?>

                    <p class="text-muted">
                        Este produto ainda não possui fotos cadastradas.
                    </p>

                <?php else: ?>

                    <div class="row g-3">

                        <?php foreach ($imagensEdicao as $imagem): ?>

                            <div class="col-6 col-md-3">

                                <div class="border rounded p-2 h-100">

                                    <img
                                        src="/mongafut_shop_v1/assets/img/produtos/<?= e($imagem['imagem']) ?>"
                                        alt="Foto do produto"
                                        class="img-fluid rounded mb-2"
                                        style="width: 100%; height: 130px; object-fit: contain;"
                                    >

                                    <small class="d-block text-muted mb-2">
                                        <?= (int) $imagem['ordem'] === 1
                                            ? 'Imagem principal'
                                            : 'Imagem ' . (int) $imagem['ordem']
                                        ?>
                                    </small>

                                    <form
                                        method="POST"
                                        action="/mongafut_shop_v1/admin/produtos.php"
                                        onsubmit="return confirm('Deseja excluir esta imagem?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="_csrf"
                                            value="<?= e($_SESSION['_csrf']) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="excluir_imagem"
                                        >

                                        <input
                                            type="hidden"
                                            name="produto_id"
                                            value="<?= (int) $produtoEdicao['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="imagem_id"
                                            value="<?= (int) $imagem['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger w-100"
                                        >
                                            Excluir foto
                                        </button>

                                    </form>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </div>

    </div>

    <!-- Tabela de produtos -->

    <div class="card">

        <div class="card-body">

            <h2 class="h5 mb-3">
                Produtos cadastrados
            </h2>

            <div class="table-responsive">

                <table class="table table-striped align-middle">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Fotos</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($produtos)): ?>

                            <tr>
                                <td colspan="8" class="text-center">
                                    Nenhum produto cadastrado.
                                </td>
                            </tr>

                        <?php endif; ?>

                        <?php foreach ($produtos as $produto): ?>

                            <tr>

                                <td>
                                    <?= (int) $produto['id'] ?>
                                </td>

                                <td>
                                    <?= e($produto['nome']) ?>
                                </td>

                                <td>
                                    <?= e($produto['categoria_nome']) ?>
                                </td>

                                <td>
                                    R$ <?= number_format(
                                        (float) $produto['preco'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $produto['estoque'] ?>
                                </td>

                                <td>
                                    <?= (int) $produto['total_imagens'] ?>
                                </td>

                                <td>

                                    <?php if ($produto['ativo']): ?>

                                        <span class="badge bg-success">
                                            Ativo
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inativo
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="d-flex flex-wrap gap-2">

                                        <a
                                            href="/mongafut_shop_v1/admin/produtos.php?editar=<?= (int) $produto['id'] ?>"
                                            class="btn btn-sm btn-primary"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            method="POST"
                                            action="/mongafut_shop_v1/admin/produtos.php"
                                        >

                                            <input
                                                type="hidden"
                                                name="_csrf"
                                                value="<?= e($_SESSION['_csrf']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="acao"
                                                value="alternar_status"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $produto['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-warning"
                                            >
                                                <?= $produto['ativo']
                                                    ? 'Desativar'
                                                    : 'Ativar'
                                                ?>
                                            </button>

                                        </form>

                                        <form
                                            method="POST"
                                            action="/mongafut_shop_v1/admin/produtos.php"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este produto?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="_csrf"
                                                value="<?= e($_SESSION['_csrf']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="acao"
                                                value="excluir"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $produto['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                            >
                                                Excluir
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

