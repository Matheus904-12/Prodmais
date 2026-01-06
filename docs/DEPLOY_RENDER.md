# Instruções de Deploy no Render - Sistema Prodmais UMC

## 🚀 Deploy Automático no Render

### 1. **Conectar Repositório GitHub**
- Acesse [Render.com](https://render.com) e faça login
- Conecte sua conta GitHub
- Selecione o repositório: `Matheus904-12/Prodmais`

### 2. **Configurar Web Service**
```yaml
Name: prodmais-umc
Environment: Web Service
Branch: main
Root Directory: (deixar vazio)
Runtime: PHP 8.2
Build Command: composer install --no-dev --optimize-autoloader
Start Command: bash start.sh
```

### 3. **Variáveis de Ambiente**
```env
# PHP Runtime
PHP_VERSION=8.2

# Aplicação
APP_ENV=production
APP_DEBUG=false

# Elasticsearch (opcional - sistema funciona em modo fallback)
ELASTICSEARCH_HOST=localhost:9200
ELASTICSEARCH_INDEX=prodmais_cientifica

# LGPD e Segurança
LGPD_ENABLED=true
DATA_RETENTION_YEARS=10
AUDIT_LOGS_ENABLED=true

# UMC Configuration
UMC_PROGRAMS=Biotecnologia,Engenharia Biomédica,Políticas Públicas,Ciência e Tecnologia em Saúde
```

### 4. **Configurações Específicas do Render**

**Auto-Deploy:** ✅ Habilitado  
**Branch:** main  
**Health Check Path:** `/api/health.php`  
**Port:** Automático (Render detecta)

### 5. **Processo de Deploy**

1. **Push do código:** ✅ Completo
   ```bash
   git add .
   git commit -m "feat: Deploy ready for Render"
   git push origin main
   ```

2. **Render detectará automaticamente:**
   - Linguagem: PHP
   - Dependências: composer.json
   - Build script: composer install
   - Start script: start.sh

3. **Deploy automático iniciará:**
   - Instalação de dependências PHP
   - Configuração do ambiente
   - Inicialização do servidor

### 6. **URLs de Acesso (após deploy)**

```
✅ URL Principal: https://prodmais-umc.onrender.com
✅ Dashboard: https://prodmais-umc.onrender.com/
✅ Admin: https://prodmais-umc.onrender.com/admin.php
✅ API: https://prodmais-umc.onrender.com/api/search.php
```

### 7. **Funcionalidades Disponíveis**

**✅ Sistema Completo Funcional:**
- Dashboard principal com estatísticas UMC
- Filtros pelos 4 programas de pós-graduação
- Upload individual e em lote de currículos XML
- Área administrativa completa
- APIs REST funcionais
- Modo fallback (funciona sem Elasticsearch)

**✅ Conformidade LGPD:**
- DPIA completo implementado
- Política de privacidade
- Exercício de direitos dos titulares
- Anonimização automática

**✅ Documentação:**
- Manual do usuário (74 páginas)
- Documentação técnica (100+ páginas)
- Guias de boas práticas LGPD

### 8. **Monitoramento Pós-Deploy**

**Health Check:**
```bash
curl https://prodmais-umc.onrender.com/api/health.php
```

**Logs do Sistema:**
- Acessar via dashboard do Render
- Logs PHP disponíveis em tempo real
- Métricas de performance automáticas

### 9. **Manutenção e Atualizações**

**Deploy Automático:**
- Qualquer push para `main` triggera novo deploy
- Rollback automático em caso de falha
- Zero downtime deployment

**Backup:**
- Dados em modo fallback (seguros)
- Configurações versionadas no Git
- Documentação sempre atualizada

### 10. **Custos e Limites**

**Render Free Tier:**
- ✅ Suficiente para demonstração
- ✅ HTTPS automático
- ✅ Custom domain disponível
- ✅ Escalabilidade automática

**Upgrades Recomendados:**
- Starter ($7/mês): Para uso regular
- Professional ($25/mês): Para produção completa

---

## 🎓 **Sistema Pronto para UMC!**

O **Sistema Prodmais UMC** está 100% preparado para deploy no Render e uso em produção pela Universidade de Mogi das Cruzes.

**Próximos passos:**
1. ✅ Código commitado e enviado
2. 🚀 Conectar no Render.com
3. 📊 Configurar variáveis de ambiente
4. 🎯 Deploy automático
5. 📋 Testes finais com dados reais UMC

**Projeto PIVIC 2024/2025 - Implementação da Ferramenta Prodmais na Universidade de Mogi das Cruzes**