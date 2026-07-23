# 🚀 Prodmais UMC

![Versão](https://img.shields.io/badge/vers%C3%A3o-2.0.0-blue)
![Arquitetura](https://img.shields.io/badge/arquitetura-modular-green)
![PHP](https://img.shields.io/badge/PHP-8.2-777bb4)
![Docker](https://img.shields.io/badge/Docker-ready-2496ed)

Sistema moderno para gestão e análise da produção científica dos Programas de Pós-Graduação da **Universidade de Mogi das Cruzes**.

---

## 👨‍💻 Criador
**Matheus Lucindo dos Santos** — *Criador e Desenvolvedor Principal*

---

## ⚡ Início Rápido

O sistema é totalmente dockerizado para facilitar o ambiente de desenvolvimento.

```powershell
# Iniciar o ecossistema completo
.\scripts\INICIAR.ps1

# Acessar a aplicação
http://localhost:8000
```

*   **Elasticsearch:** `http://localhost:9200`
*   **Kibana:** `http://localhost:5601`
*   **Admin:** `http://localhost:8000/login.php` (User: admin / Pass: admin123)

---

## 🏗️ Nova Arquitetura Modular
O Prodmais foi reestruturado para seguir padrões de **Clean Architecture**, garantindo manutenibilidade e escalabilidade:

*   **`src/Core/`**: Extensibilidade via Hook Manager (Estilo WordPress).
*   **`src/Infrastructure/`**: Camada de dados (Elasticsearch, MySQL, SQLite).
*   **`src/Domain/`**: Inteligência de negócio e importação Lattes.
*   **`src/View/`**: Sistema de componentes e páginas modulares.

---

## 🌟 Principais Funcionalidades
- ✅ **Busca Ultra-rápida:** Motor Elasticsearch 8.x para grandes volumes de dados.
- ✅ **Importação Lattes:** Parser robusto para CV Lattes (Artigos, Livros, Patentes).
- ✅ **Integrações:** ORCID, OpenAlex e Crossref sincronizados.
- ✅ **LGPD Compliant:** Logs de auditoria e anonimização de dados sensíveis.
- ✅ **Exportação:** Suporte a BibTeX, RIS, CSV e JSON.

---

## 📂 Organização
- **`/public`**: Apenas arquivos estáticos e entry-points.
- **`/src`**: Código fonte protegido e organizado por camadas.
- **`/data`**: Armazenamento seguro de logs, bancos SQLite e backups.

---

## 🎨 Design System

A identidade visual do Prodmais (cores, tipografia, componentes, padrões de UI) está documentada e disponível para consulta em:

**[design-system-fawn-three.vercel.app](https://design-system-fawn-three.vercel.app/)**

Use essa referência antes de criar ou alterar qualquer tela do sistema, para manter consistência visual entre as páginas.

---

## 📞 Suporte & Contato
- **Universidade de Mogi das Cruzes**

---
© 2025 **Prodmais UMC** — *Desenvolvido por Matheus Lucindo dos Santos*
