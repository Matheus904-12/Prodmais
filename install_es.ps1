# PRODMAIS UMC - Instalador Elasticsearch 8.10.0
Write-Host "Instalando Elasticsearch..." -ForegroundColor Cyan

$version = "8.10.0"
$url = "https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-$version-windows-x86_64.zip"
$zip = "$env:TEMP\elasticsearch.zip"
$dest = "C:\elasticsearch-$version"

if (Test-Path $dest) {
    Write-Host "Elasticsearch ja instalado em $dest" -ForegroundColor Green
    $r = Read-Host "Reinstalar? (S/N)"
    if ($r -ne "S") { exit 0 }
    Remove-Item $dest -Recurse -Force
}

Write-Host "Baixando..." -ForegroundColor Yellow
$ProgressPreference = 'SilentlyContinue'
Invoke-WebRequest $url -OutFile $zip

Write-Host "Extraindo..." -ForegroundColor Yellow
Expand-Archive $zip -DestinationPath "C:\" -Force

Write-Host "Configurando..." -ForegroundColor Yellow
$cfg = @"
cluster.name: prodmais-umc
network.host: localhost
http.port: 9200
discovery.type: single-node
xpack.security.enabled: false
"@
Set-Content "$dest\config\elasticsearch.yml" $cfg

$start = @"
@echo off
cd /d "$dest\bin"
elasticsearch.bat
"@
Set-Content "$dest\INICIAR.bat" $start

Remove-Item $zip -Force

Write-Host "Concluido! Execute: $dest\INICIAR.bat" -ForegroundColor Green
$i = Read-Host "Iniciar agora? (S/N)"
if ($i -eq "S") { Start-Process "$dest\INICIAR.bat" }
