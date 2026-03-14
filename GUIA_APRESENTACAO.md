# 🎯 GUIA RÁPIDO PARA APRESENTAÇÃO

## ⚡ INÍCIO RÁPIDO (3 passos)

### 1️⃣ Iniciar o Sistema
```powershell
.\INICIAR.ps1
```
**Tempo:** ~2-3 minutos na primeira vez

### 2️⃣ Acessar o Sistema
O navegador abrirá automaticamente em: **http://localhost:8080**

### 3️⃣ Para Parar
```powershell
.\PARAR.ps1
```

---

## 📋 CHECKLIST PRÉ-APRESENTAÇÃO

### ✅ Antes de Apresentar (10 minutos antes)

- [ ] Abrir Docker Desktop e verificar que está rodando
- [ ] Executar `INICIAR.ps1` e aguardar todos os serviços ficarem prontos
- [ ] Acessar http://localhost:8080 e confirmar que o site abre
- [ ] Testar uma busca rápida para verificar Elasticsearch
- [ ] Verificar que http://localhost:5601 (Kibana) está acessível

### ✅ Durante a Apresentação

- [ ] Manter o Docker Desktop aberto em segundo plano
- [ ] Ter o PowerShell terminal visível com logs (opcional): `docker-compose logs -f`

---

## 🎯 ROTEIRO DE DEMONSTRAÇÃO (10-15 min)

### 1. Visão Geral (2 min)
**Página Principal** → http://localhost:8080

- "Sistema de gestão de produção científica da UMC"
- "Integra dados de Lattes, ORCID, OpenAlex"
- "Busca indexada com Elasticsearch para alta performance"

### 2. Busca Avançada (3 min)
**Página de Busca** → http://localhost:8080/public/presearch.php

Demonstrar:
- ✅ Busca por termo (ex: "inteligência artificial")
- ✅ Filtros por tipo de produção
- ✅ Filtros por período
- ✅ Filtros por PPG
- ✅ Resultados instantâneos

### 3. Dashboard de Métricas (3 min)
**Dashboard** → http://localhost:8080/public/dashboard.php

Mostrar:
- 📊 Gráficos de produção ao longo do tempo
- 📈 Métricas de publicações por PPG
- 🎯 Indicadores de desempenho
- 📉 Análises comparativas

### 4. Gestão de PPGs (2 min)
**PPGs** → http://localhost:8080/public/ppgs.php

Demonstrar:
- 📚 Lista de Programas de Pós-Graduação
- 👥 Pesquisadores vinculados
- 📊 Estatísticas por programa

### 5. Pesquisadores e Importação Lattes (3 min)
**Pesquisadores** → http://localhost:8080/public/pesquisadores.php

Mostrar:
- 👨‍🔬 Cadastro de pesquisadores
- 📄 Importação de currículo Lattes (XML)
- 🔗 Integração com ORCID
- ✅ Processamento automático

### 6. Infraestrutura Técnica (2 min - se questionado)

**Kibana** → http://localhost:5601
- Visualização dos índices Elasticsearch
- Monitoramento em tempo real

**phpMyAdmin** → http://localhost:8081
- Banco de dados MySQL
- Esquema relacional

---

## 🔧 SERVIÇOS E PORTAS

| Serviço | URL | Credenciais |
|---------|-----|-------------|
| **Sistema Principal** | http://localhost:8080 | - |
| **Elasticsearch** | http://localhost:9200 | - |
| **Kibana** | http://localhost:5601 | - |
| **phpMyAdmin** | http://localhost:8081 | usuário: `prodmais`<br>senha: `prodmais123` |
| **MySQL** | localhost:3306 | usuário: `prodmais`<br>senha: `prodmais123` |

---

## ⚠️ TROUBLESHOOTING RÁPIDO

### ❌ Problema: "Docker não está rodando"
**Solução:** Abrir Docker Desktop e aguardar inicialização completa

### ❌ Problema: "Porta 3306 já está em uso"
**Solução:** Parar MySQL local: `net stop mysql` (como Admin)

### ❌ Problema: "Site não carrega"
**Solução:** 
```powershell
docker-compose restart web
```

### ❌ Problema: "Elasticsearch não responde"
**Solução:**
```powershell
docker-compose restart elasticsearch
# Aguardar 30 segundos
```

### 🔍 Ver logs em tempo real
```powershell
docker-compose logs -f
```

### 🔄 Reiniciar tudo
```powershell
.\PARAR.ps1
.\INICIAR.ps1
```

---

## 💡 PONTOS FORTES PARA DESTACAR

### Técnicos
- ✅ **Arquitetura moderna**: Docker, Elasticsearch, MySQL
- ✅ **Escalável**: Arquitetura containerizada permite expansão fácil
- ✅ **Busca rápida**: Elasticsearch indexa todos os dados
- ✅ **Integração externa**: APIs de ORCID, OpenAlex, Lattes

### Funcionais
- ✅ **Importação automática**: Processa XMLs do Lattes
- ✅ **Dashboard visual**: Métricas e gráficos interativos
- ✅ **Gestão centralizada**: Todos os PPGs em um só lugar
- ✅ **Compliance**: LGPD, segurança, autenticação

### Diferenciais
- ✅ **Open Source**: Código aberto, customizável
- ✅ **Sem custos de licença**: Tecnologias gratuitas
- ✅ **Deploy flexível**: Funciona local ou em nuvem
- ✅ **Modular**: Componentes independentes

---

## 📞 CONTATOS DE EMERGÊNCIA

Se algo der errado:
1. **Parar tudo**: Executar `PARAR.ps1`
2. **Reiniciar**: Executar `INICIAR.ps1`
3. **Verificar logs**: `docker-compose logs -f`

---

## 🎓 BOA SORTE NA APRESENTAÇÃO!

### Lembre-se:
- ✅ Testar TUDO 10 minutos antes
- ✅ Ter o Docker rodando antes
- ✅ Manter a calma se algo der errado
- ✅ Focar nos resultados, não na tecnologia

**Você consegue! 💪**
