# Script para iniciar Elasticsearch corretamente
Write-Host "🚀 Iniciando Elasticsearch..." -ForegroundColor Cyan

$esPath = "C:\elasticsearch-8.10.0"
$configFile = "$esPath\config\elasticsearch.yml"

# Verificar se o Elasticsearch está instalado
if (-not (Test-Path $esPath)) {
    Write-Host "❌ Elasticsearch não encontrado em $esPath" -ForegroundColor Red
    Write-Host "Execute primeiro: php fix_elasticsearch.php para instalar" -ForegroundColor Yellow
    exit 1
}

# Configurar elasticsearch.yml corretamente
$config = @"
cluster.name: prodmais-umc
node.name: node-1
path.data: C:\elasticsearch-8.10.0\data
path.logs: C:\elasticsearch-8.10.0\logs
network.host: localhost
http.port: 9200
discovery.type: single-node
xpack.security.enabled: false
xpack.security.http.ssl.enabled: false
"@

Write-Host "📝 Configurando elasticsearch.yml..." -ForegroundColor Yellow
Set-Content -Path $configFile -Value $config -Encoding UTF8

# Criar diretórios de dados e logs
New-Item -ItemType Directory -Path "$esPath\data" -Force | Out-Null
New-Item -ItemType Directory -Path "$esPath\logs" -Force | Out-Null

Write-Host "✅ Configuração completa!" -ForegroundColor Green
Write-Host "`n🔄 Iniciando Elasticsearch..." -ForegroundColor Cyan

# Iniciar Elasticsearch
Start-Process -FilePath "$esPath\bin\elasticsearch.bat" -WorkingDirectory $esPath -WindowStyle Normal

Write-Host "`n⏳ Aguardando Elasticsearch iniciar..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Verificar se está rodando
try {
    $health = Invoke-RestMethod -Uri "http://localhost:9200/_cluster/health" -TimeoutSec 5
    Write-Host "`n✅ Elasticsearch está rodando!" -ForegroundColor Green
    Write-Host "   Cluster: $($health.cluster_name)" -ForegroundColor Cyan
    Write-Host "   Status: $($health.status)" -ForegroundColor $(if ($health.status -eq "green") { "Green" } else { "Yellow" })
    Write-Host "   Nodes: $($health.number_of_nodes)" -ForegroundColor Cyan
} catch {
    Write-Host "`n⚠️ Elasticsearch ainda está iniciando..." -ForegroundColor Yellow
    Write-Host "Aguarde mais 30 segundos e execute: curl http://localhost:9200" -ForegroundColor Cyan
}

Write-Host "`n📌 Próximo passo:" -ForegroundColor Cyan
Write-Host "Execute: php fix_elasticsearch.php" -ForegroundColor Yellow
Write-Host "Para criar os índices corretamente" -ForegroundColor Gray
