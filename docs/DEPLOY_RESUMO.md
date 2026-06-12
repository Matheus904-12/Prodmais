# Prodmais UMC - Resumo Executivo de Deploy

## 📊 Visao Geral

O sistema Prodmais UMC esta pronto para deploy em 3 cenarios diferentes:

### 1️⃣ Demonstracao (Temporario)
**Objetivo:** Mostrar sistema funcionando para coordenador antes da producao  
**Duracao:** 7 dias  
**Custo:** ~$5 USD  
**Plataforma:** Railway.app ou Render.com

### 2️⃣ Producao (Locaweb)
**Objetivo:** Sistema em producao para uso real da UMC  
**Duracao:** Permanente  
**Custo:** R$ 150-535/mes  
**Plataforma:** Locaweb + Elastic Cloud ou VPS

### 3️⃣ Local (Docker)
**Objetivo:** Desenvolvimento e testes  
**Duracao:** Ilimitado  
**Custo:** Gratuito  
**Plataforma:** Docker Desktop

---

## 🚀 Opcao Recomendada: RAILWAY (Demonstracao)

### Por que Railway?
✅ Setup rapido (30 minutos)  
✅ Suporte completo a Docker  
✅ MySQL e Elasticsearch inclusos  
✅ URL publica automatica  
✅ Logs em tempo real  
✅ $5 credito gratuito (suficiente para 7 dias)

### Passo a Passo Rapido

```bash
# 1. Preparar projeto
.\prepare-deploy.ps1
# Escolha opcao 1 (Demonstracao)

# 2. Commit no GitHub
git add .
git commit -m "Deploy Railway"
git push origin main

# 3. Deploy Railway
# - Acesse railway.app
# - Login com GitHub
# - New Project > Deploy from GitHub
# - Selecione repositorio Prodmais
# - Adicione MySQL service
# - Adicione Elasticsearch service
# - Configure variaveis de ambiente
# - Gere dominio publico

# 4. Importar banco
# - Conecte no MySQL Railway
# - Importe sql/schema.sql
# - Importe sql/schema_auth.sql
# - Insira dados demo

# 5. Pronto!
# URL: https://prodmais-production-xxxx.up.railway.app
# Login: admin / Admin@2025
```

### Resultado Esperado
- ✅ Sistema 100% funcional
- ✅ Elasticsearch rodando
- ✅ Kibana acessivel
- ✅ MySQL com dados
- ✅ URL publica para compartilhar

---

## 💼 Producao Locaweb (Pos-Aprovacao)

### Arquitetura Recomendada

```
┌─────────────────────────────────────┐
│                                     │
│   LOCAWEB HOSPEDAGEM PREMIUM        │
│   - PHP 8.2 + Apache                │
│   - MySQL 8.0                       │
│   - SSL Let's Encrypt               │
│   - R$ 59,90/mes                    │
│                                     │
└─────────────┬───────────────────────┘
              │
              │ API REST
              │
┌─────────────▼───────────────────────┐
│                                     │
│   ELASTIC CLOUD                     │
│   - Elasticsearch 8.10              │
│   - Kibana                          │
│   - $95/mes (~R$ 475)               │
│                                     │
└─────────────────────────────────────┘
```

### Custo Total: ~R$ 535/mes

### Alternativa Economica: VPS

```
┌─────────────────────────────────────┐
│   LOCAWEB (PHP + MySQL)             │
│   R$ 59,90/mes                      │
└─────────────┬───────────────────────┘
              │
              │
┌─────────────▼───────────────────────┐
│   DIGITALOCEAN VPS                  │
│   - Elasticsearch + Kibana          │
│   - 2GB RAM                         │
│   - $18/mes (~R$ 90)                │
└─────────────────────────────────────┘
```

### Custo Total: ~R$ 150/mes

---

## 📋 Checklist Coordenador

### Para Aprovacao da Demonstracao

- [ ] Acessar URL demo: https://prodmais-production-xxxx.up.railway.app
- [ ] Login: admin / Admin@2025
- [ ] Testar busca de producoes
- [ ] Visualizar dashboard com metricas
- [ ] Verificar listagem de pesquisadores
- [ ] Checar PPGs e projetos
- [ ] Acessar Kibana (se configurado)
- [ ] Validar sistema de login/logout
- [ ] Testar recuperacao de senha
- [ ] Verificar responsividade mobile
- [ ] Aprovar design e funcionalidades

### Apos Aprovacao

- [ ] Definir orcamento (R$ 150/mes ou R$ 535/mes)
- [ ] Contratar hospedagem Locaweb
- [ ] Contratar Elasticsearch (Cloud ou VPS)
- [ ] Registrar dominio (prodmais.umc.br)
- [ ] Configurar email institucional (prodmais@umc.br)
- [ ] Agendar migracao de dados
- [ ] Definir usuarios administradores
- [ ] Configurar backups automaticos
- [ ] Estabelecer politica de manutencao
- [ ] Treinar equipe de gestao

---

## 🎯 Timeline Sugerida

### Semana 1: Demonstracao
```
Dia 1-2: Deploy Railway + Dados demo
Dia 3-4: Apresentacao ao coordenador
Dia 5-6: Ajustes conforme feedback
Dia 7:   Aprovacao final
```

### Semana 2-3: Preparacao Producao
```
Dia 8-10:  Contratar servicos (Locaweb + Elastic)
Dia 11-12: Configurar infraestrutura
Dia 13-15: Importar dados reais Lattes
Dia 16-17: Testes de integracao
Dia 18-19: Treinamento equipe
Dia 20:    Homologacao interna
Dia 21:    Go Live!
```

### Pos Go-Live
```
Semana 4:   Monitoramento intensivo
Mes 2-3:    Ajustes e otimizacoes
Mes 4+:     Operacao estavel
```

---

## 📞 Contatos para Suporte

### Railway (Demo)
- Documentacao: https://docs.railway.app
- Discord: https://discord.gg/railway
- Status: https://status.railway.app

### Locaweb (Producao)
- Telefone: 0800 777 4000
- Chat: https://www.locaweb.com.br/ajuda
- Email: suporte@locaweb.com.br

### Elastic Cloud
- Documentacao: https://www.elastic.co/guide
- Suporte: https://cloud.elastic.co/support
- Status: https://status.elastic.co

### Desenvolvimento (Matheus)
- Email: matheus.lucindo@umc.br
- GitHub: https://github.com/Matheus904-12

---

## 💡 Proximos Passos Imediatos

### 1. Rodar Script de Preparacao
```powershell
.\prepare-deploy.ps1
```
Escolha opcao 1 (Demonstracao)

### 2. Fazer Push GitHub
```bash
git add .
git commit -m "Sistema pronto para demonstracao"
git push origin main
```

### 3. Deploy Railway
- Acesse: https://railway.app
- Siga guia: DEPLOY_DEMO.md

### 4. Compartilhar com Coordenador
```
Assunto: Prodmais UMC - Sistema Disponivel para Avaliacao

URL: https://prodmais-production-xxxx.up.railway.app
Login: admin
Senha: Admin@2025

Documentacao: Ver README.md e DEPLOY_DEMO.md
Validade: 7 dias (ate DD/MM/YYYY)
```

---

## ❓ FAQ

**P: Quanto tempo leva o deploy demo?**  
R: 30-45 minutos (incluindo importacao de dados)

**P: O que acontece apos 7 dias no Railway?**  
R: O ambiente e pausado. Pode reativar pagando $5 ou fazer novo deploy.

**P: E possivel usar so Locaweb?**  
R: Nao. Locaweb nao suporta Elasticsearch. Precisa serviço externo.

**P: Qual a diferenca entre Elastic Cloud e VPS?**  
R: Elastic Cloud e gerenciado (mais caro, mais facil). VPS voce gerencia (mais barato, mais trabalho).

**P: Preciso de conhecimento tecnico?**  
R: Para demo Railway: Basico (seguir tutorial).  
Para producao: Intermediario ou contratar desenvolvedor.

**P: E seguro expor sistema demo publicamente?**  
R: Sim. Use senha forte, nao coloque dados sensiveis reais, e desative apos 7 dias.

---

**Ultima atualizacao:** 06/01/2026  
**Versao:** 1.0 - Deploy Ready
