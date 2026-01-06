# Script para limpar repositorio Prodmais
# Remove arquivos e pastas desnecessarias

Write-Host "====================================" -ForegroundColor Cyan
Write-Host "LIMPEZA DO REPOSITORIO PRODMAIS" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""

$baseDir = "C:\app3\Prodmais"
Set-Location $baseDir

# Lista de arquivos e pastas para remover
$itemsToRemove = @(
    "prodmais-main",
    "fix_disk_lock.php",
    "fix_elasticsearch.php",
    "fix_elasticsearch.ps1",
    "fix_shards.php",
    "restart_elasticsearch.php",
    "install.bat",
    "install.sh",
    "install_es.ps1",
    "instalar_kibana.bat",
    "start.sh",
    "preparar-apresentacao.ps1",
    "prepare-000webhost.ps1",
    "prepare-infinityfree.ps1",
    "prepare-infinityfree.sh",
    "cleanup.ps1",
    "prodmais-infinityfree.zip",
    ".history",
    "CHANGELOG.md",
    "CHECKLIST_APRESENTACAO.md",
    "COMPARATIVO_SISTEMAS.md",
    "CORRECAO_404_INFINITYFREE.md",
    "DEPLOY_000WEBHOST.md",
    "DEPLOY_ALTERNATIVAS.md",
    "DEPLOY_GUIA_RAPIDO.md",
    "DEPLOY_INFINITYFREE.md",
    "DEPLOY_RAILWAY.md",
    "DEPLOY_RENDER.md",
    "GUIA_TESTE_RAPIDO.md",
    "INICIO_RAPIDO.md",
    "INSTALAR_ELASTICSEARCH.md",
    "INTEGRACAO_CONCLUIDA.md",
    "PLANO_IMPLEMENTACAO_COMPLETO.md",
    "PRODUCAO_READY.md",
    "RELATORIO_APRESENTACAO.md",
    "RELATORIO_FINAL.md",
    "ROTEIRO_DEMONSTRACAO.md",
    "SUCESSO_IMPORTACAO.md",
    "TESTES_CORRIGIDOS.md",
    "TESTES_CYPRESS.md",
    "TROUBLESHOOTING.md",
    "GUIA_PROXIMOS_PASSOS.md",
    "MIGRACAO_MYSQL_LOCAWEB.md",
    "RESUMO_EXECUTIVO.md",
    ".htaccess-000webhost",
    "index-root.php"
)

$removedCount = 0
$failedCount = 0

foreach ($item in $itemsToRemove) {
    $fullPath = Join-Path $baseDir $item
    
    if (Test-Path $fullPath) {
        try {
            Write-Host "Removendo: $item" -ForegroundColor Yellow
            Remove-Item -Path $fullPath -Recurse -Force -ErrorAction Stop
            $removedCount++
            Write-Host "  [OK] Removido com sucesso" -ForegroundColor Green
        } catch {
            Write-Host "  [ERRO] Falha: $_" -ForegroundColor Red
            $failedCount++
        }
    }
}

Write-Host ""
Write-Host "====================================" -ForegroundColor Cyan
Write-Host "RESUMO DA LIMPEZA" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan
Write-Host "Itens removidos: $removedCount" -ForegroundColor Green
Write-Host "Falhas: $failedCount" -ForegroundColor $(if ($failedCount -gt 0) { "Red" } else { "Green" })
Write-Host ""
Write-Host "LIMPEZA CONCLUIDA!" -ForegroundColor Green
Write-Host ""

