-- Mongafut Shop
-- MariaDB 10.4+ / MySQL 8+
-- Estrutura e dados iniciais para ambiente de teste.
-- Execute com o banco de dados mongafut_shop (ou teste mongafut) selecionado.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Categorias: precisa existir antes de produtos.
CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Usuários: precisa existir antes de pedidos.
CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    tipo ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Produtos.
CREATE TABLE IF NOT EXISTS produtos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT UNSIGNED NOT NULL DEFAULT 0,
    imagem VARCHAR(255) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_produtos_categoria (categoria_id),
    CONSTRAINT fk_produtos_categorias
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Pedidos.
CREATE TABLE IF NOT EXISTS pedidos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    data_pedido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    forma_pagamento ENUM('pix','boleto','cartao') NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_pedidos_usuario (usuario_id),
    CONSTRAINT fk_pedidos_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Itens dos pedidos.
CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pedido_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NOT NULL,
    quantidade INT UNSIGNED NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_itens_pedido (pedido_id),
    KEY idx_itens_produto (produto_id),
    CONSTRAINT fk_itens_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_itens_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Galeria de imagens dos produtos.
CREATE TABLE IF NOT EXISTS produto_imagens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    produto_id INT UNSIGNED NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    ordem INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_produto_imagens (produto_id),
    CONSTRAINT fk_produto_imagens_produtos
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorias utilizadas pelo projeto.
INSERT IGNORE INTO categorias (id, nome, descricao) VALUES
(1, 'Roupas', 'Camisas, bermudas e conjuntos de treino'),
(2, 'Chuteiras de campo', 'Chuteiras próprias para futebol de campo'),
(3, 'Chuteiras society', 'Chuteiras próprias para futebol society'),
(4, 'Chuteiras de futsal', 'Chuteiras próprias para futsal'),
(5, 'Bolas de campo', 'Bolas próprias para futebol de campo'),
(6, 'Bolas society', 'Bolas próprias para futebol society'),
(7, 'Bolas de futsal', 'Bolas próprias para futsal'),
(8, 'Acessórios de perna', 'Meias, meiões e caneleiras'),
(9, 'Acessórios', 'Manguitos, munhequeiras e faixas de cabeça');

-- As seis chuteiras existentes no banco enviado.
-- Os arquivos de imagem devem existir em assets/img/produtos/.
INSERT IGNORE INTO produtos
(id, categoria_id, nome, descricao, preco, estoque, imagem, ativo, criado_em) VALUES
(2, 2, 'Nike Mercurial Vapor 17 Pro',
 'Chuteira Nike Mercurial Vapor 17 Pro desenvolvida para futebol em gramados naturais. Seu design prioriza velocidade, agilidade e controle durante as jogadas, sendo indicada para jogadores que buscam movimentação rápida em campo.',
 679.90, 10, 'assets/img/produtos/15eacf41f5739384c96f21868d1b3288.png', 1, '2026-11-14 05:13:21'),
(3, 2, 'Chuteira adidas Campo F50 League',
 'Chuteira adidas F50 League desenvolvida para partidas em gramados naturais. A linha F50 é voltada à velocidade e à agilidade, com construção pensada para auxiliar nas arrancadas e mudanças rápidas de direção.',
 449.90, 15, 'assets/img/produtos/bb4f45cda484614f24f1cb42f28456a7.png', 1, '2026-11-14 05:15:48'),
(4, 3, 'Chuteira Society adidas Predator Club',
 'Chuteira adidas Predator Club desenvolvida para partidas em gramados sintéticos. Seu cabedal apresenta elementos texturizados que auxiliam no contato com a bola, oferecendo controle durante passes, conduções e finalizações.',
 399.90, 9, 'assets/img/produtos/6089db028d938754c48de1f5e5f2ad11.png', 1, '2026-11-14 05:16:44'),
(5, 3, 'Chuteira Society Nike Tiempo Maestro Club',
 'Chuteira Nike Tiempo Maestro Club desenvolvida para partidas em gramados sintéticos. Possui cabedal sintético e construção voltada ao conforto e ao controle da bola durante as jogadas.',
 329.90, 11, 'assets/img/produtos/a318ac7cd87f8e71ef38eec3e375cdd8.png', 1, '2026-11-14 05:17:45'),
(6, 4, 'Masculino Chuteira Futsal Nike Beco 2',
 'Chuteira Nike Beco 2 desenvolvida para a prática de futsal em quadras. Possui solado de borracha adequado para superfícies internas, oferecendo aderência e estabilidade durante movimentações e mudanças de direção.',
 189.90, 10, 'assets/img/produtos/c336a9d4772cb4ac71bce94be125c33b.png', 1, '2026-11-14 05:18:45'),
(7, 4, 'Chuteira Predator Club Sala adidas',
 'Chuteira adidas Predator Club Sala desenvolvida para partidas de futsal em quadras. Seu design é voltado ao controle da bola e à precisão dos movimentos, com solado apropriado para superfícies internas.',
 219.90, 7, 'assets/img/produtos/651e857891a9998c368289b3f47f5dbe.png', 1, '2026-11-14 05:19:54');

-- Galeria de imagens existente no banco enviado.
INSERT IGNORE INTO produto_imagens (id, produto_id, imagem, ordem) VALUES
(1, 2, 'assets/img/produtos/15eacf41f5739384c96f21868d1b3288.png', 1),
(2, 3, 'assets/img/produtos/bb4f45cda484614f24f1cb42f28456a7.png', 1),
(3, 4, 'assets/img/produtos/6089db028d938754c48de1f5e5f2ad11.png', 1),
(4, 5, 'assets/img/produtos/a318ac7cd87f8e71ef38eec3e375cdd8.png', 1),
(5, 6, 'assets/img/produtos/c336a9d4772cb4ac71bce94be125c33b.png', 1),
(6, 7, 'assets/img/produtos/651e857891a9998c368289b3f47f5dbe.png', 1),
(8, 4, 'assets/img/produtos/d9767180c3cd6d226666ca5d23fc5465.png', 2),
(9, 4, 'assets/img/produtos/73a71c1260a1838710f78c400c6475f8.png', 3),
(10, 4, 'assets/img/produtos/1df8c08b62ad11c90bf41532ba6183f1.png', 4),
(11, 4, 'assets/img/produtos/6645e06d835b38706e93bae64111eb90.png', 5);

-- Não são inseridos usuários nem pedidos pessoais do banco original.
-- Para tornar uma conta cadastrada administradora, execute:
-- UPDATE usuarios SET tipo = 'admin' WHERE email = 'SEU_EMAIL_DE_CADASTRO';
