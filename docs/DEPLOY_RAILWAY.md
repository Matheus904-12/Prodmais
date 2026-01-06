# 🚂 DEPLOY NO RAILWAY.APP - Guia Completo

## ✨ Por que Railway?

- ✅ **$5 grátis todo mês** (sem cartão de crédito)
- ✅ **Deploy automático** do GitHub (igual ao Render)
- ✅ **PHP 8.2 nativo**
- ✅ **HTTPS automático**
- ✅ **Interface moderna e simples**
- ✅ **Logs em tempo real**
- ✅ **Muito mais generoso que Render no free tier**

---

## 🚀 DEPLOY EM 5 MINUTOS

### **1️⃣ Criar Conta (30 segundos)**

```
🌐 https://railway.app
```

1. Clique em **"Start a New Project"** ou **"Login"**
2. Selecione **"Login with GitHub"**
3. Autorize o Railway a acessar seu GitHub
4. ✅ Pronto! Sem cartão de crédito necessário

---

### **2️⃣ Criar Novo Projeto (1 minuto)**

1. No dashboard, clique em **"New Project"**
2. Selecione **"Deploy from GitHub repo"**
3. Escolha o repositório: **`Matheus904-12/Prodmais`**
4. Railway detecta automaticamente que é PHP! 🎉

---

### **3️⃣ Configurar Build (2 minutos)**

Railway detecta automaticamente, mas você pode ajustar:

**Acesse:** Project → Settings

#### **Build Settings:**
```bash
Build Command: composer install --no-dev --optimize-autoloader
Start Command: bash start.sh
```

#### **Root Directory:**
```
(deixar vazio - usar raiz do projeto)
```

---

### **4️⃣ Adicionar Variáveis de Ambiente (1 minuto)**

**Acesse:** Project → Variables

Clique em **"+ New Variable"** e adicione:

```env
NIXPACKS_PHP_VERSION=8.2
PHP_VERSION=8.2
APP_ENV=production
APP_DEBUG=false
LGPD_ENABLED=true
DATA_RETENTION_YEARS=10
UMC_PROGRAMS=Biotecnologia,Engenharia Biomédica,Políticas Públicas,Ciência e Tecnologia em Saúde
```

**⚠️ IMPORTANTE:** `NIXPACKS_PHP_VERSION=8.2` é essencial para Railway!

---

### **5️⃣ Configurar Domínio (30 segundos)**

**Acesse:** Project → Settings → Domains

1. Clique em **"Generate Domain"**
2. Railway gera automaticamente: `prodmais-umc-production.up.railway.app`
3. Ou adicione domínio customizado (se tiver)

✅ **HTTPS é automático!**

---

### **6️⃣ Deploy! (3-5 minutos)**

Railway inicia o deploy automaticamente:

```
⚙️  Building...
├── Detectando PHP 8.2
├── Instalando Composer
├── Rodando composer install
└── Build completo! ✅

🚀 Deploying...
├── Iniciando servidor
├── Executando start.sh
└── Deploy live! ✅

🌐 Available at: https://prodmais-umc-production.up.railway.app
```

**Acompanhe em tempo real:** Clique na aba **"Deployments"**

---

## ✅ VERIFICAR DEPLOY

### **Teste 1: Health Check**
```
https://[seu-app].up.railway.app/api/health.php
```

**Resposta esperada:**
```json
{
  "status": "healthy",
  "system": "Prodmais UMC",
  "version": "1.0.0"
}
```

### **Teste 2: Dashboard**
```
https://[seu-app].up.railway.app/
```

Deve mostrar:
- ✅ Dashboard Prodmais UMC
- ✅ Filtros dos 4 programas
- ✅ Gráficos de produção
- ✅ Tabela de resultados

### **Teste 3: Admin**
```
https://[seu-app].up.railway.app/admin.php
```

---

## 📊 MONITORAMENTO

### **Dashboard Railway:**

**Métricas Disponíveis:**
- 📈 **CPU Usage**
- 💾 **Memory Usage**
- 🌐 **Request Count**
- ⏱️ **Response Times**
- 💰 **Credit Usage** ($5/mês free)

**Logs em Tempo Real:**
- Acesse a aba **"Logs"**
- Veja todos os logs do PHP
- Filtre por tipo (error, warning, info)

---

## 🔄 DEPLOY AUTOMÁTICO

Qualquer push no GitHub triggera deploy automático:

```bash
git add .
git commit -m "feat: nova funcionalidade"
git push origin main
```

🚀 **Railway detecta e faz deploy automaticamente!**

---

## 💰 CUSTOS E LIMITES

### **Free Tier (Hobby Plan):**
```
💵 $5 em crédito GRÁTIS todo mês
⏰ ~500 horas de execução/mês
💾 Até 1GB RAM
📦 Até 1GB storage
🌐 HTTPS incluso
```

**Suficiente para:**
- ✅ Projeto Prodmais UMC completo
- ✅ Demonstrações PIVIC
- ✅ Testes e desenvolvimento
- ✅ Uso moderado em produção

### **Se passar do limite:**
- Sistema pausa automaticamente
- Volta no próximo mês
- OU upgrade para $5/mês (sem limite)

---

## 🔧 TROUBLESHOOTING

### **❌ Build falha**

**Solução 1:** Verificar `NIXPACKS_PHP_VERSION`
```env
NIXPACKS_PHP_VERSION=8.2
```

**Solução 2:** Verificar logs
- Aba "Deployments" → Clique no deploy → "View Logs"

**Solução 3:** Rebuild
- Settings → Redeploy

### **❌ Erro 502 Bad Gateway**

**Causa:** Start command incorreto

**Solução:**
```bash
Start Command: bash start.sh
```

Certifique-se que `start.sh` está no repositório.

### **❌ Variáveis não carregam**

**Solução:**
- Variables → Verificar se todas foram adicionadas
- Fazer redeploy: Settings → "Redeploy"

### **❌ Sistema não encontra arquivos**

**Solução:** Verificar estrutura no repositório
```
/public/index.php ✅
/src/ ✅
/vendor/ (gerado no build) ✅
```

---

## 🎯 CONFIGURAÇÃO AVANÇADA

### **Custom Domain (Opcional):**

1. **Adicionar CNAME:**
   ```
   Type: CNAME
   Name: prodmais (ou @)
   Value: [seu-app].up.railway.app
   ```

2. **Adicionar no Railway:**
   - Settings → Domains → "Custom Domain"
   - Digite: `prodmais.seudominio.com.br`
   - SSL automático em ~5 minutos

### **Health Checks:**

Railway monitora automaticamente, mas você pode configurar:

```
Settings → Health Check
├── Path: /api/health.php
├── Port: $PORT (automático)
└── Interval: 60s
```

### **Restart Policy:**

```
Settings → Restart Policy
├── On Failure: Restart automatically ✅
└── Max Restarts: 3
```

---

## 📈 COMPARAÇÃO: RENDER vs RAILWAY

| Recurso | Render | Railway |
|---------|--------|---------|
| **Free Tier** | 750h/mês | $5 crédito/mês |
| **Deploy Auto** | ✅ | ✅ |
| **HTTPS** | ✅ | ✅ |
| **PHP 8.2** | ✅ | ✅ |
| **Setup** | Fácil | Mais fácil |
| **Dashboard** | Bom | Excelente |
| **Logs** | ✅ | ✅ Melhor |
| **Suspensão** | Sim (15min) | Não |
| **Múltiplos Apps** | Limite 1 | Vários com $5 |

🏆 **Vencedor:** Railway (mais flexível e generoso)

---

## 🎓 RESULTADO FINAL

### ✅ **Sistema Prodmais UMC no Railway:**

```
🌐 URL: https://[seu-app].up.railway.app
🔒 HTTPS: Automático
📊 Dashboard: Funcional
📤 Upload: Operacional
🔍 API: Ativa
✅ LGPD: Compliant
```

### **Próximos passos:**
1. ✅ Deploy no Railway
2. 📝 Testar todas funcionalidades
3. 📤 Fazer upload de currículos Lattes
4. 📊 Apresentar no PIVIC

---

## 📞 LINKS ÚTEIS

- **Railway Dashboard:** https://railway.app/dashboard
- **Railway Docs:** https://docs.railway.app
- **PHP Support:** https://docs.railway.app/guides/php
- **Status Page:** https://railway.statuspage.io

---

## 🎉 DEPLOY CONCLUÍDO!

**Sistema Prodmais UMC está online e rodando no Railway!**

```
✅ Deploy automático configurado
✅ HTTPS habilitado
✅ Monitoramento ativo
✅ $5 grátis todo mês
✅ Sem cartão de crédito
✅ Pronto para PIVIC 2025!
```

**Desenvolvido para Universidade de Mogi das Cruzes**  
**Projeto PIVIC 2024/2025**

---

**Dúvidas?** Consulte `TROUBLESHOOTING.md` ou a documentação Railway!
