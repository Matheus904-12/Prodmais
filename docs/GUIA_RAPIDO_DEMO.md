# Guia Rapido - Deploy Demonstracao para Coordenador

## 🎯 Objetivo
Subir o sistema Prodmais UMC online em 30 minutos para demonstracao ao coordenador.

## ✅ Pre-requisitos
- [ ] Conta GitHub (gratuita)
- [ ] Codigo do Prodmais no GitHub
- [ ] 30 minutos de tempo

## 🚀 Passo a Passo (30 minutos)

### ETAPA 1: Preparar Projeto (5 min)

```powershell
# Abrir PowerShell na pasta do projeto
cd C:\app3\Prodmais

# Executar script de preparacao
.\prepare-deploy.ps1

# Escolher opcao: 1 (Demonstracao)
```

**O que acontece:**
- Cria Dockerfile
- Cria docker-compose.yml
- Cria railway.toml
- Prepara projeto para deploy

### ETAPA 2: Enviar para GitHub (5 min)

```bash
# Commitar mudancas
git add .
git commit -m "Deploy Railway - Demo para coordenador"

# Fazer push
git push origin main
```

**Verificar:** Acesse github.com/Matheus904-12/Prodmais e confirme que arquivos foram enviados

### ETAPA 3: Criar Conta Railway (3 min)

1. Acesse: https://railway.app
2. Clique em **"Login"**
3. Escolha **"Login with GitHub"**
4. Autorize Railway a acessar seu GitHub
5. Confirme email

### ETAPA 4: Deploy Aplicacao (10 min)

#### 4.1 Criar Projeto
```
1. Dashboard Railway > "New Project"
2. Escolha "Deploy from GitHub repo"
3. Selecione: Matheus904-12/Prodmais
4. Aguarde build (3-5 minutos)
```

#### 4.2 Adicionar MySQL
```
1. No projeto > "+ New" > "Database" > "MySQL"
2. Railway provisiona automaticamente
3. Copie credenciais (guarde para proxima etapa)
```

#### 4.3 Adicionar Elasticsearch
```
1. No projeto > "+ New" > "Empty Service"
2. Settings > Service Name: "elasticsearch"
3. Settings > Source > "Docker Image"
4. Image: docker.elastic.co/elasticsearch/elasticsearch:8.10.0
5. Variables > Add Variable:
   - discovery.type = single-node
   - xpack.security.enabled = false
   - ES_JAVA_OPTS = -Xms512m -Xmx512m
6. Deploy
```

#### 4.4 Configurar Variaveis (App)
```
1. Service "Prodmais" > Variables
2. Adicionar variaveis:
   DB_HOST=mysql.railway.internal
   DB_NAME=railway
   DB_USER=[copiar do MySQL service]
   DB_PASS=[copiar do MySQL service]
   ES_HOST=elasticsearch.railway.internal:9200
3. Redeploy
```

#### 4.5 Gerar URL Publica
```
1. Service "Prodmais" > Settings > Networking
2. "Generate Domain"
3. Copiar URL: https://prodmais-production-xxxx.up.railway.app
```

### ETAPA 5: Configurar Banco de Dados (5 min)

#### 5.1 Conectar no MySQL
```
Railway > MySQL service > Connect
Copie o comando de conexao CLI
```

Ou use MySQL Workbench:
- Host: [URL do Railway MySQL]
- Port: [Porta do Railway MySQL]  
- User: [User do Railway MySQL]
- Password: [Password do Railway MySQL]

#### 5.2 Importar Schemas
```sql
-- Executar no MySQL
SOURCE C:/app3/Prodmais/sql/schema.sql;
SOURCE C:/app3/Prodmais/sql/schema_auth.sql;
```

#### 5.3 Criar Usuario Admin
```sql
INSERT INTO usuarios_admin 
(username, email, password_hash, nome_completo) 
VALUES 
('admin', 'admin@umc.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Demo');
```

#### 5.4 Dados de Demonstracao (Opcional)
```sql
-- PPGs
INSERT INTO ppgs (nome, descricao, area, nota_capes) VALUES
('Biotecnologia', 'Programa de Pos-Graduacao em Biotecnologia', 'Ciencias Biologicas', '4'),
('Engenharia Biomedica', 'Programa de Pos-Graduacao em Engenharia Biomedica', 'Engenharias', '3'),
('Ciencia e Tecnologia em Saude', 'Programa de Pos-Graduacao em Ciencia e Tecnologia em Saude', 'Ciencias da Saude', '4');
```

### ETAPA 6: Testar Sistema (2 min)

```
1. Acesse URL: https://prodmais-production-xxxx.up.railway.app
2. Login: admin
3. Senha: Admin@2025
4. Navegue pelas paginas:
   - Dashboard
   - Pesquisadores
   - PPGs
   - Projetos
   - Busca
```

---

## ✉️ Email para Coordenador

```
Assunto: Prodmais UMC - Sistema Disponivel para Avaliacao

Prezado(a) Coordenador(a),

O sistema Prodmais UMC esta disponivel para avaliacao:

🌐 URL: https://prodmais-production-xxxx.up.railway.app

🔑 Acesso:
   Usuario: admin
   Senha: Admin@2025

📱 Funcionalidades:
   ✅ Busca avancada de producoes cientificas
   ✅ Dashboard com metricas e graficos
   ✅ Gestao de pesquisadores e PPGs
   ✅ Sistema de projetos de pesquisa
   ✅ Integracao Elasticsearch + Kibana
   ✅ Sistema de login seguro (LGPD compliant)

⏰ Disponibilidade:
   7 dias (ate DD/MM/YYYY)
   
📊 Ambiente:
   - Railway.app (demo)
   - MySQL + Elasticsearch funcionais
   - Dados de demonstracao

📞 Duvidas:
   Estou a disposicao para esclarecimentos.
   Email: matheus.lucindo@umc.br

Atenciosamente,
Matheus Lucindo
Desenvolvimento Prodmais UMC
```

---

## 🔧 Troubleshooting

### Problema: Build Failed
**Solucao:**
```
1. Verificar logs no Railway
2. Comum: falta alguma extensao PHP
3. Adicionar ao Dockerfile e fazer novo commit
```

### Problema: App nao acessa MySQL
**Solucao:**
```
1. Verificar variaveis de ambiente
2. DB_HOST deve ser: mysql.railway.internal
3. Verificar credenciais copiadas corretamente
```

### Problema: Elasticsearch erro 503
**Solucao:**
```
1. Elasticsearch demora ~2min para iniciar
2. Aguardar e tentar novamente
3. Verificar logs do service Elasticsearch
```

### Problema: Pagina 500 Error
**Solucao:**
```
1. Railway > Prodmais service > Logs
2. Ver erro especifico
3. Comum: config.php com erro
4. Verificar variaveis de ambiente
```

---

## 📊 Monitoramento

### Verificar Status dos Services
```
Railway Dashboard > Projeto > Services

App (Prodmais):     ✅ Running
MySQL:              ✅ Running  
Elasticsearch:      ✅ Running
```

### Ver Logs em Tempo Real
```
Railway > Service > Deployments > View Logs
```

### Metricas
```
Railway > Service > Metrics
- CPU: < 50%
- Memory: < 512MB
- Requests: monitorar
```

---

## 💰 Custos

### 7 Dias de Demonstracao
```
App PHP:           $0.50
MySQL:             $0.30
Elasticsearch:     $3.00
-----------------------------
TOTAL:             ~$4.30

Credito Railway:   $5.00 (gratuito)
Saldo final:       $0.70
```

**Nao e cobrado no cartao automaticamente!**

---

## ✅ Checklist Final

- [ ] Projeto preparado (prepare-deploy.ps1)
- [ ] Codigo no GitHub (main branch)
- [ ] Conta Railway criada
- [ ] Deploy realizado com sucesso
- [ ] MySQL provisionado
- [ ] Elasticsearch provisionado  
- [ ] Variaveis de ambiente configuradas
- [ ] URL publica gerada
- [ ] Schemas SQL importados
- [ ] Usuario admin criado
- [ ] Login funcionando
- [ ] Dados demo inseridos (opcional)
- [ ] Sistema testado completamente
- [ ] Email enviado ao coordenador

---

## 🎉 Proximos Passos (Pos-Aprovacao)

Se coordenador aprovar:

1. **Definir Orcamento**
   - Economia: R$ 150/mes (Locaweb + VPS)
   - Completo: R$ 535/mes (Locaweb + Elastic Cloud)

2. **Contratar Servicos**
   - Hospedagem Locaweb Premium
   - Elasticsearch (Cloud ou VPS)
   - Dominio prodmais.umc.br

3. **Migracao Dados**
   - Importar Lattes reais
   - Configurar usuarios
   - Testes de integracao

4. **Go Live**
   - Deploy producao
   - Treinamento equipe
   - Monitoramento

---

## 📞 Suporte

**Desenvolvimento:**
- Matheus Lucindo
- Email: matheus.lucindo@umc.br
- GitHub: @Matheus904-12

**Railway:**
- Docs: https://docs.railway.app
- Discord: https://discord.gg/railway

**UMC:**
- Email institucional: prodmais@umc.br
- DPO: dpo@umc.br

---

**Estimativa total: 30 minutos**  
**Custo: $5 (credito gratuito Railway)**  
**Validade: 7 dias**  

🚀 **Boa sorte com a demonstracao!**
