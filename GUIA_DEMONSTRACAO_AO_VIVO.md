# 🎯 GUIA PARA DEMONSTRAÇÃO AO VIVO - PRODMAIS UMC

**Data prevista:** 9 de março de 2026 (amanhã)  
**Objetivo:** Demonstrar importação de currículo Lattes do coordenador

---

## ✅ STATUS DO SISTEMA (Verificado em 08/03/2026)

### Infraestrutura
- ✅ Docker rodando corretamente
- ✅ Todos os containers ativos (web, mysql, elasticsearch, kibana, phpmyadmin)
- ✅ Site principal: http://localhost:8080
- ✅ Elasticsearch conectado e índices criados

### Funcionalidades Testadas
- ✅ Página de importação: http://localhost:8080/importar_lattes.php
- ✅ Sistema de autenticação funcionando
- ✅ Integração MySQL + Elasticsearch operacional

---

## ⚠️ IMPORTANTE: FORMATO DO CURRÍCULO

### **ACEITA:**
- ✅ **Arquivo XML do Lattes** (formato obrigatório)

### **NÃO ACEITA (atualmente):**
- ❌ **PDF** - O sistema possui parser de PDF no código, mas a interface web está configurada apenas para XML

---

## 📋 PASSO A PASSO PARA A DEMONSTRAÇÃO

### **Preparação (ANTES da demonstração):**

1. **Obter o XML do Lattes do coordenador:**
   - Acesse: https://wwws.cnpq.br/cvlattesweb/
   - Faça login com o CPF do coordenador
   - Vá em: **Curriculum Vitae → Exportar Currículo → Formato XML**
   - Salve o arquivo (ex: `curriculo_coordenador.xml`)

2. **Iniciar o sistema (se não estiver rodando):**
   ```powershell
   cd C:\app3\Prodmais
   .\INICIAR.ps1
   ```

3. **Verificar se tudo está OK:**
   ```powershell
   .\VERIFICAR.ps1
   ```
   - Deve mostrar: "SISTEMA PRONTO PARA DEMONSTRACAO!"

### **Durante a demonstração:**

1. **Abrir o sistema no navegador:**
   - URL: http://localhost:8080

2. **Fazer login como admin:**
   - Clicar em "Admin" no menu
   - Login: `admin`
   - Senha: `admin123`

3. **Acessar importação de currículos:**
   - Clicar em "Importar Lattes" ou
   - Ir direto para: http://localhost:8080/importar_lattes.php

4. **Importar o currículo:**
   - Selecionar o PPG do coordenador
   - Fazer upload do arquivo XML
   - Clicar em "Importar Currículo Lattes"
   - ⏱️ Aguardar 5-30 segundos (depende do tamanho do currículo)

5. **Verificar os resultados:**
   - Pesquisador aparecerá em: http://localhost:8080/pesquisadores.php
   - Produções indexadas no Elasticsearch
   - Dados disponíveis no dashboard

---

## 🚨 PROBLEMAS POTENCIAIS E SOLUÇÕES

### Problema 1: "O coordenador só tem PDF"
**Solução:**
- Pedir para ele acessar a Plataforma Lattes e exportar em XML
- OU: Se não tiver acesso, baixar de http://buscatextual.cnpq.br/buscatextual/ (buscar pelo nome)
- **NÃO é possível** importar PDF pela interface atual

### Problema 2: "Arquivo XML muito grande"
**Solução:**
- O sistema aceita até 50MB
- Se der timeout, aumentar em `php.ini` ou `docker-compose.yml`

### Problema 3: "Elasticsearch não está disponível"
**Solução:**
```powershell
docker restart prodmais_elasticsearch
docker restart prodmais_web
```

### Problema 4: "MySQL não conecta"
**Solução:**
- Verificar se a porta 3307 não está ocupada
- Reiniciar: `docker restart prodmais_mysql`

---

## 🎬 ROTEIRO DE DEMONSTRAÇÃO SUGERIDO

### 1. Introdução (2 min)
- Mostrar a página inicial
- Explicar o objetivo do sistema
- Mostrar a busca pública

### 2. Demonstração de Importação (5 min)
- Fazer login como admin
- Acessar página de importação
- Explicar o processo
- **Fazer upload do XML do coordenador AO VIVO**
- Mostrar o progresso

### 3. Visualização dos Resultados (5 min)
- Acessar perfil do pesquisador importado
- Mostrar produções científicas
- Demonstrar busca funcionando
- Mostrar dashboard com estatísticas

### 4. Funcionalidades Extras (3 min)
- Kibana (visualizações)
- Exportação de dados
- Filtros e buscas avançadas

---

## 📊 DADOS DO SISTEMA

- **Índices Elasticsearch criados:** 4
  - `prodmais_umc` (Produções)
  - `prodmais_umc_cv` (Currículos)
  - `prodmais_umc_ppg` (PPGs)
  - `prodmais_umc_projetos` (Projetos)

- **Banco de dados:** MySQL 8.0
- **Porta web:** 8080
- **Porta MySQL:** 3307

---

## ✅ CHECKLIST PRÉ-DEMONSTRAÇÃO

**No dia anterior (HOJE - 08/03):**
- [ ] Confirmar com coordenador que ele tem o XML do Lattes
- [ ] Testar importação com um XML de exemplo
- [ ] Verificar se todos os containers estão rodando
- [ ] Preparar backup do sistema

**1 hora antes da demonstração:**
- [ ] Reiniciar todos os containers: `docker-compose restart`
- [ ] Executar `.\VERIFICAR.ps1`
- [ ] Testar acesso ao site
- [ ] Limpar banco de dados se necessário (para demonstração limpa)
- [ ] Ter o XML do coordenador salvo no desktop

**Durante a demonstração:**
- [ ] Fechar programas desnecessários
- [ ] Desativar notificações
- [ ] Aumentar zoom do navegador para melhor visualização
- [ ] Ter Kibana aberto em outra aba

---

## 🆘 COMANDOS DE EMERGÊNCIA

```powershell
# Reiniciar tudo
docker-compose restart

# Ver logs se algo der errado
docker logs prodmais_web --tail 50

# Reiniciar apenas o web server
docker restart prodmais_web

# Verificar status
.\VERIFICAR.ps1

# Parar e reconstruir (último recurso)
.\PARAR.ps1
.\REBUILD.ps1
```

---

## 📞 CONTATOS DE SUPORTE

Se algo der errado durante a demonstração:
1. Manter a calma
2. Explicar que é um sistema em desenvolvimento
3. Usar dados de exemplo se a importação falhar
4. Ter um plano B: mostrar screenshots/vídeos pré-gravados

---

**Última verificação:** 08/03/2026 - 21:00  
**Status:** ✅ SISTEMA PRONTO PARA DEMONSTRAÇÃO

---

## 📝 NOTAS ADICIONAIS

- O sistema está configurado para ambiente de desenvolvimento (localhost)
- A importação pode levar alguns segundos dependendo do tamanho do currículo
- O Elasticsearch pode demorar 1-2 segundos para indexar os dados
- Recomenda-se ter uma conexão de internet estável para carregar os recursos CDN (Bootstrap, Font Awesome)
