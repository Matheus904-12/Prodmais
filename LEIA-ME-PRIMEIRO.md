# 🚀 INICIAR SISTEMA - GUIA SUPER RÁPIDO

## Para iniciar TODO o sistema (incluindo Elasticsearch):

### Windows PowerShell:
```powershell
.\INICIAR.ps1
```

## ✅ O que este script faz:

1. ✅ Verifica se o Docker está rodando
2. ✅ Para containers antigos (se houver)
3. ✅ Inicia todos os serviços:
   - MySQL (banco de dados)
   - Elasticsearch (busca)
   - Kibana (visualização)
   - phpMyAdmin (gerenciamento BD)
   - Apache/PHP (servidor web)
4. ✅ Verifica se tudo iniciou corretamente
5. ✅ Abre o navegador automaticamente

## 📋 Outros comandos úteis:

### Parar o sistema:
```powershell
.\PARAR.ps1
```

### Verificar se está tudo OK:
```powershell
.\VERIFICAR.ps1
```

### Ver logs em tempo real:
```powershell
docker-compose logs -f
```

## 🌐 Acessar o sistema:

- **Site Principal:** http://localhost:8080
- **Elasticsearch:** http://localhost:9200
- **Kibana:** http://localhost:5601
- **phpMyAdmin:** http://localhost:8081

## ⚡ Para a apresentação de amanhã:

### 10 minutos antes:
1. Abrir Docker Desktop
2. Executar `.\INICIAR.ps1`
3. Aguardar mensagem de sucesso
4. Executar `.\VERIFICAR.ps1` para confirmar
5. Pronto! 🎉

## ⚠️ Problemas?

Se algo der errado:
```powershell
.\PARAR.ps1
.\INICIAR.ps1
```

## 📖 Guia completo:

Ver [GUIA_APRESENTACAO.md](GUIA_APRESENTACAO.md) para roteiro detalhado da demonstração.

---

**Boa sorte na apresentação! 🎓**
