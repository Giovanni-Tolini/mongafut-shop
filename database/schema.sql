-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/11/2026 às 00:15
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mongafut_shop`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `descricao`, `criado_em`) VALUES
(1, 'Roupas', 'Camisas, bermudas e conjuntos de treino', '2026-11-14 03:00:14'),
(2, 'Chuteiras de campo', 'Chuteiras próprias para futebol de campo', '2026-11-14 03:00:14'),
(3, 'Chuteiras society', 'Chuteiras próprias para futebol society', '2026-11-14 03:00:14'),
(4, 'Chuteiras de futsal', 'Chuteiras próprias para futsal', '2026-11-14 03:00:14'),
(5, 'Bolas de campo', 'Bolas próprias para futebol de campo', '2026-11-14 03:13:36'),
(6, 'Bolas society', 'Bolas próprias para futebol society', '2026-11-14 03:13:36'),
(7, 'Bolas de futsal', 'Bolas próprias para futsal', '2026-11-14 03:13:36'),
(8, 'Acessórios de perna', 'Meias, meiões e caneleiras', '2026-11-14 03:13:36'),
(9, 'Acessórios', 'Manguitos, munhequeiras e faixas de cabeça', '2026-11-14 03:13:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `forma_pagamento` enum('pix','boleto','cartao') NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `data_pedido`, `forma_pagamento`, `total`) VALUES
(1, 1, '2026-11-14 05:31:37', 'pix', 449.90),
(2, 1, '2026-11-17 04:06:38', 'pix', 379.80),
(3, 2, '2026-11-17 05:16:53', 'boleto', 679.90),
(4, 2, '2026-11-17 05:17:07', 'cartao', 679.90),
(5, 2, '2026-11-17 22:41:39', 'boleto', 1349.70),
(6, 1, '2026-11-18 01:54:20', 'pix', 679.90);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_itens`
--

CREATE TABLE `pedido_itens` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `produto_id` int(10) UNSIGNED NOT NULL,
  `quantidade` int(10) UNSIGNED NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pedido_itens`
--

INSERT INTO `pedido_itens` (`id`, `pedido_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
(1, 1, 3, 1, 449.90),
(2, 2, 6, 2, 189.90),
(3, 3, 2, 1, 679.90),
(4, 4, 2, 1, 679.90),
(5, 5, 3, 3, 449.90),
(6, 6, 2, 1, 679.90);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(10) UNSIGNED NOT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `estoque` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `imagem` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `categoria_id`, `nome`, `descricao`, `preco`, `estoque`, `imagem`, `ativo`, `criado_em`) VALUES
(2, 2, 'Nike Mercurial Vapor 17 Pro', 'Chuteira Nike Mercurial Vapor 17 Pro desenvolvida para futebol em gramados naturais. Seu design prioriza velocidade, agilidade e controle durante as jogadas, sendo indicada para jogadores que buscam movimentação rápida em campo.', 679.90, 7, '6d9af6656feddc00f5a5797e7bd73d5c.png', 1, '2026-11-14 05:13:21'),
(3, 2, 'Chuteira adidas Campo F50 League', 'Chuteira adidas F50 League desenvolvida para partidas em gramados naturais. A linha F50 é voltada à velocidade e à agilidade, com construção pensada para auxiliar nas arrancadas e mudanças rápidas de direção.', 449.90, 12, '0d40bbf1d02c5fa1fb4ab07de74812fc.png', 1, '2026-11-14 05:15:48'),
(4, 3, 'Chuteira Society adidas Predator Club', 'Chuteira adidas Predator Club desenvolvida para partidas em gramados sintéticos. Seu cabedal apresenta elementos texturizados que auxiliam no contato com a bola, oferecendo controle durante passes, conduções e finalizações.', 399.90, 9, 'ec67cbc745ed94bdc1f31f070ab62d21.png', 1, '2026-11-14 05:16:44'),
(5, 3, 'Chuteira Society Nike Tiempo Maestro Club', 'Chuteira Nike Tiempo Maestro Club desenvolvida para partidas em gramados sintéticos. Possui cabedal sintético e construção voltada ao conforto e ao controle da bola durante as jogadas.', 329.90, 11, '644152a83b32e311aebe64f607a4c5ec.png', 1, '2026-11-14 05:17:45'),
(6, 4, 'Masculino Chuteira Futsal Nike Beco 2', 'Chuteira Nike Beco 2 desenvolvida para a prática de futsal em quadras. Possui solado de borracha adequado para superfícies internas, oferecendo aderência e estabilidade durante movimentações e mudanças de direção.', 189.90, 10, '5cf8c4148b7c1cb79bf61ca4234ce7f3.png', 1, '2026-11-14 05:18:45'),
(7, 4, 'Chuteira Predator Club Sala adidas', 'Chuteira adidas Predator Club Sala desenvolvida para partidas de futsal em quadras. Seu design é voltado ao controle da bola e à precisão dos movimentos, com solado apropriado para superfícies internas.', 219.90, 7, 'dd461b38c4bc1ca0075c3009b625024d.png', 1, '2026-11-14 05:19:54'),
(8, 8, 'Nike Academy Football Socks', 'Meiao Nike Academy desenvolvido para futebol, com tecido confortável, ajuste firme e suporte durante os movimentos em campo.', 49.90, 20, '115e0d981d53e88b8cde2001bcff7192.png', 1, '2026-11-18 00:21:55'),
(9, 8, 'adidas Milano 23 Socks', 'Meiao adidas Milano 23 com construção esportiva, ajuste confortável e suporte para os pés e pernas durante os treinos e partidas.', 44.90, 18, '4b69309d2dc92d0019f35bddd5e3cd29.png', 1, '2026-11-18 00:21:55'),
(10, 8, 'Penalty Meia Futsal Grip', 'Meia esportiva Penalty para futsal, desenvolvida para proporcionar conforto e estabilidade durante os movimentos rápidos nas quadras.', 34.90, 20, '35c317ee2d831e9d6501e9f3d040f59b.png', 1, '2026-11-18 00:21:55'),
(11, 8, 'Nike Charge J Guards', 'Caneleira Nike Charge desenvolvida para proteção durante partidas de futebol, com estrutura resistente e formato anatômico.', 89.90, 12, '9a9a873349b336682f1abb908d6c1ac6.png', 1, '2026-11-18 00:21:55'),
(12, 5, 'adidas UCL Club Campo', 'Bola adidas UCL Club para futebol de campo, com design inspirado na UEFA Champions League e construção voltada para treinos e partidas.', 149.90, 10, '6e5a8708d58c8cb292f89285e2521c07.png', 1, '2026-11-18 00:21:55'),
(13, 6, 'Penalty Bravo 8X Society', 'Bola Penalty Bravo 8X desenvolvida para futebol society, com construção apropriada para partidas em gramados sintéticos.', 119.90, 12, '9c2c8a77f617aa69a0c3a278c1b25edf.png', 1, '2026-11-18 00:21:55'),
(14, 7, 'Nike Futsal Pro', 'Bola Nike para futsal, desenvolvida para partidas em quadras, com construção voltada ao controle e à precisão dos passes.', 179.90, 8, 'c34dac85b130b3b195e0dcb4e83c691d.png', 1, '2026-11-18 00:21:55'),
(15, 1, 'Nike Dri-FIT Academy Shorts', 'Bermuda esportiva desenvolvida para treinos de futebol. Possui tecnologia Dri-FIT, que ajuda a absorver o suor e manter o corpo seco, tecido leve de poliéster, cós elástico com cordão ajustável e bolsos laterais.', 99.90, 13, 'e577a81476d901ff92301bab248fea12.png', 1, '2026-11-18 00:21:55'),
(16, 1, 'Camiseta Nike Dri-FIT Academy Masculina', 'Camiseta esportiva desenvolvida para treinos de futebol. Possui tecnologia Nike Dri-FIT, que ajuda a absorver o suor e manter o corpo seco e confortável. Conta com tecido leve e respirável, painéis laterais em tela para melhorar a ventilação e logotipo Nike bordado no peito.', 99.90, 15, '833b2ac1f85a27f87e71db2b18abce08.png', 1, '2026-11-18 00:21:55'),
(18, 9, 'adidas Tennis Wristband', 'Munhequeira esportiva adidas desenvolvida para auxiliar na absorção do suor durante atividades físicas e partidas.', 39.90, 20, '0c2cb02f991c6cf0ef0bbbdb392e086b.png', 1, '2026-11-18 00:21:55'),
(19, 9, 'Nike Swoosh Headband', 'Faixa de cabeça Nike com design esportivo, desenvolvida para ajudar a manter o suor afastado do rosto durante os exercícios.', 49.90, 20, 'd0aba569402e04f190f72e8df9960f1f.png', 1, '2026-11-18 00:21:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_imagens`
--

CREATE TABLE `produto_imagens` (
  `id` int(10) UNSIGNED NOT NULL,
  `produto_id` int(10) UNSIGNED NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `ordem` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produto_imagens`
--

INSERT INTO `produto_imagens` (`id`, `produto_id`, `imagem`, `ordem`) VALUES
(19, 19, 'd0aba569402e04f190f72e8df9960f1f.png', 1),
(20, 18, '0c2cb02f991c6cf0ef0bbbdb392e086b.png', 1),
(21, 18, '7481c983a28d883eb8180a7fb0c227c9.png', 2),
(22, 18, 'e6eaa3ddfe624f526f5291daca4affed.png', 3),
(23, 18, '093b446ac31b1baebdf7963dedfc70b6.png', 4),
(24, 16, '833b2ac1f85a27f87e71db2b18abce08.png', 1),
(25, 16, '604b3618bdf6a216cddd66f71cd33017.png', 2),
(26, 15, 'e577a81476d901ff92301bab248fea12.png', 1),
(27, 15, '6c6a695bd274e8f0aec2cf5d799a0c95.png', 2),
(28, 15, '4e27e3937d12a1491a09fa6cb1250da1.png', 3),
(29, 14, 'c34dac85b130b3b195e0dcb4e83c691d.png', 1),
(30, 14, 'b972a0b554c317ffb9de846528752326.png', 2),
(31, 14, 'bc8bf475c570653b9f9f09b4b9506933.png', 3),
(32, 14, '9ae6c788ffcc7b01fdc9a24d9c7b38b4.png', 4),
(33, 13, '9c2c8a77f617aa69a0c3a278c1b25edf.png', 1),
(34, 13, 'f76739f44c29dfb3d3f2297e06a3da3d.png', 2),
(35, 13, '88fb20197e113453e164c290a62a6731.png', 3),
(36, 12, '6e5a8708d58c8cb292f89285e2521c07.png', 1),
(37, 12, 'f74f6470326b1bb1cc52699b436ce918.png', 2),
(38, 12, 'e283efd4967b913bdc311771f02f1e44.png', 3),
(39, 11, '9a9a873349b336682f1abb908d6c1ac6.png', 1),
(40, 10, '35c317ee2d831e9d6501e9f3d040f59b.png', 1),
(41, 10, 'f1241096b000153cce591399e28386e5.png', 2),
(42, 10, 'b0707d0a79540cc1c8798448f691737c.png', 3),
(43, 9, '4b69309d2dc92d0019f35bddd5e3cd29.png', 1),
(44, 9, 'c2c4e85475279c8657090eb47547705f.png', 2),
(45, 9, '670372cab6751999b24428865d90d7c7.png', 3),
(46, 8, '115e0d981d53e88b8cde2001bcff7192.png', 1),
(47, 8, '0c2a16cdbc2bb74c088d370e181b7ade.png', 2),
(48, 8, '255940db805505e5c79afc8c38c4562b.png', 3),
(49, 8, '3afb506b26e400ae663f3335837dce85.png', 4),
(50, 7, 'dd461b38c4bc1ca0075c3009b625024d.png', 1),
(51, 7, '64d94b718d1cc69a710d4caa1e89a6f9.png', 2),
(52, 7, '5d7497bf884e0880dbb1faf860d488ba.png', 3),
(53, 7, '96665f10964f59d2bf360eca467ec147.png', 4),
(54, 7, 'a196751fb7080e4d1b1342efbe81067d.png', 5),
(55, 6, '5cf8c4148b7c1cb79bf61ca4234ce7f3.png', 1),
(56, 6, '3ed8d863f7ebd4f5a79e59a1ad88b21a.png', 2),
(57, 6, '40917c39ef7283e68618fddafcb2ca48.png', 3),
(58, 6, 'bdcf797d12f1726437ac28bf3f614d15.png', 4),
(59, 6, 'c6309de66bdb617a48debb8ecdeda6d6.png', 5),
(60, 6, 'b30d751791555ff5d62cc7bc06e1f85a.png', 6),
(61, 5, '644152a83b32e311aebe64f607a4c5ec.png', 1),
(62, 5, 'be6e2fdf7f843ee6bb28004f1610d936.png', 2),
(63, 5, 'b114ff447b45ce7130cf8c4859716bff.png', 3),
(64, 5, '071bca0c9a867d9ce4a5f83550dccce6.png', 4),
(65, 5, '855711ac44efa654072872b8b27c11c6.png', 5),
(66, 5, 'ab57d9fdb67c42c0a56a8c8d5a667789.png', 6),
(67, 4, 'ec67cbc745ed94bdc1f31f070ab62d21.png', 1),
(68, 4, 'ccaaefdc4fe8f92351246612e2a3ef38.png', 2),
(69, 4, '660bbb82f30e9bc1bdb6a3cb2b2a94aa.png', 3),
(70, 4, '8ade66acc20e7556fe96d67098aea310.png', 4),
(71, 4, 'a8f970f5cfa8f818e855ea125f9e732a.png', 5),
(72, 4, 'e5e31341818e24245f311d38b54f0518.png', 6),
(73, 3, '0d40bbf1d02c5fa1fb4ab07de74812fc.png', 1),
(74, 3, 'c0557c4e505e5083abd4802861064bf4.png', 2),
(75, 3, 'b9d1bd87c949f428cbee1fde9ca3c0aa.png', 3),
(76, 3, 'e6da2213f5d70f2d48995a50ed055a8a.png', 4),
(77, 3, '751f7df0b723ccc6121aa139a0837f97.png', 5),
(79, 2, '6d9af6656feddc00f5a5797e7bd73d5c.png', 1),
(80, 2, 'b2a7521db0edfd17ba9b95e436151c4c.png', 2),
(81, 2, '0fc4bfe913ef2654f2c7ca8a69aa03ae.png', 3),
(82, 2, '078625200343b4fe0775915e324e1aeb.png', 4),
(83, 2, 'e2527df53f79082114295790a55dcc2b.png', 5),
(84, 2, '471fa25b09d75d6a8334f955abc55afb.png', 6);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `tipo` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `tipo`, `criado_em`) VALUES
(1, 'giovanni tolini', 'giovanniantunes.tolini@gmail.com', '$2y$10$0acsEyyJA9Z/.dTmXQXrSO7TLXyh0SAxgf6ymeu6l0Gedg7MUZ4xO', 'admin', '2026-11-14 03:45:59'),
(2, 'a', '11naoseioquecolocar69@gmail.com', '$2y$10$n2kpZaxyqcHaGpXjDRHpduPee0vdjKK6ZmTvv2VqfO5Y5G7dkEnES', 'cliente', '2026-11-17 04:55:36');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pedidos_usuarios` (`usuario_id`);

--
-- Índices de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_itens_pedido` (`pedido_id`),
  ADD KEY `fk_itens_produto` (`produto_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produtos_categorias` (`categoria_id`);

--
-- Índices de tabela `produto_imagens`
--
ALTER TABLE `produto_imagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produto_imagens` (`produto_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `produto_imagens`
--
ALTER TABLE `produto_imagens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD CONSTRAINT `fk_itens_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `fk_produtos_categorias` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `produto_imagens`
--
ALTER TABLE `produto_imagens`
  ADD CONSTRAINT `fk_produto_imagens_produtos` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
