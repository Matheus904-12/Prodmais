# SCRIPT DE RECONSTRUCAO TOTAL
# Use apenas quando alterar dependencias de sistema ou composer.json

Write-Host "Redeclaração do ambiente..." -ForegroundColor Cyan
docker compose down -v
docker compose build --no-cache
docker compose up -d
Write-Host "Ambiente reconstruído do zero!" -ForegroundColor Green
