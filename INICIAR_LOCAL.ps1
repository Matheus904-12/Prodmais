# ========================================
# INICIAR SEM DOCKER (LOCAL)
# ========================================
# Use este script se nao quiser usar Docker

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   PRODMAIS - MODO LOCAL (SEM DOCKER)" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$allOk = $true

# Verificar PHP
Write-Host "[*] Verificando PHP..." -ForegroundColor Yellow
try {
    $phpVersion = php --version 2>$null
    if ($phpVersion) {
        Write-Host "   [OK] PHP encontrado" -ForegroundColor Green
    } else {
        Write-Host "   [X] PHP nao encontrado!" -ForegroundColor Red
        Write-Host "      Instale o PHP 8.0+ primeiro" -ForegroundColor Yellow
        $allOk = $false
    }
} catch {
    Write-Host "   [X] PHP nao encontrado!" -ForegroundColor Red
    $allOk = $false
}

# Verificar MySQL
Write-Host "[*] Verificando MySQL..." -ForegroundColor Yellow
try {
    $mysqlStatus = Get-Service mysql -ErrorAction SilentlyContinue
    if ($mysqlStatus -and $mysqlStatus.Status -eq "Running") {
        Write-Host "   [OK] MySQL esta rodando" -ForegroundColor Green
    } else {
        Write-Host "   [!] MySQL nao esta rodando" -ForegroundColor Yellow
        Write-Host "      Tentando iniciar MySQL..." -ForegroundColor Cyan
        Start-Service mysql -ErrorAction SilentlyContinue
        Start-Sleep -Seconds 3
        $mysqlStatus = Get-Service mysql -ErrorAction SilentlyContinue
        if ($mysqlStatus -and $mysqlStatus.Status -eq "Running") {
            Write-Host "   [OK] MySQL iniciado com sucesso" -ForegroundColor Green
        } else {
            Write-Host "   [X] Nao foi possivel iniciar MySQL" -ForegroundColor Red
            $allOk = $false
        }
    }
} catch {
    Write-Host "   [X] MySQL nao encontrado!" -ForegroundColor Red
    $allOk = $false
}

# Verificar/Iniciar Elasticsearch
Write-Host "[*] Verificando Elasticsearch..." -ForegroundColor Yellow

$esPath = "C:\elasticsearch-8.10.0"
$esRunning = $false

try {
    $esCheck = Invoke-WebRequest -Uri "http://localhost:9200" -TimeoutSec 2 -UseBasicParsing 2>$null
    if ($esCheck.StatusCode -eq 200) {
        Write-Host "   [OK] Elasticsearch ja esta rodando" -ForegroundColor Green
        $esRunning = $true
    }
} catch {
    Write-Host "   [!] Elasticsearch nao esta rodando" -ForegroundColor Yellow
    
    if (Test-Path $esPath) {
        Write-Host "   [*] Iniciando Elasticsearch..." -ForegroundColor Cyan
        
        # Configurar elasticsearch.yml
        $configFile = "$esPath\config\elasticsearch.yml"
        $config = @"
cluster.name: prodmais-umc
node.name: node-1
path.data: $esPath\data
path.logs: $esPath\logs
network.host: localhost
http.port: 9200
discovery.type: single-node
xpack.security.enabled: false
xpack.security.http.ssl.enabled: false
"@
        Set-Content -Path $configFile -Value $config -Encoding UTF8
        
        # Criar diretórios
        New-Item -ItemType Directory -Path "$esPath\data" -Force | Out-Null
        New-Item -ItemType Directory -Path "$esPath\logs" -Force | Out-Null
        
        # Iniciar Elasticsearch
        Start-Process -FilePath "$esPath\bin\elasticsearch.bat" -WorkingDirectory $esPath -WindowStyle Minimized
        
        Write-Host "   [*] Aguardando Elasticsearch iniciar (30s)..." -ForegroundColor Yellow
        Start-Sleep -Seconds 30
        
        try {
            $esCheck = Invoke-WebRequest -Uri "http://localhost:9200" -TimeoutSec 5 -UseBasicParsing 2>$null
            if ($esCheck.StatusCode -eq 200) {
                Write-Host "   [OK] Elasticsearch iniciado com sucesso" -ForegroundColor Green
                $esRunning = $true
            }
        } catch {
            Write-Host "   [!] Elasticsearch ainda esta iniciando..." -ForegroundColor Yellow
            Write-Host "      Aguarde mais alguns segundos" -ForegroundColor Cyan
        }
    } else {
        Write-Host "   [X] Elasticsearch nao encontrado em $esPath" -ForegroundColor Red
        Write-Host "      Baixe e extraia o Elasticsearch 8.10.0" -ForegroundColor Yellow
        $allOk = $false
    }
}

if (-not $allOk) {
    Write-Host ""
    Write-Host "============================================" -ForegroundColor Red
    Write-Host "   REQUISITOS NAO ATENDIDOS" -ForegroundColor Red
    Write-Host "============================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Recomendacao: Use o Docker para uma instalacao mais facil" -ForegroundColor Yellow
    Write-Host "   Execute: .\INICIAR.ps1" -ForegroundColor Cyan
    Write-Host ""
    Read-Host "Pressione Enter para sair"
    exit 1
}

Write-Host ""
Write-Host "[*] Iniciando servidor PHP..." -ForegroundColor Yellow

# Verificar se composer install foi executado
if (-not (Test-Path ".\vendor")) {
    Write-Host "   [*] Instalando dependencias PHP..." -ForegroundColor Cyan
    composer install
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "   SISTEMA INICIADO!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "Servicos disponiveis:" -ForegroundColor Cyan
Write-Host ""
Write-Host "   [WEB] Site Principal:" -ForegroundColor White
Write-Host "      http://localhost:8000" -ForegroundColor Yellow
Write-Host ""
Write-Host "   [SEARCH] Elasticsearch:" -ForegroundColor White
Write-Host "      http://localhost:9200" -ForegroundColor Yellow
Write-Host ""
Write-Host "   [DB] MySQL:" -ForegroundColor White
Write-Host "      localhost:3306" -ForegroundColor Yellow
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "[*] Abrindo navegador..." -ForegroundColor Cyan

# Iniciar servidor PHP
cd public
Write-Host "   Servidor PHP rodando em http://localhost:8000" -ForegroundColor Green
Write-Host "   Pressione Ctrl+C para parar" -ForegroundColor Yellow
Write-Host ""

Start-Sleep -Seconds 2
Start-Process "http://localhost:8000"

php -S localhost:8000
