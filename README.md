# Prodmais UMC - Sistema de Gestão de Produção Científica

Sistema completo para gerenciamento da produção científica dos Programas de Pós-Graduação da Universidade de Mogi das Cruzes.

## ⚡ INÍCIO RÁPIDO (Recomendado)

### 🐳 Opção 1: Com Docker (Mais Fácil)

**Pré-requisito:** Docker Desktop instalado

```powershell
# Iniciar tudo (MySQL + Elasticsearch + Web)
.\INICIAR.ps1

# Verificar se está tudo OK
.\VERIFICAR.ps1

# Parar tudo
.\PARAR.ps1
```

**Pronto!** Acesse: http://localhost:8080

---

### 💻 Opção 2: Sem Docker (Local)

**Pré-requisitos:** PHP 8.0+, MySQL, Elasticsearch 8.x

```powershell
.\INICIAR_LOCAL.ps1
```

**Acesse:** http://localhost:8000

---

## 🚀 Funcionalidades

### Core & Extensibilidade
- ✅ **Arquitetura de Plugins:** Extensível via Hooks (Actions/Filters) estilo WordPress.
- ✅ **Busca Avançada:** Multi-índice com Elasticsearch 8.x.
- ✅ **Importação Lattes:** Extração robusta de XML (Artigos, Livros, Patentes, Softwares).
- ✅ **Integração:** ORCID, OpenAlex e BrCris.
- ✅ **Dashboard Premium:** Métricas interativas e visualizações modernas.

### Segurança & Conformidade
- ✅ **LGPD:** Total conformidade (Art. 7º, §4º) com logs de auditoria e anonimização.
- ✅ **Segurança:** Autenticação bcrypt, proteção brute-force e sessões seguras.

---

## 🔌 Sistema de Plugins
O Prodmais agora permite a linkagem de plugins para expansão conforme o uso.
*   **Localização:** Pasta `/plugins/`
*   **Funcionamento:** Baseado em `HookManager.php` (estilo WordPress).
*   **Hooks Disponíveis:** `dashboard_header`, `log_action`, entre outros.

---

## 📋 Serviços e Portas

| Serviço | URL | Descrição |
|---------|-----|-----------|
| **Site Principal** | http://localhost:8080 | Interface principal |
| **Elasticsearch** | http://localhost:9200 | Motor de busca |
| **Kibana** | http://localhost:5601 | Visualização de dados |
| **phpMyAdmin** | http://localhost:8081 | Gerenciamento MySQL |

---

## 🔗 Links Uteis

- [Plataforma Lattes](http://lattes.cnpq.br/)
- [ORCID](https://orcid.org/)
- [OpenAlex](https://openalex.org/)
- [Elasticsearch Docs](https://www.elastic.co/guide/)
- [LGPD](https://www.gov.br/cidadania/pt-br/acesso-a-informacao/lgpd)

---

## ☁️ Hospedagem e Deploy

Temos guias detalhados para diferentes ambientes:
1.  **[OCI_DEPLOY_GUIDE.md](docs/OCI_DEPLOY_GUIDE.md)** - ☁️ Guia para Oracle Cloud (Always Free).
2.  **[DEPLOY_LOCAWEB.md](docs/DEPLOY_LOCAWEB.md)** - 💼 Guia para Locaweb (PHP + MySQL).

---

## 📂 Estrutura de Pastas

```
Prodmais/
├── config/            # Configurações
├── data/              # Dados (Lattes XML, SQLite, Backups)
├── docs/              # Documentação técnica e Guias de Deploy
├── plugins/           # Extensões do sistema (Plugins)
├── public/            # Document Root (Páginas Web e CSS)
├── sql/               # Schemas MySQL e SQLite
├── src/               # Core Logics (HookManager, PluginLoader, Parsers)
└── vendor/            # Dependências Composer
```

---

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/NovaFuncionalidade`)

- Matheus Lucindo - Desenvolvimento principal
- Orientacao: Prof. Dr. [Nome]

## 📞 Suporte

- Email: prodmais@umc.br
- DPO: dpo@umc.br
- Telefone: (11) 4798-7000
