# 🚀 INÍCIO SUPER RÁPIDO - PRODMAIS

```
████████████████████████████████████████████████████████
█                                                      █
█   🎓 PRODMAIS UMC                                    █
█   Sistema de Gestão de Produção Científica          █
█                                                      █
████████████████████████████████████████████████████████
```

## ⚡ 3 PASSOS PARA RODAR TUDO

### 1️⃣ Abrir Docker Desktop
- Aguarde carregar completamente

### 2️⃣ Executar o script
```powershell
.\INICIAR.ps1
```

### 3️⃣ Acessar o site
**http://localhost:8080**

---

## 📁 ARQUIVOS IMPORTANTES

| Arquivo | Para que serve |
|---------|----------------|
| **INICIAR.ps1** | ▶️  Inicia TODO o sistema |
| **PARAR.ps1** | ⏹️  Para o sistema |
| **VERIFICAR.ps1** | ✅ Verifica se está tudo OK |
| **INICIAR_LOCAL.ps1** | 💻 Roda sem Docker (local) |
| **GUIA_APRESENTACAO.md** | 📖 Roteiro completo da demo |
| **CHECKLIST_APRESENTACAO_COORDENADOR.md** | ✅ Checklist para amanhã |

---

## 🌐 LINKS DOS SERVIÇOS

Depois de iniciar, acesse:

| Serviço | URL |
|---------|-----|
| 🌐 **Site Principal** | http://localhost:8080 |
| 🔍 **Elasticsearch** | http://localhost:9200 |
| 📊 **Kibana** | http://localhost:5601 |
| 💾 **phpMyAdmin** | http://localhost:8081 |

---

## 🎯 PARA AMANHÃ (APRESENTAÇÃO)

### ⏰ 10 minutos antes de apresentar:

```powershell
# 1. Iniciar
.\INICIAR.ps1

# 2. Aguardar ~2 min

# 3. Verificar
.\VERIFICAR.ps1

# 4. Testar
# Abrir: http://localhost:8080
```

✅ **Se aparecer "SISTEMA PRONTO" → Você está pronto!**

---

## ⚠️ SE DER PROBLEMA

```powershell
# Parar e reiniciar
.\PARAR.ps1
.\INICIAR.ps1
```

---

## 📚 DOCUMENTAÇÃO COMPLETA

- **README.md** - Documentação técnica completa
- **GUIA_APRESENTACAO.md** - Roteiro da demonstração
- **CHECKLIST_APRESENTACAO_COORDENADOR.md** - Lista de verificação

---

## 🆘 AJUDA RÁPIDA

### Ver logs:
```powershell
docker-compose logs -f
```

### Reiniciar um serviço:
```powershell
docker-compose restart web
docker-compose restart elasticsearch
```

### Status dos containers:
```powershell
docker-compose ps
```

---

## 💡 DICA

**Mantenha simples:**
1. Execute `.\INICIAR.ps1`
2. Aguarde
3. Acesse http://localhost:8080
4. Pronto! 🎉

---

```
████████████████████████████████████████████████████████
█                                                      █
█  🎉 BOA SORTE NA APRESENTAÇÃO!                       █
█  💪 Você consegue!                                   █
█                                                      █
████████████████████████████████████████████████████████
```

---

**Última atualização:** 8 de março de 2026
