# ========================================
# SCRIPT DE INICIALIZACAO - PRODMAIS
# ========================================
# Execute este script para iniciar todo o sistema

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   PRODMAIS - INICIALIZACAO RAPIDA" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se o Docker esta rodando
Write-Host "[*] Verificando Docker..." -ForegroundColor Yellow
try {
    $dockerStatus = docker ps 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[X] Docker nao esta rodando!" -ForegroundColor Red
        Write-Host "    Por favor, inicie o Docker Desktop e execute este script novamente." -ForegroundColor Yellow
        Write-Host ""
        Read-Host "Pressione Enter para sair"
        exit 1
    }
    Write-Host "[OK] Docker esta rodando!" -ForegroundColor Green
} catch {
    Write-Host "[X] Docker nao encontrado!" -ForegroundColor Red
    Write-Host "    Por favor, instale o Docker Desktop primeiro." -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Pressione Enter para sair"
    exit 1
}

Write-Host ""
Write-Host "[*] Parando containers antigos..." -ForegroundColor Yellow
docker-compose down 2>$null

Write-Host ""
Write-Host "[*] Construindo e iniciando containers..." -ForegroundColor Yellow
Write-Host "    Isso pode levar alguns minutos na primeira vez..." -ForegroundColor Cyan
Write-Host ""

docker-compose up -d --build

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "============================================" -ForegroundColor Green
    Write-Host "   SISTEMA INICIADO COM SUCESSO!" -ForegroundColor Green
    Write-Host "============================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Servicos disponiveis:" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "   [WEB] Site Principal:" -ForegroundColor White
    Write-Host "      http://localhost:8080" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "   [SEARCH] Elasticsearch:" -ForegroundColor White
    Write-Host "      http://localhost:9200" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "   [CHARTS] Kibana (Visualizacao):" -ForegroundColor White
    Write-Host "      http://localhost:5601" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "   [DB] phpMyAdmin (Banco de Dados):" -ForegroundColor White
    Write-Host "      http://localhost:8081" -ForegroundColor Yellow
    Write-Host "      Usuario: prodmais" -ForegroundColor Gray
    Write-Host "      Senha: prodmais123" -ForegroundColor Gray
    Write-Host ""
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "[*] Aguardando servicos ficarem prontos..." -ForegroundColor Yellow
    Start-Sleep -Seconds 10
    
    Write-Host ""
    Write-Host "[*] Verificando status dos servicos..." -ForegroundColor Yellow
    Write-Host ""
    
    # Verificar MySQL
    Write-Host "   [MySQL]        " -NoNewline
    $mysqlReady = $false
    for ($i = 0; $i -lt 30; $i++) {
        try {
            $mysqlCheck = docker exec prodmais_mysql mysqladmin ping -h localhost -u root -proot 2>$null
            if ($mysqlCheck -match "alive") {
                Write-Host "[OK] Pronto" -ForegroundColor Green
                $mysqlReady = $true
                break
            }
        } catch {}
        Start-Sleep -Seconds 2
    }
    if (-not $mysqlReady) {
        Write-Host "[!] Ainda iniciando..." -ForegroundColor Yellow
    }
    
    # Verificar Elasticsearch
    Write-Host "   [Elasticsearch] " -NoNewline
    $esReady = $false
    for ($i = 0; $i -lt 30; $i++) {
        try {
            $esCheck = Invoke-WebRequest -Uri "http://localhost:9200" -TimeoutSec 2 -UseBasicParsing 2>$null
            if ($esCheck.StatusCode -eq 200) {
                Write-Host "[OK] Pronto" -ForegroundColor Green
                $esReady = $true
                break
            }
        } catch {}
        Start-Sleep -Seconds 2
    }
    if (-not $esReady) {
        Write-Host "[!] Ainda iniciando..." -ForegroundColor Yellow
    }
    
    # Verificar Web
    Write-Host "   [Web Server]   " -NoNewline
    $webReady = $false
    for ($i = 0; $i -lt 20; $i++) {
        try {
            $webCheck = Invoke-WebRequest -Uri "http://localhost:8080" -TimeoutSec 2 -UseBasicParsing 2>$null
            if ($webCheck.StatusCode -eq 200) {
                Write-Host "[OK] Pronto" -ForegroundColor Green
                $webReady = $true
                break
            }
        } catch {}
        Start-Sleep -Seconds 2
    }
    if (-not $webReady) {
        Write-Host "[!] Ainda iniciando..." -ForegroundColor Yellow
    }
    
    Write-Host ""
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "DICAS:" -ForegroundColor White
    Write-Host "   * Para ver os logs: " -NoNewline -ForegroundColor Gray
    Write-Host "docker-compose logs -f" -ForegroundColor Yellow
    Write-Host "   * Para parar tudo: " -NoNewline -ForegroundColor Gray
    Write-Host "Execute PARAR.ps1" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "[OK] Pronto para a demonstracao!" -ForegroundColor Green
    Write-Host ""
    
    # Abrir navegador automaticamente
    Write-Host "[*] Abrindo navegador..." -ForegroundColor Cyan
    Start-Sleep -Seconds 2
    Start-Process "http://localhost:8080"
    
} else {
    Write-Host ""
    Write-Host "[X] ERRO ao iniciar o sistema!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Tente verificar os logs com:" -ForegroundColor Yellow
    Write-Host "   docker-compose logs" -ForegroundColor White
    Write-Host ""
}

Write-Host ""
Read-Host "Pressione Enter para sair"
