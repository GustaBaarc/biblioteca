<#
.SYNOPSIS
    Instala os requisitos e prepara este projeto Laravel no Windows.
.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\instalar-laravel.ps1
.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\instalar-laravel.ps1 -InstalarMySQL
#>
param(
    [switch]$InstalarMySQL
)

$ErrorActionPreference = 'Stop'

function Atualizar-Path {
    $machine = [Environment]::GetEnvironmentVariable('Path', 'Machine')
    $user = [Environment]::GetEnvironmentVariable('Path', 'User')
    $links = Join-Path $env:LOCALAPPDATA 'Microsoft\WinGet\Links'
    $composer = Join-Path $env:APPDATA 'Composer\vendor\bin'
    $env:Path = "$machine;$user;$links;$composer;$env:Path"
}

function Executar {
    param([string]$Programa, [string[]]$Argumentos)
    & $Programa @Argumentos
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao executar $Programa (codigo $LASTEXITCODE)."
    }
}

function Instalar-Pacote {
    param([string]$Id)
    Write-Host "`nInstalando $Id com winget..."
    Executar 'winget' @('install', '--id', $Id, '--exact', '--source', 'winget',
        '--accept-package-agreements', '--accept-source-agreements', '--disable-interactivity')
    Atualizar-Path
}

try {
    Set-Location $PSScriptRoot
    Atualizar-Path
    if (-not (Get-Command winget -ErrorAction SilentlyContinue)) {
        throw 'O winget nao foi encontrado. Instale ou atualize o App Installer pela Microsoft Store e tente novamente.'
    }

    if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
        Instalar-Pacote 'PHP.PHP.8.3'
    }
    $php = Get-Command php -ErrorAction SilentlyContinue
    if (-not $php) { throw 'PHP instalado, mas nao encontrado no PATH. Abra um novo terminal e execute o script novamente.' }
    & php -r 'exit(version_compare(PHP_VERSION, ''8.2.0'', ''>='') && version_compare(PHP_VERSION, ''9.0.0'', ''<'') ? 0 : 1);'
    if ($LASTEXITCODE -ne 0) { throw 'Este projeto precisa de PHP >= 8.2 e < 9. Ajuste o PHP no PATH e tente novamente.' }

    # Os pacotes oficiais do PHP incluem as DLLs, mas algumas extensoes ficam desativadas.
    $phpDir = Split-Path $php.Source
    $ini = Join-Path $phpDir 'php.ini'
    if (-not (Test-Path $ini)) {
        $template = Join-Path $phpDir 'php.ini-development'
        if (-not (Test-Path $template)) { throw "Nao foi encontrado php.ini nem php.ini-development em $phpDir." }
        Copy-Item $template $ini
    }
    $iniContent = Get-Content $ini -Raw
    $iniContent = [regex]::Replace($iniContent, '(?m)^\s*;?\s*extension_dir\s*=\s*"?\.?[/\\]?ext"?\s*$', 'extension_dir = "ext"')
    foreach ($extension in @('curl', 'fileinfo', 'mbstring', 'openssl', 'pdo_mysql', 'pdo_sqlite', 'sqlite3', 'zip')) {
        $pattern = "(?m)^\s*;?\s*extension\s*=\s*(?:php_)?$extension(?:\.dll)?\s*$"
        if ($iniContent -match $pattern) {
            $iniContent = [regex]::Replace($iniContent, $pattern, "extension=$extension")
        } else {
            $iniContent += "`r`nextension=$extension"
        }
    }
    Set-Content -Path $ini -Value $iniContent -Encoding ASCII
    Executar 'php' @('-r', 'foreach ([''curl'',''dom'',''fileinfo'',''mbstring'',''openssl'',''pdo_mysql'',''pdo_sqlite'',''sqlite3'',''xml'',''zip''] as $e) { if (!extension_loaded($e)) { fwrite(STDERR, ''Extensao PHP ausente: '' . $e . PHP_EOL); exit(1); } }')

    $composerLocal = Join-Path $env:LOCALAPPDATA 'LaravelTools\composer.phar'
    $composerCommand = Get-Command composer -ErrorAction SilentlyContinue
    if (-not $composerCommand -and -not (Test-Path $composerLocal)) {
        $composerDir = Split-Path $composerLocal
        New-Item -ItemType Directory -Path $composerDir -Force | Out-Null
        $installer = Join-Path $composerDir 'composer-setup.php'
        Write-Host "`nBaixando o instalador oficial do Composer..."
        try {
            [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
            $respostaHash = (Invoke-WebRequest 'https://composer.github.io/installer.sig' -UseBasicParsing).Content
            if ($respostaHash -is [byte[]]) {
                $hashEsperado = [Text.Encoding]::UTF8.GetString($respostaHash).Trim()
            } else {
                $hashEsperado = ([string]$respostaHash).Trim()
            }
            Invoke-WebRequest 'https://getcomposer.org/installer' -OutFile $installer -UseBasicParsing
            $hashObtido = (Get-FileHash $installer -Algorithm SHA384).Hash.ToLowerInvariant()
            if ($hashObtido -ne $hashEsperado.ToLowerInvariant()) {
                throw 'A assinatura do instalador do Composer nao confere.'
            }
            Executar 'php' @($installer, "--install-dir=$composerDir", '--filename=composer.phar')
        } finally {
            if (Test-Path $installer) { Remove-Item $installer -Force }
        }
    }
    if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
        Instalar-Pacote 'OpenJS.NodeJS.LTS'
    }
    if (-not (Get-Command npm.cmd -ErrorAction SilentlyContinue)) {
        throw 'Node.js/npm nao encontrado no PATH. Abra um novo terminal e execute o script novamente.'
    }
    & node -e 'const [a,b]=process.versions.node.split(''.'').map(Number); process.exit((a>20 || (a===20 && b>=19)) ? 0 : 1)'
    if ($LASTEXITCODE -ne 0) { throw 'O Vite deste projeto precisa de Node.js 20.19+ ou 22.12+. Atualize o Node.js e tente novamente.' }
    & node -e 'const [a,b]=process.versions.node.split(''.'').map(Number); process.exit(a===21 || (a===22 && b<12) ? 1 : 0)'
    if ($LASTEXITCODE -ne 0) { throw 'O Vite deste projeto precisa de Node.js 20.19+ ou 22.12+. Atualize o Node.js e tente novamente.' }

    if ($InstalarMySQL) {
        Write-Host "`nA instalacao do MySQL pode solicitar elevacao e configuracao manual."
        Instalar-Pacote 'Oracle.MySQL'
        Write-Warning 'Configure o servidor, usuario e banco MySQL manualmente. O script nao altera seu .env para MySQL.'
    }

    if (-not (Test-Path '.env')) {
        Copy-Item '.env.example' '.env'
    }
    if ($composerCommand) {
        Executar $composerCommand.Source @('install', '--no-interaction', '--prefer-dist')
    } else {
        Executar 'php' @($composerLocal, 'install', '--no-interaction', '--prefer-dist')
    }
    $envContent = Get-Content '.env' -Raw
    if ($envContent -notmatch '(?m)^\s*APP_KEY\s*=\s*\S+') {
        Executar 'php' @('artisan', 'key:generate', '--no-interaction')
    }
    Executar 'npm.cmd' @('install', '--no-audit', '--no-fund')
    Executar 'npm.cmd' @('run', 'build')

    if ($envContent -match '(?m)^\s*DB_CONNECTION\s*=\s*sqlite\s*$') {
        if (-not (Test-Path 'database/database.sqlite')) {
            New-Item -ItemType File -Path 'database/database.sqlite' | Out-Null
        }
        Write-Host "`nSQLite preparado. Execute php artisan migrate apos conferir o .env."
    } else {
        Write-Host "`nConfigure e confira o banco no .env; depois execute php artisan migrate."
    }
    Write-Host "`nInstalacao concluida. Para iniciar: php artisan migrate; php artisan serve"
    Write-Host 'Para desenvolvimento com Vite, execute npm run dev em outro terminal.'
} catch {
    Write-Error $_
    exit 1
}
