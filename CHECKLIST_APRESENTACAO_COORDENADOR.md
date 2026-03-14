# ✅ CHECKLIST - APRESENTAÇÃO PARA O COORDENADOR

## 📅 DIA DA APRESENTAÇÃO

### ⏰ 30 MINUTOS ANTES

- [ ] **Abrir Docker Desktop**
  - Aguardar até estar totalmente carregado
  - Ícone deve estar verde/normal

- [ ] **Executar script de inicialização**
  ```powershell
  .\INICIAR.ps1
  ```
  - Aguardar ~2-3 minutos
  - Verificar mensagem de sucesso

- [ ] **Executar verificação do sistema**
  ```powershell
  .\VERIFICAR.ps1
  ```
  - Confirmar que todos os ✅ aparecem
  - Se houver ❌, reiniciar: `.\PARAR.ps1` e depois `.\INICIAR.ps1`

- [ ] **Testar acesso aos serviços**
  - [ ] http://localhost:8080 - Site abre?
  - [ ] http://localhost:9200 - Elasticsearch responde?
  - [ ] http://localhost:5601 - Kibana carrega?
  - [ ] http://localhost:8081 - phpMyAdmin acessível?

- [ ] **Fazer uma busca de teste**
  - Acessar: http://localhost:8080/public/presearch.php
  - Fazer uma busca simples
  - Verificar se retorna resultados

---

### 🎯 DURANTE A APRESENTAÇÃO

#### 1️⃣ Introdução (2 min)
- [ ] Explicar o propósito do sistema
- [ ] Mencionar a arquitetura (Docker, Elasticsearch, MySQL)
- [ ] Destacar principais funcionalidades

#### 2️⃣ Demonstração de Busca (3 min)
- [ ] Mostrar a página de busca: http://localhost:8080/public/presearch.php
- [ ] Fazer uma busca por palavra-chave
- [ ] Demonstrar filtros (tipo, período, PPG)
- [ ] Mostrar velocidade dos resultados

#### 3️⃣ Dashboard e Métricas (3 min)
- [ ] Abrir: http://localhost:8080/public/dashboard.php
- [ ] Mostrar gráficos de produção
- [ ] Explicar métricas apresentadas
- [ ] Destacar visualizações interativas

#### 4️⃣ Gestão de PPGs e Pesquisadores (3 min)
- [ ] Acessar: http://localhost:8080/public/ppgs.php
- [ ] Mostrar lista de programas
- [ ] Acessar: http://localhost:8080/public/pesquisadores.php
- [ ] Explicar importação de Lattes

#### 5️⃣ Infraestrutura (2 min) - Se perguntado
- [ ] Mostrar Kibana: http://localhost:5601
- [ ] Explicar indexação do Elasticsearch
- [ ] Mencionar phpMyAdmin: http://localhost:8081
- [ ] Destacar escalabilidade

#### 6️⃣ Conclusão (2 min)
- [ ] Resumir benefícios
- [ ] Mencionar próximos passos
- [ ] Abrir para perguntas

---

### 🔧 PREPARAÇÃO TÉCNICA

#### Ter aberto em ABAS do navegador:
1. http://localhost:8080 (Página principal)
2. http://localhost:8080/public/presearch.php (Busca)
3. http://localhost:8080/public/dashboard.php (Dashboard)
4. http://localhost:8080/public/ppgs.php (PPGs)
5. http://localhost:8080/public/pesquisadores.php (Pesquisadores)
6. http://localhost:5601 (Kibana - opcional)

#### Ter aberto em janelas:
- [ ] PowerShell com: `docker-compose logs -f` (para monitorar)
- [ ] Docker Desktop (para mostrar containers rodando)
- [ ] Editor de código (se for mostrar código)

---

### 💡 PONTOS A DESTACAR

#### Técnicos:
✅ Arquitetura moderna e escalável
✅ Busca indexada com Elasticsearch (milhares de registros em milissegundos)
✅ Containerização com Docker (deploy fácil)
✅ Banco relacional (MySQL) + NoSQL (Elasticsearch)
✅ APIs REST para integrações externas

#### Funcionais:
✅ Importação automática de currículos Lattes
✅ Integração com ORCID e OpenAlex
✅ Dashboard com visualizações em tempo real
✅ Gestão centralizada de todos os PPGs
✅ Busca avançada com múltiplos filtros

#### Diferenciais:
✅ Open Source (sem custos de licença)
✅ LGPD compliance (Política de Privacidade)
✅ Sistema completo e funcional
✅ Facilmente customizável
✅ Pronto para produção

---

### ⚠️ PLANO B - SE ALGO DER ERRADO

#### Problema: Site não carrega
```powershell
docker-compose restart web
```
Aguardar 30 segundos e tentar novamente

#### Problema: Elasticsearch não responde
```powershell
docker-compose restart elasticsearch
```
Aguardar 1 minuto e tentar novamente

#### Problema: Tudo está quebrado
```powershell
.\PARAR.ps1
.\INICIAR.ps1
```
Aguardar 2-3 minutos para reiniciar tudo

#### Último recurso: Modo Local
```powershell
.\INICIAR_LOCAL.ps1
```
Usa PHP built-in server (se tiver PHP e MySQL instalados localmente)

---

### 📞 RECURSOS DE EMERGÊNCIA

#### Ver o que está acontecendo:
```powershell
# Status dos containers
docker-compose ps

# Logs gerais
docker-compose logs --tail=50

# Logs de um serviço específico
docker-compose logs -f web
```

#### Verificar serviços:
```powershell
# Elasticsearch
curl http://localhost:9200

# MySQL
docker exec prodmais_mysql mysqladmin ping -h localhost -u root -proot
```

---

### 🎯 PERGUNTAS FREQUENTES (PREPARE-SE)

**P: Quanto tempo leva para importar um currículo Lattes?**
R: Poucos segundos por currículo. O sistema processa XML automaticamente.

**P: Quantos registros o sistema suporta?**
R: Elasticsearch pode indexar milhões de documentos. Testado com milhares.

**P: É possível integrar com outros sistemas?**
R: Sim! O sistema tem APIs REST e pode exportar/importar dados.

**P: Quanto custa para manter?**
R: Software é gratuito (open source). Custos apenas de servidor/infraestrutura.

**P: Quanto tempo levou para desenvolver?**
R: [Sua resposta - seja honesto sobre o tempo e esforço]

**P: Pode funcionar em outros departamentos/universidades?**
R: Sim! É totalmente customizável e pode ser adaptado.

**P: Como é feito backup dos dados?**
R: MySQL usa volumes persistentes. Pode fazer backup via mysqldump ou phpMyAdmin.

**P: E se o Elasticsearch cair?**
R: O sistema continua funcionando, mas sem busca avançada. MySQL permanece intacto.

---

### ✨ DICAS FINAIS

1. **Respire fundo** - Você conhece o sistema!
2. **Mantenha a calma** - Se algo der errado, use os scripts de verificação
3. **Seja confiante** - O sistema está funcionando e bem feito
4. **Foque nos resultados** - Mostre valor, não apenas tecnologia
5. **Ouça as perguntas** - Não tenha pressa em responder

---

### 🎉 BOA SORTE!

**Você está preparado!** 💪

Todo o sistema está automatizado e pronto para funcionar com um clique.

**Última verificação antes de apresentar:**
```powershell
.\VERIFICAR.ps1
```

Se aparecer "🎉 SISTEMA PRONTO PARA DEMONSTRAÇÃO!" - **você está pronto!**

---

**Data da apresentação:** [Preencher]
**Horário:** [Preencher]
**Local:** [Preencher]
