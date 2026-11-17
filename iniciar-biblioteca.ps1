$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
$env:Path = [Environment]::GetEnvironmentVariable('Path', 'Machine') + ';' + [Environment]::GetEnvironmentVariable('Path', 'User') + ';' + $env:Path
$mysql = 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqld.exe'
$dados = Join-Path $env:LOCALAPPDATA 'BibliotecaMySQL\data'
if (-not (Get-Command php -ErrorAction SilentlyContinue)) { throw 'PHP não encontrado. Abra um novo terminal e tente novamente.' }
if (-not (Test-Path $mysql) -or -not (Test-Path $dados)) { throw 'Instância local do MySQL não encontrada. Consulte o README para configurar o seu MySQL.' }
if (-not (Get-NetTCPConnection -LocalPort 3307 -State Listen -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $mysql -ArgumentList @('--no-defaults', '--basedir="C:\Program Files\MySQL\MySQL Server 8.4"', "--datadir=`"$dados`"", '--port=3307', '--bind-address=127.0.0.1', '--mysqlx=0') -WindowStyle Hidden
    $pronto = $false
    for ($i = 0; $i -lt 30; $i++) {
        Start-Sleep -Seconds 1
        if (Get-NetTCPConnection -LocalPort 3307 -State Listen -ErrorAction SilentlyContinue) { $pronto = $true; break }
    }
    if (-not $pronto) { throw 'Não foi possível iniciar o MySQL local na porta 3307.' }
}
php artisan migrate --force
if ($LASTEXITCODE -ne 0) { throw 'Não foi possível conectar ao banco ou executar as migrations.' }
Write-Host 'Abra http://127.0.0.1:8000 no navegador. Para parar o Laravel, pressione Ctrl+C.'
php artisan serve --host=127.0.0.1 --port=8000
