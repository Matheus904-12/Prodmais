# Script para preparar arquivos para hospedagem gratuita
# Compatível com: 000webhost, InfinityFree, Hostinger Free
# Cria um ZIP otimizado apenas com arquivos necessários

Write-Host "🚀 Preparando arquivos para hospedagem gratuita..." -ForegroundColor Green
Write-Host ""

# Nome do arquivo ZIP
$zipName = "prodmais-deploy.zip"

# Remover ZIP antigo se existir
if (Test-Path $zipName) {
    Remove-Item $zipName -Force
    Write-Host "✓ ZIP antigo removido" -ForegroundColor Yellow
}

# Arquivos e pastas a INCLUIR
$includeItems = @(
    "bin",
    "config",
    "data",
    "public",
    "src",
    "vendor",
    "composer.json",
    "composer.lock",
    ".htaccess-000webhost",
    "index-root.php",
    "README.md"
)

# Criar pasta temporária
$tempFolder = "temp_000webhost"
if (Test-Path $tempFolder) {
    Remove-Item $tempFolder -Recurse -Force
}
New-Item -ItemType Directory -Path $tempFolder | Out-Null

Write-Host "📦 Copiando arquivos necessários..." -ForegroundColor Cyan

# Copiar itens selecionados
foreach ($item in $includeItems) {
    if (Test-Path $item) {
        Copy-Item -Path $item -Destination $tempFolder -Recurse -Force
        Write-Host "  ✓ $item" -ForegroundColor Gray
    }
}

# Renomear arquivos especiais
Move-Item -Path "$tempFolder/.htaccess-000webhost" -Destination "$tempFolder/.htaccess" -Force
Move-Item -Path "$tempFolder/index-root.php" -Destination "$tempFolder/index.php" -Force

Write-Host ""
Write-Host "🗜️ Compactando arquivos..." -ForegroundColor Cyan

# Criar ZIP
Compress-Archive -Path "$tempFolder\*" -DestinationPath $zipName -Force

# Remover pasta temporária
Remove-Item $tempFolder -Recurse -Force

# Tamanho do arquivo
$fileSize = (Get-Item $zipName).Length / 1MB
$fileSizeMB = [math]::Round($fileSize, 2)

Write-Host ""
Write-Host "✅ Arquivo criado com sucesso!" -ForegroundColor Green
Write-Host "📁 Nome: $zipName" -ForegroundColor White
Write-Host "📊 Tamanho: $fileSizeMB MB" -ForegroundColor White
Write-Host ""
Write-Host "📤 PRÓXIMOS PASSOS:" -ForegroundColor Yellow
Write-Host "1. Acesse o painel da sua hospedagem gratuita" -ForegroundColor White
Write-Host "2. Abra o File Manager ou cPanel" -ForegroundColor White
Write-Host "3. Entre na pasta 'public_html'" -ForegroundColor White
Write-Host "4. Delete todos os arquivos existentes" -ForegroundColor White
Write-Host "5. Faça upload do arquivo: $zipName" -ForegroundColor Cyan
Write-Host "6. Extraia o arquivo ZIP" -ForegroundColor White
Write-Host "7. Delete o ZIP após extrair" -ForegroundColor White
Write-Host "8. Configure permissões da pasta 'data' para 755" -ForegroundColor White
Write-Host "9. Acesse seu site!" -ForegroundColor Green
Write-Host ""
Write-Host "🎉 Pronto para upload!" -ForegroundColor Green
