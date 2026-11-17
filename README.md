# Sistema de gerenciamento de biblioteca

Projeto acadêmico em Laravel 12, MySQL, Blade e Bootstrap. Permite cadastrar livros e leitores, controlar empréstimos e devoluções, pesquisar registros e acompanhar indicadores. A quantidade de cada livro representa os exemplares **disponíveis**. Empréstimos e devoluções usam transações; cadastros com histórico de empréstimos não podem ser excluídos.

## Executar neste computador

No PowerShell, dentro da pasta `biblioteca`, execute:

```powershell
powershell -ExecutionPolicy Bypass -File .\iniciar-biblioteca.ps1
```

Abra `http://127.0.0.1:8000`. O script inicia, se necessário, a instância local de MySQL criada para o projeto na porta 3307 e depois inicia o Laravel. Para encerrar o Laravel, use Ctrl+C. Os dados locais do MySQL ficam em `%LOCALAPPDATA%\BibliotecaMySQL\data` e não vão para o repositório.

## Configuração em outro computador

Requisitos: PHP 8.2 ou superior com as extensões exigidas pelo Laravel, incluindo `pdo_mysql`, Composer e MySQL. O Bootstrap é carregado via CDN, portanto a interface precisa de internet.

1. Crie um banco vazio chamado `biblioteca` no MySQL.
2. Execute `composer install` na pasta do projeto.
3. Copie `.env.example` para `.env` e configure os campos `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`.
4. Execute `php artisan key:generate` e `php artisan migrate`.
5. Execute `php artisan serve` e acesse o endereço informado no terminal.

Para executar os testes automatizados: `php artisan test`. A configuração padrão de testes usa SQLite em memória e não altera o banco MySQL.

Não publique o arquivo `.env` ou credenciais. Esta aplicação é didática e não possui autenticação, por isso não deve ser exposta publicamente sem controle de acesso. Para a atividade, capture telas do sistema em funcionamento e inclua-as na entrega, conforme solicitado no tutorial.
