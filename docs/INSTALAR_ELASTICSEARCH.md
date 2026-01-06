# 🚀 Guia de Instalação do Elasticsearch para PRODMAIS UMC

## ✅ O que já está funcionando

- ✅ **Sistema de importação Lattes** - Testado com currículo real de 126 produções!
- ✅ **Interface web** completa (8 páginas)
- ✅ **Servidor PHP** rodando em http://localhost:8000
- ✅ **Processamento de currículos extensos** - Sem problemas de memória ou timeout

## 📋 O que falta

Apenas o **Elasticsearch** precisa ser instalado para indexar os dados extraídos.

---

## 🐳 Instalação via Docker (RECOMENDADO)

### Passo 1: Instalar Docker Desktop

1. Baixe o Docker Desktop: https://www.docker.com/products/docker-desktop/
2. Instale e reinicie o computador se necessário
3. Abra o Docker Desktop e aguarde inicializar

### Passo 2: Iniciar Elasticsearch

Abra o **PowerShell** e execute:

```powershell
docker run -d `
  --name elasticsearch-prodmais `
  -p 9200:9200 `
  -p 9300:9300 `
  -e "discovery.type=single-node" `
  -e "xpack.security.enabled=false" `
  -e "ES_JAVA_OPTS=-Xms1g -Xmx1g" `
  docker.elastic.co/elasticsearch/elasticsearch:8.10.0
```

### Passo 3: Verificar se está rodando

```powershell
# Aguarde 30 segundos e teste:
curl http://localhost:9200
```

Você deve ver uma resposta JSON com informações do Elasticsearch.

### Passo 4: Reimportar o currículo

Agora com o Elasticsearch rodando, reimporte o currículo:

```powershell
cd C:\app3\Prodmais
php src/LattesImporter.php -f "C:\Users\mathe\Downloads\2745899638505571 (1).xml" -p "Biotecnologia" -a "Biotecnologia Industrial"
```

Desta vez você verá:
- ✅ Pesquisador indexado
- ✅ 126 produções indexadas
- ✅ Dados disponíveis para busca!

---

## 🌐 Opção 2: Instalação Manual (Windows)

### Passo 1: Baixar Elasticsearch

1. Acesse: https://www.elastic.co/downloads/elasticsearch
2. Baixe a versão 8.10.0 para Windows (.zip)
3. Extraia para `C:\elasticsearch-8.10.0`

### Passo 2: Configurar

Edite o arquivo `C:\elasticsearch-8.10.0\config\elasticsearch.yml`:

```yaml
# Adicione estas linhas:
network.host: localhost
http.port: 9200
xpack.security.enabled: false
discovery.type: single-node
```

### Passo 3: Iniciar

```powershell
cd C:\elasticsearch-8.10.0\bin
.\elasticsearch.bat
```

Aguarde até ver a mensagem: `"started"`

### Passo 4: Testar

Em outro PowerShell:

```powershell
curl http://localhost:9200
```

---

## 🎯 Após instalar o Elasticsearch

### 1. Acesse a interface web

http://localhost:8000/importar_lattes.php

### 2. Importe currículos via interface

- Selecione o PPG
- Faça upload do XML do Lattes
- Clique em "Importar Currículo"
- Aguarde o processamento (currículos extensos podem levar 1-2 minutos)

### 3. Visualize os dados

- **Busca geral:** http://localhost:8000/index_umc.php
- **PPGs:** http://localhost:8000/ppgs.php
- **Projetos:** http://localhost:8000/projetos.php
- **Dashboard:** http://localhost:8000/dashboard.php (requer Kibana)

---

## 📊 Instalar Kibana (Opcional - para Dashboards)

### Via Docker:

```powershell
docker run -d `
  --name kibana-prodmais `
  -p 5601:5601 `
  -e "ELASTICSEARCH_HOSTS=http://host.docker.internal:9200" `
  docker.elastic.co/kibana/kibana:8.10.0
```

Acesse: http://localhost:5601

---

## ✅ Teste Completo

### 1. Verificar Elasticsearch
```powershell
curl http://localhost:9200/_cat/indices?v
```

Você deve ver os índices:
- `prodmais_umc` (produções)
- `prodmais_umc_cv` (currículos)
- `prodmais_umc_ppg` (PPGs)
- `prodmais_umc_projetos` (projetos)

### 2. Buscar dados
```powershell
curl http://localhost:9200/prodmais_umc/_search?pretty
```

### 3. Contar produções do coordenador
```powershell
curl -X GET "http://localhost:9200/prodmais_umc/_count?q=lattesID:2745899638505571"
```

Deve retornar: `"count": 126`

---

## 🐛 Resolução de Problemas

### Erro: "No alive nodes"

**Causa:** Elasticsearch não está rodando  
**Solução:**
```powershell
# Verificar se o Docker está rodando:
docker ps

# Se não aparecer elasticsearch-prodmais, inicie:
docker start elasticsearch-prodmais

# Ou crie novamente (veja Passo 2 acima)
```

### Erro: "Out of memory"

**Causa:** Pouca memória para o Elasticsearch  
**Solução:** Aumente a memória no Docker Desktop:
1. Abra Docker Desktop
2. Settings → Resources
3. Aumente "Memory" para pelo menos 4GB

### Elasticsearch não inicia

**Solução:**
```powershell
# Verificar logs:
docker logs elasticsearch-prodmais

# Remover e recriar:
docker rm -f elasticsearch-prodmais
# Execute novamente o comando do Passo 2
```

---

## 📝 Comandos Úteis

### Parar Elasticsearch
```powershell
docker stop elasticsearch-prodmais
```

### Iniciar Elasticsearch
```powershell
docker start elasticsearch-prodmais
```

### Remover todos os dados (reset)
```powershell
docker rm -f elasticsearch-prodmais
# Execute novamente o comando de criação
```

### Ver logs em tempo real
```powershell
docker logs -f elasticsearch-prodmais
```

---

## 🎓 Próximos Passos

Após ter o Elasticsearch rodando:

1. ✅ **Importe todos os currículos dos 4 PPGs**
   - Use a interface: http://localhost:8000/importar_lattes.php
   - Ou via linha de comando (mais rápido para múltiplos arquivos)

2. ✅ **Configure os dashboards Kibana**
   - Abra http://localhost:5601
   - Importe os templates de visualização

3. ✅ **Integre com ORCID e OpenAlex**
   - Configure as chaves de API em `config/config_umc.php`

4. ✅ **Configure autenticação**
   - Edite usuários em `public/login.php`
   - Defina senhas seguras

---

## 📞 Suporte

Em caso de dúvidas:
- Verifique os logs: `docker logs elasticsearch-prodmais`
- Teste a conexão: `curl http://localhost:9200`
- Reimporte dados se necessário

**Sistema desenvolvido para PIVIC 2025 - UMC**  
Baseado em Prodmais UNIFESP com adaptações para os 4 PPGs da UMC.
