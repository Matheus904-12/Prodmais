# ========================================
# SCRIPT PARA PARAR O SISTEMA
# ========================================

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   PARANDO SISTEMA PRODMAIS" -ForegroundColor Yellow
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "[*] Parando containers..." -ForegroundColor Yellow
docker-compose down

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "[OK] Sistema parado com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Para iniciar novamente, execute: " -NoNewline -ForegroundColor Gray
    Write-Host "INICIAR.ps1" -ForegroundColor Yellow
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "[!] Erro ao parar containers." -ForegroundColor Yellow
    Write-Host ""
}

Read-Host "Pressione Enter para sair"
