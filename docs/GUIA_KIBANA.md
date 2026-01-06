# 🎯 Guia Rápido - Configurar Kibana para Prodmais UMC

## ✅ Status Atual
- **Elasticsearch**: ✅ Rodando na porta 9200
- **Kibana**: ✅ Rodando na porta 5601
- **Dados**: ✅ 125 produções científicas indexadas

## 📋 Passo a Passo

### 1. Acesse o Kibana
Abra no navegador: http://localhost:5601

### 2. Configure Index Patterns

**Na primeira vez:**
1. Clique em **"Explore on my own"** (se aparecer tela de boas-vindas)
2. Vá para: **☰ Menu → Management → Stack Management**
3. Na seção **Kibana**, clique em **Index Patterns**

**Criar os Index Patterns:**

#### Index Pattern 1: Produções Científicas
- Clique em **"Create index pattern"**
- **Name**: `prodmais_umc*`
- Clique em **Next step**
- **Time field**: Selecione `@timestamp` (ou "I don't want to use time field")
- Clique em **Create index pattern**

#### Index Pattern 2: Pesquisadores (CVs)
- Repita o processo:
- **Name**: `prodmais_umc_cv*`
- **Time field**: Nenhum (I don't want to use time field)
- Clique em **Create index pattern**

#### Index Pattern 3: Projetos
- **Name**: `prodmais_umc_projetos*`
- **Time field**: Nenhum
- Clique em **Create index pattern**

### 3. Importar Dashboard

1. Vá para: **☰ Menu → Management → Stack Management**
2. Na seção **Kibana**, clique em **Saved Objects**
3. Clique no botão **Import** (canto superior direito)
4. Selecione o arquivo:
   ```
   C:\app3\Prodmais\prodmais-main\inc\dashboards\dashboard_ppgs_prod_cv.ndjson
   ```
5. Clique em **Import**
6. Se aparecer conflitos, escolha **"Automatically overwrite conflicts"**

### 4. Visualizar Dashboard

1. Vá para: **☰ Menu → Dashboard**
2. Você verá os dashboards importados:
   - **Dashboard PPGs - Produção Científica**
   - **Dashboard PPGs - Currículos**
   - **Dashboard Geral UMC**

### 5. Explorar Dados

**Discover (Busca de Dados):**
- **☰ Menu → Discover**
- Selecione o index pattern: `prodmais_umc*`
- Você verá as 125 produções científicas

**Visualize (Criar Gráficos):**
- **☰ Menu → Visualize Library**
- Crie visualizações personalizadas

## 🚀 Atalhos Rápidos

| Recurso | URL |
|---------|-----|
| Kibana Home | http://localhost:5601 |
| Discover | http://localhost:5601/app/discover |
| Dashboards | http://localhost:5601/app/dashboards |
| Elasticsearch API | http://localhost:9200 |
| Ver Índices | http://localhost:9200/_cat/indices?v |

## 🎨 Criar Visualizações Personalizadas

### Exemplo: Gráfico de Produções por Ano

1. **☰ Menu → Visualize Library → Create visualization**
2. Escolha **Vertical bar** (gráfico de barras)
3. Selecione o index pattern: `prodmais_umc*`
4. Configure:
   - **Y-axis**: Count
   - **X-axis**: Date Histogram → Campo: `year`
5. Clique em **Update** e depois **Save**

### Exemplo: Top 10 Autores

1. **Create visualization → Pie chart**
2. Index pattern: `prodmais_umc*`
3. Configure:
   - **Slice size**: Count
   - **Split slices**: Terms → Campo: `authors.keyword` → Size: 10
4. **Update** e **Save**

## 📊 Dashboards Disponíveis

Após importar, você terá:

1. **Dashboard Produções**: 
   - Produções por ano
   - Produções por tipo
   - Produções por PPG
   - Top autores

2. **Dashboard Pesquisadores**:
   - Total de pesquisadores
   - Pesquisadores por PPG
   - Formação acadêmica

3. **Dashboard Geral**:
   - Visão consolidada
   - Métricas CAPES
   - Indicadores quadrienais

## 🔧 Solução de Problemas

**Kibana não carrega?**
```powershell
# Reiniciar Kibana
Start-Process -FilePath "C:\kibana-9.2.0\bin\kibana.bat" -WorkingDirectory "C:\kibana-9.2.0"
```

**Elasticsearch não conecta?**
```powershell
# Verificar status
Invoke-RestMethod -Uri "http://localhost:9200"
```

**Ver logs do Kibana:**
```
C:\kibana-9.2.0\logs\kibana.log
```

## 📝 Notas Importantes

- ⏰ **Primeira inicialização** do Kibana pode levar 1-2 minutos
- 🔄 **Dados em tempo real**: As visualizações atualizam automaticamente
- 💾 **Dados persistentes**: Os índices ficam salvos no Elasticsearch
- 🎨 **Personalização**: Crie dashboards próprios conforme necessidade

## 🎓 Próximos Passos

1. ✅ Importar mais currículos Lattes → `/admin.php`
2. 📊 Explorar os dashboards
3. 🔍 Criar visualizações customizadas
4. 📤 Exportar relatórios para apresentações

---

**🆘 Precisa de ajuda?**
- Documentação Kibana: https://www.elastic.co/guide/en/kibana/current/index.html
- Elasticsearch Docs: https://www.elastic.co/guide/en/elasticsearch/reference/current/index.html
