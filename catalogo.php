
<?php
require __DIR__ . '/config/bootstrap.php';

$pdo = db();

/*
|--------------------------------------------------------------------------
| CATEGORIAS
|--------------------------------------------------------------------------
*/

$categories = $pdo->query(
    'SELECT id, nome FROM categorias ORDER BY nome'
)->fetchAll();

$categoryNames = [];

foreach ($categories as $category) {
    $categoryNames[(int)$category['id']] = $category['nome'];
}

/*
|--------------------------------------------------------------------------
| SEÇÃO E FILTROS
|--------------------------------------------------------------------------
*/

$section = $_GET['secao'] ?? '';

$validSections = ['todos', 'chuteiras', 'acessorios'];

$categoryId = filter_input(
    INPUT_GET,
    'categoria',
    FILTER_VALIDATE_INT
) ?: 0;

if (!in_array($section, $validSections, true)) {
    if (in_array($categoryId, [2, 3, 4], true)) {
        $section = 'chuteiras';
    } elseif (in_array($categoryId, [1, 5, 6, 7, 8, 9], true)) {
        $section = 'acessorios';
    } else {
        $section = 'todos';
    }
}

$search = trim((string)($_GET['busca'] ?? ''));


/*
|--------------------------------------------------------------------------
| INTERPRETAÇÃO INTELIGENTE DA BUSCA
|--------------------------------------------------------------------------
*/

$normalizedSearch = strtolower($search);

// Normalizar acentos para facilitar a identificação das palavras
$normalizedSearch = strtr($normalizedSearch, [
    'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
    'é' => 'e', 'ê' => 'e',
    'í' => 'i',
    'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
    'ú' => 'u',
    'ç' => 'c'
]);

$semanticCategories = [];

// Identificar modalidades
$isCampo = preg_match('/\bcampo\b/', $normalizedSearch);
$isSociety = preg_match('/\bsociety\b/', $normalizedSearch);
$isFutsal = preg_match('/\bfutsal\b/', $normalizedSearch);

// Identificar tipos de produto
$isChuteira = preg_match('/\b(chuteira|chuteiras)\b/', $normalizedSearch);
$isBola = preg_match('/\b(bola|bolas)\b/', $normalizedSearch);

$isRoupa = preg_match(
    '/\b(roupa|roupas|camisa|camiseta|bermuda|conjunto)\b/',
    $normalizedSearch
);

$isAcessorioPerna = preg_match(
    '/\b(meia|meias|caneleira|caneleiras)\b/',
    $normalizedSearch
);

$isAcessorio = preg_match(
    '/\b(acessorio|acessorios|munhequeira|munhequeiras|faixa|faixas)\b/',
    $normalizedSearch
);

// Chuteiras por modalidade
if ($isChuteira) {
    if ($isCampo) {
        $semanticCategories = [2];
    } elseif ($isSociety) {
        $semanticCategories = [3];
    } elseif ($isFutsal) {
        $semanticCategories = [4];
    } else {
        $semanticCategories = [2, 3, 4];
    }
}

// Bolas por modalidade
elseif ($isBola) {
    if ($isCampo) {
        $semanticCategories = [5];
    } elseif ($isSociety) {
        $semanticCategories = [6];
    } elseif ($isFutsal) {
        $semanticCategories = [7];
    } else {
        $semanticCategories = [5, 6, 7];
    }
}

// Roupas
elseif ($isRoupa) {
    $semanticCategories = [1];
}

// Acessórios de perna
elseif ($isAcessorioPerna) {
    $semanticCategories = [8];
}

// Outros acessórios
elseif ($isAcessorio) {
    $semanticCategories = [8, 9];
}

// Separar palavras importantes da pesquisa
$searchWords = preg_split(
    '/[^a-z0-9]+/',
    $normalizedSearch,
    -1,
    PREG_SPLIT_NO_EMPTY
);

// Palavras que não precisam ser pesquisadas
$ignoredWords = [
    'de', 'da', 'do', 'das', 'dos',
    'para', 'com', 'por', 'em', 'um', 'uma',
    'o', 'a', 'os', 'as', 'e'
];

// Quando uma categoria já foi identificada,
// não precisamos procurar novamente seus termos.
if (!empty($semanticCategories)) {
    $ignoredWords = array_merge($ignoredWords, [
        'chuteira', 'chuteiras',
        'bola', 'bolas',
        'campo', 'society', 'futsal',
        'roupa', 'roupas',
        'camisa', 'camiseta', 'bermuda', 'conjunto',
        'meia', 'meias', 'caneleira', 'caneleiras',
        'acessorio', 'acessorios',
        'munhequeira', 'munhequeiras',
        'faixa', 'faixas'
    ]);
}

$searchWords = array_values(array_unique(array_filter(
    $searchWords,
    function ($word) use ($ignoredWords) {
        return strlen($word) > 1 &&
            !in_array($word, $ignoredWords, true);
    }
)));


$shoeCategories = [2, 3, 4];
$accessoryCategories = [1, 5, 6, 7, 8, 9];

if ($section === 'chuteiras') {
    $allowedCategories = $shoeCategories;
} elseif ($section === 'acessorios') {
    $allowedCategories = $accessoryCategories;
} else {
    $allowedCategories = [];
}

if (
    $categoryId > 0 &&
    !empty($allowedCategories) &&
    !in_array($categoryId, $allowedCategories, true)
) {
    $categoryId = 0;
}

/*
|--------------------------------------------------------------------------
| GRUPOS DE FILTROS
|--------------------------------------------------------------------------
*/

if ($section === 'chuteiras') {
    $filterGroups = [
        'CHUTEIRAS' => [2, 3, 4]
    ];
} elseif ($section === 'acessorios') {
    $filterGroups = [
        'ROUPAS' => [1],
        'ACESSÓRIOS DE PERNA' => [8],
        'ACESSÓRIOS' => [9],
        'BOLAS' => [5, 6, 7]
    ];
} else {
    $filterGroups = [
        'CHUTEIRAS' => [2, 3, 4],
        'ROUPAS' => [1],
        'ACESSÓRIOS DE PERNA' => [8],
        'ACESSÓRIOS' => [9],
        'BOLAS' => [5, 6, 7]
    ];
}

/*
|--------------------------------------------------------------------------
| CONSULTA DOS PRODUTOS
|--------------------------------------------------------------------------
*/

$sql = '
    SELECT
        p.*,
        c.nome AS categoria_nome
    FROM produtos p
    INNER JOIN categorias c ON c.id = p.categoria_id
    WHERE p.ativo = 1
';

$params = [];

if (!empty($allowedCategories)) {
    $placeholders = [];

    foreach ($allowedCategories as $index => $id) {
        $placeholder = ':secao' . $index;
        $placeholders[] = $placeholder;
        $params[$placeholder] = $id;
    }

    $sql .= ' AND p.categoria_id IN (' .
        implode(', ', $placeholders) . ')';
}

if ($categoryId > 0) {
    $sql .= ' AND p.categoria_id = :categoria';
    $params[':categoria'] = $categoryId;
}

// Categorias identificadas pela pesquisa
if (!empty($semanticCategories)) {
    $placeholders = [];

    foreach ($semanticCategories as $index => $id) {
        $placeholder = ':busca_categoria_semantica' . $index;

        $placeholders[] = $placeholder;
        $params[$placeholder] = $id;
    }

    $sql .= ' AND p.categoria_id IN (' .
        implode(', ', $placeholders) . ')';
}

// Pesquisar cada palavra importante
foreach ($searchWords as $index => $word) {
    $placeholderNome = ':busca_nome_' . $index;
    $placeholderDescricao = ':busca_desc_' . $index;
    $placeholderCategoria = ':busca_categoria_' . $index;

    $sql .= '
        AND (
            p.nome LIKE ' . $placeholderNome . '
            OR p.descricao LIKE ' . $placeholderDescricao . '
            OR c.nome LIKE ' . $placeholderCategoria . '
        )
    ';

    $term = '%' . $word . '%';

    $params[$placeholderNome] = $term;
    $params[$placeholderDescricao] = $term;
    $params[$placeholderCategoria] = $term;
}

$sql .= ' ORDER BY p.criado_em DESC, p.nome';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| TÍTULOS
|--------------------------------------------------------------------------
*/

if ($section === 'chuteiras') {
    $pageTitle = 'Chuteiras';
    $heading = 'CHUTEIRAS PARA CADA JOGO.';
    $description = 'Encontre o equipamento ideal para campo, society e futsal.';
    $sectionLabel = 'CALÇADOS / PERFORMANCE';
} elseif ($section === 'acessorios') {
    $pageTitle = 'Acessórios';
    $heading = 'EQUIPAMENTO COMPLETO.';
    $description = 'Tudo o que acompanha você dentro e fora de campo.';
    $sectionLabel = 'VESTUÁRIO / ACESSÓRIOS';
} else {
    $pageTitle = 'Catálogo de produtos';
    $heading = 'ENCONTRE SEU EQUIPAMENTO.';
    $description = 'Explore nossa seleção de produtos para todos os tipos de jogo.';
    $sectionLabel = 'CATÁLOGO / COLEÇÃO COMPLETA';
}

$selectedCategoryName = $categoryId > 0
    ? ($categoryNames[$categoryId] ?? '')
    : '';

$clearParams = [];

if ($section !== 'todos') {
    $clearParams['secao'] = $section;
}

if ($search !== '') {
    $clearParams['busca'] = $search;
}

$clearUrl = 'catalogo.php' . (
    !empty($clearParams)
        ? '?' . http_build_query($clearParams)
        : ''
);

require __DIR__ . '/includes/header.php';
?>

<section class="shop-catalog">

    <!-- APRESENTAÇÃO -->

    <header class="shop-catalog-hero">

        <div class="shop-catalog-hero-copy">
            <span class="shop-catalog-eyebrow">
                MONGAFUT SHOP <i></i> <?= e($sectionLabel) ?>
            </span>

            <h1><?= e($heading) ?></h1>

            <p><?= e($description) ?></p>
        </div>

        <div class="shop-catalog-edition">
            <span>MF / 026</span>
            <strong>PERFORMANCE</strong>
            <small>SEU JOGO. SUA IDENTIDADE.</small>
        </div>

        <div class="shop-catalog-hero-number">01</div>

    </header>


    <!-- ÁREA DE BUSCA E FILTROS -->

    <section class="shop-catalog-controls">

        <div class="shop-catalog-controls-top">

            <div>
                <span class="shop-catalog-section-label">ENCONTRE O QUE PROCURA</span>
                <h2>EXPLORE A COLEÇÃO<span>.</span></h2>
            </div>

            <span class="shop-catalog-control-index">
                FILTROS / 2026
            </span>

        </div>

        <div class="shop-catalog-control-grid">

            <!-- BUSCA -->

            <form method="get" class="shop-catalog-search">

                <input
                    type="hidden"
                    name="secao"
                    value="<?= e($section) ?>"
                >

                <?php if ($categoryId > 0): ?>
                    <input
                        type="hidden"
                        name="categoria"
                        value="<?= (int)$categoryId ?>"
                    >
                <?php endif; ?>

                <label for="catalog-busca">BUSCAR PRODUTO</label>

                <div class="shop-catalog-search-field">

                    <input
                        type="search"
                        id="catalog-busca"
                        name="busca"
                        value="<?= e($search) ?>"
                        placeholder="Nome do produto..."
                    >

                    <button type="submit" aria-label="Buscar produtos">
                        ↗
                    </button>

                </div>

            </form>


            <!-- FILTRO -->

            <form method="get" class="shop-catalog-filter">

                <input
                    type="hidden"
                    name="secao"
                    value="<?= e($section) ?>"
                >

                <?php if ($search !== ''): ?>
                    <input
                        type="hidden"
                        name="busca"
                        value="<?= e($search) ?>"
                    >
                <?php endif; ?>

                <label for="categoria">CATEGORIA</label>

                <div class="shop-catalog-filter-field">

                    <select name="categoria" id="categoria">

                        <?php if ($section === 'chuteiras'): ?>
                            <option value="0">Todas as chuteiras</option>
                        <?php elseif ($section === 'acessorios'): ?>
                            <option value="0">Todos os acessórios</option>
                        <?php else: ?>
                            <option value="0">Todas as categorias</option>
                        <?php endif; ?>

                        <?php foreach ($filterGroups as $groupName => $ids): ?>

                            <optgroup label="<?= e($groupName) ?>">

                                <?php foreach ($ids as $id): ?>

                                    <?php if (isset($categoryNames[$id])): ?>

                                        <option
                                            value="<?= (int)$id ?>"
                                            <?= $categoryId === $id ? 'selected' : '' ?>
                                        >
                                            <?= e($categoryNames[$id]) ?>
                                        </option>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </optgroup>

                        <?php endforeach; ?>

                    </select>

                    <span class="shop-catalog-select-arrow">⌄</span>

                </div>

                <button type="submit" class="shop-catalog-apply">
                    APLICAR FILTRO <span>↗</span>
                </button>

            </form>

        </div>

        <?php if ($categoryId > 0 || $search !== ''): ?>

            <div class="shop-catalog-active-filters">

                <span>FILTROS ATIVOS:</span>

                <?php if ($categoryId > 0): ?>
                    <span class="shop-catalog-filter-tag">
                        <?= e($selectedCategoryName) ?>
                    </span>
                <?php endif; ?>

                <?php if ($search !== ''): ?>
                    <span class="shop-catalog-filter-tag">
                        BUSCA: <?= e($search) ?>
                    </span>
                <?php endif; ?>

                <a href="<?= e($clearUrl) ?>">LIMPAR FILTROS ×</a>

            </div>

        <?php endif; ?>

    </section>


    <!-- RESULTADOS -->

    <section class="shop-catalog-results">

        <div class="shop-catalog-results-heading">

            <div>
                <span class="shop-catalog-section-label">MONGAFUT / SELEÇÃO</span>

                <h2>
                    PRODUTOS <span>DISPONÍVEIS.</span>
                </h2>
            </div>

            <div class="shop-catalog-count">
                <strong><?= count($products) ?></strong>
                <span>PRODUTO(S)<br>ENCONTRADO(S)</span>
            </div>

        </div>


        <?php if (!$products): ?>

            <div class="shop-catalog-empty">

                <span>MF / 00</span>

                <h3>NENHUM PRODUTO ENCONTRADO.</h3>

                <p>
                    Não encontramos resultados para essa seleção.
                    Experimente mudar a busca ou a categoria.
                </p>

                <a href="<?= e($clearUrl) ?>">
                    LIMPAR FILTROS <span>↗</span>
                </a>

            </div>

        <?php else: ?>

            <div class="shop-catalog-grid">

                <?php foreach ($products as $index => $p): ?>

                    <article class="shop-product-card">

                        <a
                            class="shop-product-photo"
                            href="produto.php?id=<?= (int)$p['id'] ?>"
                        >

                            <span class="shop-product-number">
                                <?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?>
                            </span>

                            <span class="shop-product-category">
                                <?= e($p['categoria_nome']) ?>
                            </span>

                            <img
                                src="<?= e(
                                    !empty($p['imagem'])
                                        ? 'assets/img/produtos/' . $p['imagem']
                                        : 'assets/img/produtos/sem-imagem.svg'
                                ) ?>"
                                alt="<?= e($p['nome']) ?>"
                                loading="lazy"
                            >

                            <span class="shop-product-view">VER DETALHES ↗</span>

                        </a>

                        <div class="shop-product-info">

                            <div class="shop-product-stock <?= (int)$p['estoque'] > 0 ? 'is-available' : 'is-soldout' ?>">
                                <i></i>
                                <?= (int)$p['estoque'] > 0 ? 'DISPONÍVEL' : 'ESGOTADO' ?>
                            </div>

                            <h3><?= e($p['nome']) ?></h3>

                            <div class="shop-product-bottom">

                                <strong><?= money($p['preco']) ?></strong>

                                <a
                                    href="produto.php?id=<?= (int)$p['id'] ?>"
                                    aria-label="Ver <?= e($p['nome']) ?>"
                                >
                                    ↗
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
