# Script para corrigir configuração do Elasticsearch
Write-Host "🔧 Corrigindo configuração do Elasticsearch..." -ForegroundColor Cyan

# Deletar índices com problemas
Write-Host "`n1️⃣ Deletando índices com configuração incorreta..." -ForegroundColor Yellow
$indices = @("prodmais_umc", "prodmais_umc_cv", "prodmais_umc_ppg", "prodmais_umc_projetos", "qualis", "openalexcitedworks")
foreach ($index in $indices) {
    try {
        Invoke-RestMethod -Uri "http://localhost:9200/$index" -Method Delete -ErrorAction SilentlyContinue | Out-Null
        Write-Host "  ✅ Índice $index deletado" -ForegroundColor Green
    } catch {
        Write-Host "  ℹ️ Índice $index não existia" -ForegroundColor Gray
    }
}

Start-Sleep -Seconds 2

# Criar índices corretos (sem réplicas para single-node)
Write-Host "`n2️⃣ Criando índices com configuração correta..." -ForegroundColor Yellow

$indexConfig = @{
    settings = @{
        number_of_shards = 1
        number_of_replicas = 0
    }
} | ConvertTo-Json -Depth 10

foreach ($index in $indices) {
    try {
        $response = Invoke-RestMethod -Uri "http://localhost:9200/$index" -Method Put -Body $indexConfig -ContentType "application/json"
        Write-Host "  ✅ Índice $index criado com sucesso" -ForegroundColor Green
    } catch {
        Write-Host "  ❌ Erro ao criar $index : $_" -ForegroundColor Red
    }
}

Start-Sleep -Seconds 2

# Verificar status do cluster
Write-Host "`n3️⃣ Verificando status do cluster..." -ForegroundColor Yellow
$health = Invoke-RestMethod -Uri "http://localhost:9200/_cluster/health?pretty"
Write-Host "  Cluster: $($health.cluster_name)" -ForegroundColor Cyan
Write-Host "  Status: $($health.status)" -ForegroundColor $(if ($health.status -eq "green") { "Green" } elseif ($health.status -eq "yellow") { "Yellow" } else { "Red" })
Write-Host "  Nós: $($health.number_of_nodes)" -ForegroundColor Cyan
Write-Host "  Shards ativos: $($health.active_primary_shards)/$($health.active_shards)" -ForegroundColor Cyan

# Listar índices
Write-Host "`n4️⃣ Índices disponíveis:" -ForegroundColor Yellow
$indicesResponse = Invoke-RestMethod -Uri "http://localhost:9200/_cat/indices?v&h=health,status,index,docs.count"
Write-Host $indicesResponse

Write-Host "`n✅ Configuração concluída!" -ForegroundColor Green
Write-Host "Agora você pode importar currículos normalmente." -ForegroundColor Cyan
