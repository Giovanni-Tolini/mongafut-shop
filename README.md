# Mongafut Shop

O Mongafut Shop é um site de vendas de artigos esportivos voltados ao futebol de campo, society e futsal. O projeto foi desenvolvido como atividade do 4º bimestre, com o objetivo de aplicar os conhecimentos de desenvolvimento web e integração com banco de dados.

## Tecnologias

O sistema foi desenvolvido utilizando HTML, CSS, Bootstrap e JavaScript na parte visual, PHP no processamento das funcionalidades e MySQL para o armazenamento dos dados. A conexão com o banco de dados é realizada por meio do PDO.

## Funcionalidades

O site possui cadastro e login de usuários, catálogo de produtos com pesquisa e filtros por categoria, página de detalhes dos produtos e carrinho de compras. Também permite alterar quantidades, verificar a disponibilidade em estoque e finalizar pedidos com simulação de pagamento por PIX, boleto ou cartão.

Além da área do cliente, o sistema conta com uma área administrativa para cadastrar, editar e gerenciar produtos, atualizar estoques e consultar os pedidos realizados.

## Banco de dados

O banco de dados utiliza tabelas para armazenar usuários, categorias, produtos, imagens, pedidos e itens dos pedidos. O arquivo `schema.sql` contém a estrutura necessária para a criação do banco e sua configuração inicial.

## Como executar

O projeto pode ser executado localmente utilizando o XAMPP ou outro ambiente compatível com PHP e MySQL.

1. Coloque a pasta do projeto no diretório `htdocs` do XAMPP.
2. Inicie os serviços Apache e MySQL.
3. Crie um banco de dados no MySQL e importe o arquivo `schema.sql`.
4. Configure os dados de conexão no arquivo de configuração do projeto.
5. Acesse `http://localhost/mongafut_shop_v1/` pelo navegador.

## Segurança

O sistema utiliza consultas preparadas com PDO, armazenamento de senhas com `password_hash()`, verificação com `password_verify()` e controle de acesso por sessões. Também são realizadas validações no servidor para proteger as operações da aplicação.

## Informações do projeto

**Projeto:** Mongafut Shop  
**Atividade:** Challenge – 4º Bimestre  
