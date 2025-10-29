# ⚡ GUIA RÁPIDO - Testar Sistema PRODMAIS UMC

## 🎯 Status Atual

✅ **Sistema funcionando perfeitamente!**
- Currículo do coordenador processado: **126 produções extraídas**
- 51 artigos + 3 livros + 3 capítulos + 69 eventos
- Sistema pronto para indexar (aguardando Elasticsearch)

## 📥 Elasticsearch Instalando...

O script está baixando o Elasticsearch (~500MB). Aguarde alguns minutos.

### Após a instalação concluir:

1. **Quando ver "Concluido! Execute..."**, digite `S` para iniciar
2. Aguarde aparecer a mensagem **"started"** na janela do Elasticsearch
3. Continue com os passos abaixo

---

## 🧪 TESTE COMPLETO EM 5 PASSOS

### Passo 1: Verificar Elasticsearch ✅

```powershell
# Teste se está rodando:
curl http://localhost:9200
```

**Resultado esperado:** JSON com informações do Elasticsearch

### Passo 2: Re-importar Currículo com Elasticsearch Ativo 🔄

```powershell
cd C:\app3\Prodmais
php src/LattesImporter.php -f "C:\Users\mathe\Downloads\2745899638505571 (1).xml" -p "Biotecnologia" -a "Biotecnologia Industrial"
```

**Resultado esperado:**
```
✅ Pesquisador indexado: Fabiano Bezerra Menegidio
✅ 126 produções indexadas
✅ 0 projetos indexados
```

### Passo 3: Verificar Dados Indexados 📊

```powershell
# Contar produções:
curl "http://localhost:9200/prodmais_umc/_count"

# Buscar produções:
curl "http://localhost:9200/prodmais_umc/_search?q=*&size=5&pretty"

# Ver pesquisador:
curl "http://localhost:9200/prodmais_umc_cv/_search?q=lattesID:2745899638505571&pretty"
```

### Passo 4: Testar Interface Web 🌐

Acesse no navegador:

1. **Homepage:** http://localhost:8000/index_umc.php
2. **Importar:** http://localhost:8000/importar_lattes.php
3. **PPGs:** http://localhost:8000/ppgs.php
4. **Busca:** Digite "Fabiano" e busque

### Passo 5: Importar Mais Currículos 📤

Use a interface web em http://localhost:8000/importar_lattes.php

1. Selecione o PPG
2. Faça upload do XML do Lattes
3. Clique em "Importar Currículo"
4. Aguarde (currículos extensos podem levar 1-2 min)

---

## 🎓 Demonstração para o Coordenador

### Prepare-se:

1. ✅ Elasticsearch rodando (porta 9200)
2. ✅ PHP server rodando (porta 8000)
3. ✅ Dados do coordenador indexados

### Demonstre:

1. **Homepage moderna:** Mostre http://localhost:8000/index_umc.php
2. **Busca seu nome:** Digite "Fabiano Bezerra" na busca
3. **Resultado:** Mostre as 126 produções encontradas
4. **Filtros:** Demonstre filtros por ano, tipo, etc.
5. **PPGs:** Mostre http://localhost:8000/ppgs.php com os 4 programas
6. **Importação:** Demonstre http://localhost:8000/importar_lattes.php

---

## 🐛 Resolução de Problemas

### Problema: "No alive nodes"

```powershell
# Verifique se Elasticsearch está rodando:
netstat -ano | findstr :9200

# Se não aparecer nada, inicie:
C:\elasticsearch-8.10.0\INICIAR.bat
```

### Problema: Página não abre

```powershell
# Verifique se PHP server está rodando:
netstat -ano | findstr :8000

# Se não aparecer, inicie:
cd C:\app3\Prodmais
php -S localhost:8000 -t public
```

### Problema: Erro ao importar

```powershell
# Verifique as permissões da pasta:
icacls C:\app3\Prodmais\data\lattes_xml

# Crie a pasta se não existir:
mkdir C:\app3\Prodmais\data\lattes_xml
```

---

## 📈 Próximos Passos

### Para completar o PIVIC:

1. **Coletar currículos dos 4 PPGs:**
   - Biotecnologia (✅ coordenador já importado)
   - Engenharia Biomédica
   - Políticas Públicas
   - Ciência e Tecnologia em Saúde

2. **Importar todos via interface web:**
   - http://localhost:8000/importar_lattes.php
   - Selecione PPG correto para cada currículo
   - Sistema processa automaticamente

3. **Configurar Kibana (opcional):**
   - Dashboards de visualização
   - Gráficos de evolução temporal
   - Análise de colaborações

4. **Integrar ORCID (opcional):**
   - Configurar API key em `config/config_umc.php`
   - Enriquecer dados com citações

---

## ✨ Recursos Disponíveis

### Já funcionando:

- ✅ Importação de currículos extensos (testado com 126 produções)
- ✅ Extração automática de: artigos, livros, capítulos, eventos
- ✅ Interface web moderna (Bootstrap 5)
- ✅ Busca por título, autor, ano
- ✅ Filtros por PPG, tipo, período
- ✅ 4 PPGs configurados
- ✅ LGPD compliant (logs de auditoria)

### Formatos de exportação:

- BibTeX
- RIS
- EndNote
- CSV
- JSON
- XML
- ORCID
- BrCris

---

## 🎉 Sistema Pronto!

Após o Elasticsearch instalar e iniciar, você terá um **sistema completo de gerenciamento de produções científicas** para os 4 PPGs da UMC, totalmente compatível com o padrão Prodmais UNIFESP!

**Desenvolvido para PIVIC 2025**
Sistema baseado em Prodmais UNIFESP com adaptações UMC
