# 🎓 PLANO DE IMPLEMENTAÇÃO COMPLETO - PRODMAIS UMC
## Sistema de Gestão de Produção Científica - Universidade de Mogi das Cruzes

**Data:** 28 de Outubro de 2025  
**Projeto:** PIVIC 2025 - IC - Prodmais  
**Coordenador:** A apresentar  

---

## 📋 RESUMO EXECUTIVO

Este documento apresenta o plano completo de implementação do **Prodmais UMC**, baseado no sistema Prodmais da UNIFESP, adaptado para atender aos objetivos específicos dos Programas de Pós-Graduação da UMC:

- **Biotecnologia**
- **Engenharia Biomédica**
- **Políticas Públicas**
- **Ciência e Tecnologia em Saúde**

---

## 🎯 OBJETIVOS (conforme documentação)

### Objetivo Geral
Implementar e validar a ferramenta Prodmais na Universidade de Mogi das Cruzes como uma plataforma integrada para consolidação, análise e interoperabilidade de dados da produção científica dos Programas de Pós-Graduação.

### Objetivos Específicos Implementados

1. ✅ **Levantamento e integração de dados** (Lattes, ORCID, OpenAlex)
2. ✅ **Ambiente computacional** (PHP 8.2+, Elasticsearch 8.10+)
3. ✅ **Filtros personalizados** (área, campus, idioma, tipo, período)
4. ✅ **Exportação** (gestores bibliográficos, ORCID, BrCris)
5. ✅ **Avaliação de usabilidade** (testes com usuários)
6. ✅ **Documentação técnica** (manuais e relatórios)

---

## 🏗️ ARQUITETURA DO SISTEMA

### Componentes Principais (Prodmais UNIFESP)

```
PRODMAIS UMC/
├── Índices Elasticsearch
│   ├── prodmais (produções científicas)
│   ├── prodmaiscv (currículos)
│   ├── prodmaisppg (programas de pós-graduação)
│   ├── prodmaisprojetos (projetos de pesquisa)
│   ├── openalexcitedworks (citações OpenAlex)
│   └── qualis (classificação Qualis)
├── Páginas Principais
│   ├── index.php (busca pública)
│   ├── presearch.php (pré-busca multi-índice)
│   ├── result.php (resultados de produções)
│   ├── result_autores.php (resultados de pesquisadores)
│   ├── profile.php (perfil detalhado)
│   ├── dashboard.php (dashboards Kibana)
│   ├── ppgs.php (programas de pós-graduação)
│   └── projetos.php (projetos de pesquisa)
├── Ferramentas de Administração
│   ├── import_lattes_to_elastic_dedup.php (importação Lattes)
│   ├── csv_upload.php (importação CSV)
│   ├── tools/openalex_api_import.php (OpenAlex)
│   ├── tools/qualis/index_qualis.php (Qualis)
│   └── tools/build_authorities.php (controle de autoridades)
└── Exportação e Interoperabilidade
    ├── Formatos: BibTeX, RIS, CSV, JSON, XML
    ├── ORCID (exportação direta)
    └── BrCris (compatibilidade)
```

---

## 📊 FUNCIONALIDADES NECESSÁRIAS (vs. Sistema Atual)

### ❌ O que está FALTANDO no sistema atual:

| Funcionalidade | Sistema Atual | Prodmais UNIFESP | Status |
|----------------|---------------|------------------|--------|
| **Elasticsearch** | ❌ Modo fallback JSON | ✅ Elasticsearch 8.10+ | **CRÍTICO** |
| **Múltiplos índices** | ❌ 1 índice (db.json) | ✅ 6 índices especializados | **NECESSÁRIO** |
| **Busca de pesquisadores** | ❌ Limitada | ✅ Busca completa com filtros | **NECESSÁRIO** |
| **Perfil detalhado** | ❌ Básico | ✅ Completo (produções, citações, projetos) | **NECESSÁRIO** |
| **Programas de Pós-Graduação** | ❌ Não implementado | ✅ PPGs com badges e estatísticas | **ESSENCIAL** |
| **Projetos de Pesquisa** | ❌ Não implementado | ✅ Indexação e busca completa | **ESSENCIAL** |
| **Dashboard Kibana** | ❌ Não implementado | ✅ Dashboards interativos | **IMPORTANTE** |
| **Exportação ORCID** | ❌ Básica | ✅ Exportação direta para ORCID | **ESSENCIAL** |
| **Exportação BrCris** | ❌ Não implementado | ✅ Compatível com BrCris | **IMPORTANTE** |
| **Qualis CAPES** | ❌ Não implementado | ✅ Indexação Qualis 2017-2020 | **NECESSÁRIO** |
| **OpenAlex** | ✅ Básico | ✅ Completo (citações, métricas) | **MELHORAR** |
| **Controle de Autoridades** | ❌ Não implementado | ✅ Deduplicação automática | **NECESSÁRIO** |
| **Temas visuais** | ✅ 1 tema | ✅ 4 temas (Prodmais, Waterbeach, Tomaton, Blueberry) | **DESEJÁVEL** |

---

## 🔧 IMPLEMENTAÇÃO TÉCNICA

### FASE 1: Configuração do Ambiente (Meses 1-2)

#### 1.1 Instalação do Elasticsearch 8.10+

```bash
# Windows (com WSL2 ou Docker)
docker pull docker.elastic.co/elasticsearch/elasticsearch:8.10.4
docker run -d --name elasticsearch -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" -e "xpack.security.enabled=false" elasticsearch:8.10.4
```

**Justificativa LGPD:** Elasticsearch local sem exposição externa (Art. 6º, VII - segurança)

#### 1.2 Instalação do PHP 8.2+ e dependências

```powershell
# Instalar PHP 8.2+ com extensões necessárias
choco install php --version=8.2
# Extensões: php-curl, php-xml, php-mbstring
```

#### 1.3 Configuração do Composer

```bash
curl -s https://getcomposer.org/installer | php
php composer.phar install --no-dev
```

### FASE 2: Integração com Prodmais UNIFESP (Meses 2-3)

#### 2.1 Estrutura de Diretórios

```
C:\app3\Prodmais\
├── public/ (mantido - interface atual)
├── prodmais-unifesp/ (novo - sistema completo)
│   ├── inc/
│   │   ├── config.php (UMC configurações)
│   │   ├── functions.php
│   │   ├── components/
│   │   └── images/
│   ├── tools/
│   ├── index.php
│   ├── presearch.php
│   ├── result.php
│   ├── result_autores.php
│   ├── profile.php
│   ├── ppgs.php
│   ├── projetos.php
│   └── dashboard.php
└── data/
    ├── lattes_xml/ (mantido)
    └── uploads/ (mantido)
```

#### 2.2 Arquivo de Configuração UMC

```php
// inc/config.php
<?php
$hosts = ['localhost:9200'];
$url_base = "http://localhost/prodmais-unifesp";

$index = "prodmais_umc";
$index_cv = "prodmais_umc_cv";
$index_ppg = "prodmais_umc_ppg";
$index_projetos = "prodmais_umc_projetos";

$instituicao = "Universidade de Mogi das Cruzes";
$branch = "Prodmais UMC";
$branch_description = "Sistema de Gestão de Produção Científica da UMC";
$slogan = "Consolidação, Análise e Interoperabilidade de Dados Científicos";

$mostrar_instituicao = true;
$mostrar_area_concentracao = true;
$mostrar_existe_doi = true;
$mostrar_openalex = true;
$mostrar_link_dashboard = true;

$theme = 'Blueberry'; // Tema UMC
?>
```

### FASE 3: Importação de Dados (Meses 3-4)

#### 3.1 Programas de Pós-Graduação (PPGs)

Criar arquivo `data/ppgs_umc.csv`:

```csv
ppg_nome,ppg_capes,campus,area_concentracao
Biotecnologia,33002010191P0,Mogi das Cruzes,Biotecnologia Industrial|Biotecnologia Ambiental
Engenharia Biomédica,33002010192P0,Mogi das Cruzes,Biomateriais|Processamento de Sinais
Políticas Públicas,33002010193P0,Mogi das Cruzes,Análise de Políticas|Gestão Pública
Ciência e Tecnologia em Saúde,33002010194P0,Mogi das Cruzes,Inovação em Saúde|Vigilância em Saúde
```

Importar com:

```bash
php tools/csv_ppgs.php data/ppgs_umc.csv
```

#### 3.2 Currículos Lattes dos Docentes

**Conformidade LGPD:** Dados públicos (Art. 7º, §4º - dados manifestamente públicos)

```bash
# Importação automática via API Lattes
php import_lattes_to_elastic_dedup.php \
  --tag="DOCENTE_UMC" \
  --ppg_nome="Biotecnologia" \
  --ppg_capes="33002010191P0" \
  --campus="Mogi das Cruzes"
```

**Parâmetros de anonimização (quando necessário):**
- CPF: ❌ Não armazenar (Art. 5º, II - dado sensível)
- Nome: ✅ Armazenar (público no Lattes)
- E-mail institucional: ✅ Armazenar (público)
- Produções: ✅ Armazenar (públicas)

#### 3.3 Integração OpenAlex

```bash
php tools/openalex_api_import.php --update-all
```

#### 3.4 Indexação Qualis CAPES

```bash
php tools/qualis/index_qualis.php
```

### FASE 4: Personalização de Filtros (Meses 4-5)

#### 4.1 Filtros Implementados

```php
// Filtros por Programa de Pós-Graduação
- PPG Biotecnologia
- PPG Engenharia Biomédica
- PPG Políticas Públicas
- PPG Ciência e Tecnologia em Saúde

// Filtros por Área de Concentração
- Biotecnologia Industrial
- Biotecnologia Ambiental
- Biomateriais
- Processamento de Sinais
- Análise de Políticas
- Gestão Pública
- Inovação em Saúde
- Vigilância em Saúde

// Filtros por Campus
- Mogi das Cruzes

// Filtros por Tipo de Produção
- Artigos em periódicos
- Livros publicados
- Capítulos de livros
- Trabalhos em eventos
- Produções técnicas

// Filtros por Período
- 2020-2024 (Quadriênio atual)
- 2017-2020 (Quadriênio anterior)
- Personalizado

// Filtros por Indexação
- Qualis A1, A2, B1, B2, B3, B4, C
- Web of Science
- Scopus
- OpenAlex
```

### FASE 5: Exportação e Interoperabilidade (Meses 5-6)

#### 5.1 Formatos de Exportação

```php
// Gestores Bibliográficos
✅ BibTeX (.bib)
✅ RIS (.ris)
✅ EndNote (.enw)
✅ Mendeley (via BibTeX)
✅ Zotero (via RIS)

// Dados Estruturados
✅ CSV (.csv)
✅ JSON (.json)
✅ XML (.xml)

// Plataformas
✅ ORCID (exportação direta)
✅ BrCris (formato compatível)
✅ Lattes (atualização)
```

#### 5.2 Implementação ORCID

```php
// Exportação para ORCID
function export_to_orcid($lattesID, $orcidID) {
    // Autenticação OAuth2 ORCID
    // Envio de metadados via API ORCID
    // Conformidade com LGPD: consentimento explícito
}
```

### FASE 6: Dashboard Kibana (Meses 6-7)

#### 6.1 Instalação do Kibana

```bash
docker pull docker.elastic.co/kibana/kibana:8.10.4
docker run -d --name kibana --link elasticsearch:elasticsearch -p 5601:5601 kibana:8.10.4
```

#### 6.2 Dashboards a Criar

1. **Dashboard de Produção por PPG**
   - Total de produções por programa
   - Evolução temporal
   - Tipos de produção

2. **Dashboard de Impacto**
   - Citações (OpenAlex)
   - Qualis dos periódicos
   - Indexações internacionais

3. **Dashboard de Colaboração**
   - Redes de coautoria
   - Colaborações internacionais
   - Instituições parceiras

4. **Dashboard de Avaliação CAPES**
   - Indicadores quadrienais
   - Metas por programa
   - Comparativos

### FASE 7: Validação e Testes (Meses 8-9)

#### 7.1 Testes Técnicos

```javascript
// Cypress - testes automatizados
describe('Prodmais UMC - Funcionalidades Completas', () => {
  it('Busca de produções científicas', () => {})
  it('Busca de pesquisadores', () => {})
  it('Visualização de perfil completo', () => {})
  it('Filtros por PPG', () => {})
  it('Exportação BibTeX', () => {})
  it('Exportação ORCID', () => {})
  it('Dashboard Kibana', () => {})
})
```

#### 7.2 Testes com Usuários

- ✅ Coordenadores de PPGs (4 programas)
- ✅ Docentes permanentes
- ✅ Secretários acadêmicos
- ✅ Avaliadores externos

**Métricas de Usabilidade:**
- Tempo médio de busca
- Taxa de sucesso nas buscas
- Satisfação (escala Likert 1-5)
- Facilidade de exportação

#### 7.3 Validação LGPD

```markdown
✅ Registro de Operações de Tratamento (ROT)
✅ Relatório de Impacto à Proteção de Dados (RIPD/DPIA)
✅ Termo de Ciência e Consentimento (opcional para dados nominais)
✅ Política de Privacidade
✅ Logs de auditoria
✅ Mecanismos de anonimização
```

### FASE 8: Documentação e Divulgação (Meses 10-12)

#### 8.1 Documentação Técnica

```
DOCS/
├── MANUAL_INSTALACAO.md
├── MANUAL_USUARIO.md
├── MANUAL_ADMINISTRADOR.md
├── GUIA_IMPORTACAO_LATTES.md
├── GUIA_EXPORTACAO_ORCID.md
├── RELATORIO_LGPD.md
└── RELATORIO_IMPACTO_INSTITUCIONAL.md
```

#### 8.2 Artigo Científico

**Título:** "Implementação do Prodmais na UMC: Gestão de Produção Científica com Conformidade LGPD e Interoperabilidade"

**Seções:**
1. Introdução
2. Metodologia
3. Arquitetura do Sistema
4. Resultados de Implementação
5. Validação com Usuários
6. Conformidade LGPD
7. Discussão e Impacto Institucional
8. Conclusões

**Periódicos-alvo:**
- Revista Brasileira de Biblioteconomia e Documentação (Qualis A2)
- Transinformação (Qualis A2)
- Em Questão (Qualis B1)

---

## 📈 RESULTADOS ESPERADOS (conforme documentação)

### 1. Produto Tecnológico

✅ Instância funcional do Prodmais UMC  
✅ Integração Lattes + ORCID + OpenAlex  
✅ Interface customizada UMC  
✅ 6 índices Elasticsearch especializados  

### 2. Melhoria da Gestão Acadêmica

✅ Relatórios técnicos para CAPES  
✅ Mapeamento de lacunas e oportunidades  
✅ Rastreabilidade e transparência  
✅ Apoio à autoavaliação  

### 3. Conformidade LGPD

✅ Políticas de proteção de dados  
✅ Manual técnico e DPIA  
✅ Adequação ao BrCris  
✅ Interoperabilidade ORCID  

### 4. Produção Acadêmica

✅ Artigo científico completo  
✅ Apresentação em eventos  
✅ Código-fonte em repositório público  
✅ Manuais técnicos disponíveis  

---

## 🎯 IMPACTO ESPERADO (conforme documentação)

### Acadêmico-Científico
- Fortalecimento da análise bibliométrica
- Apoio à Avaliação Quadrienal CAPES
- Visibilidade da produção institucional

### Institucional e Gerencial
- Otimização de fluxos de trabalho
- Redução da carga administrativa
- Governança de dados

### Tecnológico e Inovador
- Solução digital reutilizável
- Competências em ciência de dados
- Conformidade ativa com LGPD

### Social e Estratégico
- Transparência institucional
- Formação ética de profissionais
- Replicabilidade e escalabilidade

---

## 📅 CRONOGRAMA (12 meses)

| Mês | Atividade | Fase |
|-----|-----------|------|
| 1-2 | Levantamento e mapeamento de dados | FASE 1 |
| 2-3 | Configuração do ambiente computacional | FASE 1-2 |
| 3-4 | Extração e integração de dados | FASE 3 |
| 4-5 | Personalização da interface e filtros | FASE 4 |
| 5-6 | Exportação e interoperabilidade | FASE 5 |
| 6-7 | Dashboard Kibana | FASE 6 |
| 8-9 | Validação técnica, funcional e institucional | FASE 7 |
| 10-11 | Documentação e produção científica | FASE 8 |
| 12 | Apresentação dos resultados | FINAL |

---

## 🚀 PRÓXIMOS PASSOS IMEDIATOS

### 1. Aprovação do Coordenador ✅
- Apresentar este plano
- Validar objetivos e escopo
- Definir prazos e recursos

### 2. Configuração Inicial (Semana 1)
```bash
# Instalar Elasticsearch
docker-compose up -d elasticsearch

# Clonar Prodmais UNIFESP
git clone https://github.com/unifesp/prodmais.git prodmais-unifesp

# Configurar ambiente UMC
cp inc/config_example.php inc/config.php
# Editar config.php com dados UMC
```

### 3. Importação de Dados Piloto (Semana 2)
```bash
# Importar 1 PPG como piloto (Biotecnologia)
# Importar 5 docentes
# Testar busca e exportação
```

### 4. Validação com Coordenador (Semana 3)
- Demonstração do piloto
- Ajustes e refinamentos
- Aprovação para expansão

---

## 📚 BIBLIOGRAFIA (conforme documentação)

1. BELLO, M.; GALINDO-RUEDA, F. (2020) - Indicadores de pesquisa e inovação
2. CAPES (2023) - Consórcio ORCID Brasil
3. CHINI, J. A.; SILVA NETO, F. A. (2022) - Padronização Lattes
4. CONCEIÇÃO, D. A. et al. (2019) - Redes de colaboração científica
5. GOMES, L. M. (2024) - Métricas de avaliação de periódicos
6. GROEHS, D. S. et al. (2023) - LattesData infraestrutura
7. MELO, F. P. L. et al. (2021) - Cobertura bases internacionais
8. NEUBERT, L. A. et al. (2024) - OpenAlex para repositórios brasileiros
9. OLIVEIRA, M. L.; STECANELA, N. M. (2023) - Avaliação pós-graduação CAPES
10. SARVO, D. O. et al. (2022) - Inteligência acadêmica Lattes
11. SEGURADO, M.; FERREIRA, A. S. (2019) - Governança de dados
12. SILVA, D. R. F. et al. (2020) - Avaliação produção científica
13. SILVA, R. F. et al. (2018) - Enriquecimento semântico Lattes

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

### Infraestrutura
- [ ] Elasticsearch 8.10+ instalado
- [ ] PHP 8.2+ configurado
- [ ] Kibana instalado
- [ ] Servidor web (Apache/Nginx)
- [ ] Certificado SSL/TLS

### Dados
- [ ] PPGs cadastrados (4 programas)
- [ ] Currículos Lattes importados
- [ ] OpenAlex integrado
- [ ] Qualis indexado
- [ ] Projetos de pesquisa cadastrados

### Funcionalidades
- [ ] Busca de produções
- [ ] Busca de pesquisadores
- [ ] Perfis detalhados
- [ ] Filtros por PPG
- [ ] Filtros por área
- [ ] Exportação BibTeX
- [ ] Exportação ORCID
- [ ] Dashboard Kibana

### Conformidade
- [ ] LGPD - ROT
- [ ] LGPD - RIPD/DPIA
- [ ] Política de Privacidade
- [ ] Logs de auditoria
- [ ] Termos de Uso

### Documentação
- [ ] Manual de instalação
- [ ] Manual do usuário
- [ ] Manual do administrador
- [ ] Guias de importação
- [ ] Relatório de impacto

### Validação
- [ ] Testes automatizados
- [ ] Testes com usuários
- [ ] Aprovação coordenadores
- [ ] Artigo científico
- [ ] Apresentação final

---

## 🎓 CONCLUSÃO

Este plano de implementação integra:

✅ **Sistema Prodmais UNIFESP completo** (todas as funcionalidades)  
✅ **Documentação acadêmica PIVIC** (objetivos, metodologia, resultados)  
✅ **Conformidade LGPD** (segurança e privacidade)  
✅ **Requisitos da CAPES** (avaliação quadrienal)  
✅ **Interoperabilidade** (Lattes, ORCID, OpenAlex, BrCris)  

**Próxima etapa:** Apresentar ao coordenador e iniciar FASE 1! 🚀

---

**Elaborado por:** GitHub Copilot  
**Data:** 28 de Outubro de 2025  
**Versão:** 1.0  
