# Script para Limpar Arquivos Desnecessarios do Git
# ATENCAO: Este script ira modificar o historico do Git localmente

Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "  LIMPEZA DE ARQUIVOS DESNECESSARIOS DO GIT" -ForegroundColor Cyan  
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se ha mudancas nao commitadas
$status = git status --porcelain
if ($status) {
    Write-Host "Atencao: Ha mudancas nao commitadas!" -ForegroundColor Yellow
    Write-Host "   Commit ou descarte as mudancas antes de continuar." -ForegroundColor Yellow
    Write-Host ""
    git status --short
    Write-Host ""
    $continue = Read-Host "Deseja continuar mesmo assim? (s/N)"
    if ($continue -ne 's' -and $continue -ne 'S') {
        Write-Host "Operacao cancelada." -ForegroundColor Red
        exit
    }
}

Write-Host "Arquivos que serao removidos do Git:" -ForegroundColor Yellow
Write-Host ""
Write-Host "  1. vendor/ (940 arquivos, 4 MB)" -ForegroundColor White
Write-Host "  2. data/db.json" -ForegroundColor White
Write-Host "  3. data/logs.sqlite" -ForegroundColor White
Write-Host "  4. data/lattes_xml/*.xml" -ForegroundColor White
Write-Host "  5. cypress/screenshots/*.png" -ForegroundColor White
Write-Host "  6. cypress/videos/*.mp4" -ForegroundColor White
Write-Host ""
Write-Host "Os arquivos permanecerao no sistema de arquivos," -ForegroundColor Yellow
Write-Host "mas serao removidos do controle de versao do Git." -ForegroundColor Yellow
Write-Host ""

$confirm = Read-Host "Confirma a remocao? (s/N)"
if ($confirm -ne 's' -and $confirm -ne 'S') {
    Write-Host "Operacao cancelada." -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "Removendo arquivos do Git..." -ForegroundColor Cyan

# Remover vendor/
Write-Host "  -> Removendo vendor/..." -ForegroundColor Gray
git rm -r --cached vendor/ 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "    OK vendor/ removida" -ForegroundColor Green
}

# Remover arquivos de data/
Write-Host "  -> Removendo data/db.json..." -ForegroundColor Gray
git rm --cached data/db.json 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "    OK data/db.json removido" -ForegroundColor Green
}

Write-Host "  -> Removendo data/logs.sqlite..." -ForegroundColor Gray
git rm --cached data/logs.sqlite 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "    OK data/logs.sqlite removido" -ForegroundColor Green
}

Write-Host "  -> Removendo data/lattes_xml/*.xml..." -ForegroundColor Gray
git rm --cached data/lattes_xml/*.xml 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "    OK XMLs removidos" -ForegroundColor Green
}

# Remover cypress artifacts
Write-Host "  -> Removendo cypress/screenshots/..." -ForegroundColor Gray
git rm -r --cached cypress/screenshots/ 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "    OK Screenshots removidas" -ForegroundColor Green
}

Write-Host "  -> Removendo cypress/videos/..." -ForegroundColor Gray
git rm -r --cached cypress/videos/ 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "    OK Videos removidos" -ForegroundColor Green
}

Write-Host ""
Write-Host "Limpeza concluida!" -ForegroundColor Green
Write-Host ""
Write-Host "Status do Git:" -ForegroundColor Cyan
git status --short

Write-Host ""
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "  PROXIMOS PASSOS" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Revise as mudancas:" -ForegroundColor White
Write-Host "   git status" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Commit as mudancas:" -ForegroundColor White
Write-Host "   git add .gitignore" -ForegroundColor Gray
Write-Host "   git commit -m 'chore: remove arquivos desnecessarios do repositorio'" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Push para o repositorio remoto:" -ForegroundColor White
Write-Host "   git push" -ForegroundColor Gray
Write-Host ""
Write-Host "IMPORTANTE:" -ForegroundColor Yellow
Write-Host "   - vendor/ sera reinstalada com: composer install" -ForegroundColor Yellow
Write-Host "   - Arquivos de data/ sao regenerados automaticamente" -ForegroundColor Yellow
Write-Host "   - Screenshots e videos do Cypress sao criados ao executar testes" -ForegroundColor Yellow
Write-Host ""
