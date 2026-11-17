# Mongafut Shop

Loja virtual acadêmica de artigos para futebol de campo, society e futsal. Projeto desenvolvido com HTML5, CSS3, Bootstrap 5, JavaScript, PHP e MySQL/MariaDB via PDO.

## Requisitos
- XAMPP com Apache, PHP 8.0+ e MySQL/MariaDB.
- Navegador atualizado.

## Instalação local
1. Copie a pasta do projeto para `C:\xampp\htdocs\mongafut_shop_v1`.
2. No phpMyAdmin, crie o banco `mongafut_shop` com codificação `utf8mb4`.
3. Selecione o banco e importe `database/schema.sql`.
4. Copie `.env.example` para `.env` e configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS` de acordo com seu ambiente local.
5. Inicie Apache e MySQL no XAMPP.
6. Acesse `http://localhost/mongafut_shop_v1/`.

## Criar o primeiro administrador
1. Cadastre uma conta normalmente pela opção **Cadastrar**.
2. No phpMyAdmin, selecione `mongafut_shop` e execute, substituindo pelo e-mail usado no cadastro:

```sql
UPDATE usuarios SET tipo = 'admin' WHERE email = 'seu-email@exemplo.com';
```

3. Saia e entre novamente. O menu **Administração** ficará disponível. O acesso às páginas em `/admin` também é validado no servidor.

## Funcionalidades implementadas
- Catálogo dinâmico com produtos e categorias vindos do MySQL.
- Pesquisa e filtro por categoria.
- Página individual com descrição, preço, imagem e disponibilidade.
- Cadastro e login com `password_hash()` e `password_verify()`.
- Carrinho armazenado em `$_SESSION`, com alteração de quantidade, remoção e validação de estoque.
- Checkout demonstrativo (PIX, boleto ou cartão), criação de pedido e itens, baixa de estoque e limpeza do carrinho.
- Área `/admin` protegida por sessão e perfil administrativo.
- Cadastro, listagem, edição e ativação/desativação de produtos; upload de imagens JPG, PNG ou WEBP.
- Listagem administrativa de pedidos com seus itens e valores.
- Consultas SQL com PDO e prepared statements; token CSRF nos formulários de alteração.

## Observações
- O pagamento é simulado; não há cobrança real nem integração com instituição financeira.
- O banco começa com as nove categorias definidas para a loja, sem produtos fictícios. Cadastre produtos reais pelo painel administrativo depois de confirmar modelo, descrição e imagem.
- `.env` contém credenciais locais e não deve ser enviado ao Git. O modelo `.env.example` deve permanecer no repositório.
- O repositório remoto e o histórico de commits devem ser mantidos pelo aluno/equipe conforme a etapa de versionamento da atividade.
