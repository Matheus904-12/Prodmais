# ========================================
# Script de Limpeza e Rebuild - PRODMAIS
# ========================================

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   LIMPEZA E REBUILD DO DOCKER" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "1/4 Parando todos os containers..." -ForegroundColor Yellow
docker-compose down

Write-Host ""
Write-Host "2/4 Removendo imagens antigas..." -ForegroundColor Yellow
docker rmi prodmais-web 2>$null

Write-Host ""
Write-Host "3/4 Limpando cache do Docker..." -ForegroundColor Yellow  
docker builder prune -f

Write-Host ""
Write-Host "4/4 Reconstruindo e iniciando..." -ForegroundColor Yellow
docker-compose up -d --build --force-recreate

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "============================================" -ForegroundColor Green
    Write-Host "   [OK] REBUILD CONCLUIDO COM SUCESSO!" -ForegroundColor Green
    Write-Host "============================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Aguardando servicos iniciarem..." -ForegroundColor Cyan
    Start-Sleep -Seconds 15
    
    Write-Host ""
    Write-Host "Abrindo navegador..." -ForegroundColor Cyan
    Start-Process "http://localhost:8080"
} else {
    Write-Host ""
    Write-Host "[X] ERRO no rebuild!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Verifique os logs:" -ForegroundColor Yellow
    Write-Host "   docker-compose logs" -ForegroundColor White
    Write-Host ""
}

Write-Host ""
Read-Host "Pressione Enter para sair"
