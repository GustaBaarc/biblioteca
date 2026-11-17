# Biblioteca

Para rodar o projeto é necessário ter PHP, Composer e MySQL instalados.

Crie um banco de dados chamado `biblioteca` no MySQL.

Na pasta do projeto, execute:

```
composer install
```

Copie o arquivo `.env.example`, renomeie a cópia para `.env` e coloque nela os dados de acesso ao MySQL.

Depois execute:

```
php artisan key:generate
php artisan migrate
php artisan serve
```

Abra no navegador o endereço mostrado no terminal, normalmente http://127.0.0.1:8000.
