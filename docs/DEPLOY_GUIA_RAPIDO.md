# 🚀 GUIA RÁPIDO - DEPLOY NO RENDER.COM

## ✅ Pré-requisitos Completos

- ✅ Código no GitHub: `Matheus904-12/Prodmais`
- ✅ Commit mais recente: `5f946de` (limpeza)
- ✅ Arquivos de deploy prontos:
  - `render.yaml` ✅
  - `start.sh` ✅
  - `composer.json` ✅
  - `/api/health.php` ✅

---

## 📋 PASSO A PASSO (5 minutos)

### **1️⃣ Acessar Render.com**
```
🌐 https://render.com
```
- Clique em **"Get Started"** ou **"Sign In"**
- Faça login com sua conta GitHub

### **2️⃣ Criar New Web Service**
- Clique no botão **"+ New"** (canto superior direito)
- Selecione **"Web Service"**

### **3️⃣ Conectar Repositório**
- **Connect a repository** → Clique em **"Connect account"**
- Autorize o Render a acessar seu GitHub
- Selecione o repositório: **`Matheus904-12/Prodmais`**
- Clique em **"Connect"**

### **4️⃣ Configurar o Serviço**

Preencha os campos:

| Campo | Valor |
|-------|-------|
| **Name** | `prodmais-umc` |
| **Region** | `Oregon (US West)` ou mais próximo |
| **Branch** | `main` |
| **Root Directory** | *(deixar vazio)* |
| **Runtime** | `Native` |
| **Build Command** | `composer install --no-dev --optimize-autoloader` |
| **Start Command** | `bash start.sh` |

### **5️⃣ Adicionar Variáveis de Ambiente**

Clique em **"Advanced"** e adicione:

```env
PHP_VERSION=8.2
APP_ENV=production
APP_DEBUG=false
LGPD_ENABLED=true
DATA_RETENTION_YEARS=10
UMC_PROGRAMS=Biotecnologia,Engenharia Biomédica,Políticas Públicas,Ciência e Tecnologia em Saúde
```

### **6️⃣ Configurar Health Check**

- **Health Check Path:** `/api/health.php`
- *(O Render verificará automaticamente se o sistema está online)*

### **7️⃣ Escolher Plano**

- **Free Plan** ✅ (Suficiente para demonstração e testes)
  - 750 horas/mês gratuitas
  - HTTPS automático
  - Deploy automático
  - 0.5 GB RAM

- **Starter Plan** ($7/mês) - Para uso regular
  - RAM ilimitada
  - Sem suspensão de inatividade

### **8️⃣ Criar Web Service**

- Clique no botão azul **"Create Web Service"**
- O Render iniciará o deploy automaticamente! 🚀

---

## ⏱️ PROCESSO DE DEPLOY (3-5 minutos)

Você verá em tempo real:

```
1. ⚙️  Building... (instalando dependências)
   └── composer install --no-dev --optimize-autoloader
   
2. 🔧 Starting... (iniciando servidor)
   └── bash start.sh
   
3. ✅ Live! (deploy concluído)
   └── https://prodmais-umc.onrender.com
```

---

## 🌐 URLs DO SISTEMA

Após o deploy, seu sistema estará disponível em:

```
🏠 Dashboard Principal
   https://prodmais-umc.onrender.com/

🔐 Área Administrativa
   https://prodmais-umc.onrender.com/admin.php

📊 API de Busca
   https://prodmais-umc.onrender.com/api/search.php

💚 Health Check
   https://prodmais-umc.onrender.com/api/health.php
```

---

## ✅ VERIFICAR DEPLOY

### **Teste 1: Health Check**
Abra no navegador:
```
https://prodmais-umc.onrender.com/api/health.php
```

Resposta esperada:
```json
{
  "status": "healthy",
  "system": "Prodmais UMC",
  "version": "1.0.0",
  "checks": {
    "php": { "status": "ok", "version": "8.2.x" },
    "php_extensions": { "status": "ok" },
    "composer": { "status": "ok" }
  }
}
```

### **Teste 2: Dashboard**
Abra:
```
https://prodmais-umc.onrender.com/
```

Você deverá ver:
- ✅ Logo "Prodmais - UMC"
- ✅ Filtros dos 4 programas de pós-graduação
- ✅ Gráficos de produção científica
- ✅ Tabela de publicações

### **Teste 3: Admin**
Abra:
```
https://prodmais-umc.onrender.com/admin.php
```

Você deverá ver:
- ✅ Abas de upload (Individual / Em Lote)
- ✅ Botão de upload de XML Lattes
- ✅ Logs do sistema

---

## 🔄 DEPLOY AUTOMÁTICO

Qualquer alteração que você fizer no código:

```bash
git add .
git commit -m "feat: nova funcionalidade"
git push origin main
```

**O Render detecta automaticamente e faz novo deploy!** 🚀

---

## 📊 MONITORAMENTO

No painel do Render você pode ver:

- 📈 **Métricas:** CPU, RAM, Requests
- 📋 **Logs em tempo real:** Erros e avisos
- 🔄 **Histórico de deploys:** Todos os deploys anteriores
- ⚡ **Performance:** Tempo de resposta

---

## 🆘 TROUBLESHOOTING

### **❌ Build falhou**
- Verifique os logs no Render
- Certifique-se que `composer.json` está correto
- Verifique se o GitHub está conectado

### **❌ Servidor não inicia**
- Verifique se `start.sh` tem permissões corretas
- Veja os logs: pode ser erro de PHP ou falta de extensão

### **❌ Página não carrega**
- Aguarde 1-2 minutos (primeiro deploy demora mais)
- Verifique se o serviço está "Live" no Render
- Teste o health check primeiro

### **❌ Modo Free dorme após inatividade**
- Normal no plano gratuito
- Primeira requisição acorda o serviço (~30 segundos)
- Upgrade para Starter ($7/mês) evita isso

---

## 💰 CUSTOS

| Plano | Preço | Ideal para |
|-------|-------|------------|
| **Free** | $0 | Testes, demos, PIVIC |
| **Starter** | $7/mês | Uso regular UMC |
| **Standard** | $25/mês | Produção completa |

---

## 🎓 RESULTADO FINAL

### **✅ Sistema Prodmais UMC Online!**

- 🌐 Acessível pela internet
- 🔒 HTTPS automático (seguro)
- 📊 Dashboard funcional
- 📤 Upload de currículos Lattes
- 🔍 Busca avançada
- 📈 Estatísticas dos 4 programas
- ✅ Conformidade LGPD

### **🎉 Projeto PIVIC 2025 - DEPLOY COMPLETO!**

---

## 📞 SUPORTE

**Documentação completa:** `/docs/`
- Manual do Usuário: `MANUAL_USUARIO_PRODMAIS_UMC.md`
- Documentação Técnica: `DOCUMENTACAO_TECNICA.md`
- Troubleshooting: `TROUBLESHOOTING.md`

**Render Docs:** https://render.com/docs

---

**Desenvolvido para Universidade de Mogi das Cruzes (UMC)**  
**Projeto PIVIC 2024/2025**
