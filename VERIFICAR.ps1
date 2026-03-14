# ========================================
# VERIFICACAO DO SISTEMA
# ========================================
# Execute este script para verificar se tudo esta funcionando

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   VERIFICACAO DO SISTEMA PRODMAIS" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$allOk = $true

# Verificar Docker
Write-Host "[*] Verificando Docker..." -ForegroundColor Yellow
try {
    $dockerVersion = docker --version
    if ($dockerVersion) {
        Write-Host "   [OK] Docker instalado: $dockerVersion" -ForegroundColor Green
        
        $dockerStatus = docker ps 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   [OK] Docker esta rodando" -ForegroundColor Green
        } else {
            Write-Host "   [X] Docker nao esta rodando!" -ForegroundColor Red
            Write-Host "      Inicie o Docker Desktop" -ForegroundColor Yellow
            $allOk = $false
        }
    }
} catch {
    Write-Host "   [X] Docker nao encontrado!" -ForegroundColor Red
    Write-Host "      Instale o Docker Desktop" -ForegroundColor Yellow
    $allOk = $false
}

Write-Host ""

# Verificar containers
Write-Host "[*] Verificando containers..." -ForegroundColor Yellow
$containers = docker ps --format "{{.Names}}" 2>$null

if ($containers) {
    $expectedContainers = @("prodmais_web", "prodmais_mysql", "prodmais_elasticsearch", "prodmais_kibana", "prodmais_phpmyadmin")
    
    foreach ($container in $expectedContainers) {
        if ($containers -contains $container) {
            Write-Host "   [OK] $container esta rodando" -ForegroundColor Green
        } else {
            Write-Host "   [!] $container nao esta rodando" -ForegroundColor Yellow
        }
    }
} else {
    Write-Host "   [!] Nenhum container rodando" -ForegroundColor Yellow
    Write-Host "      Execute INICIAR.ps1 para iniciar" -ForegroundColor Cyan
}

Write-Host ""

# Verificar servicos web
Write-Host "[*] Verificando servicos web..." -ForegroundColor Yellow

# Site principal
try {
    $web = Invoke-WebRequest -Uri "http://localhost:8080" -TimeoutSec 3 -UseBasicParsing 2>$null
    if ($web.StatusCode -eq 200) {
        Write-Host "   [OK] Site Principal (http://localhost:8080)" -ForegroundColor Green
    }
} catch {
    Write-Host "   [X] Site Principal nao esta acessivel" -ForegroundColor Red
    $allOk = $false
}

# Elasticsearch
try {
    $es = Invoke-WebRequest -Uri "http://localhost:9200" -TimeoutSec 3 -UseBasicParsing 2>$null
    if ($es.StatusCode -eq 200) {
        Write-Host "   [OK] Elasticsearch (http://localhost:9200)" -ForegroundColor Green
    }
} catch {
    Write-Host "   [X] Elasticsearch nao esta acessivel" -ForegroundColor Red
    $allOk = $false
}

# Kibana
try {
    $kibana = Invoke-WebRequest -Uri "http://localhost:5601" -TimeoutSec 3 -UseBasicParsing 2>$null
    if ($kibana.StatusCode -eq 200) {
        Write-Host "   [OK] Kibana (http://localhost:5601)" -ForegroundColor Green
    }
} catch {
    Write-Host "   [!] Kibana nao esta acessivel (nao critico)" -ForegroundColor Yellow
}

# phpMyAdmin
try {
    $phpmyadmin = Invoke-WebRequest -Uri "http://localhost:8081" -TimeoutSec 3 -UseBasicParsing 2>$null
    if ($phpmyadmin.StatusCode -eq 200) {
        Write-Host "   [OK] phpMyAdmin (http://localhost:8081)" -ForegroundColor Green
    }
} catch {
    Write-Host "   [!] phpMyAdmin nao esta acessivel (nao critico)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan

if ($allOk) {
    Write-Host "   SISTEMA PRONTO PARA DEMONSTRACAO!" -ForegroundColor Green
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "[OK] Tudo esta funcionando perfeitamente!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Links rapidos:" -ForegroundColor White
    Write-Host "   * Site: http://localhost:8080" -ForegroundColor Cyan
    Write-Host "   * Kibana: http://localhost:5601" -ForegroundColor Cyan
    Write-Host "   * phpMyAdmin: http://localhost:8081" -ForegroundColor Cyan
} else {
    Write-Host "   ATENCAO: ALGUNS PROBLEMAS DETECTADOS" -ForegroundColor Yellow
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Recomendacoes:" -ForegroundColor White
    Write-Host "   1. Certifique-se que o Docker Desktop esta rodando" -ForegroundColor Cyan
    Write-Host "   2. Execute: .\INICIAR.ps1" -ForegroundColor Cyan
    Write-Host "   3. Aguarde 2-3 minutos" -ForegroundColor Cyan
    Write-Host "   4. Execute este script novamente" -ForegroundColor Cyan
}

Write-Host ""
Read-Host "Pressione Enter para sair"
