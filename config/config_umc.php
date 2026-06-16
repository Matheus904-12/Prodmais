<?php
/**
 * PRODMAIS UMC - Configuração Principal
 * Baseado em Prodmais UNIFESP + Design UMC
 * Seguindo documentação PIVIC 2025
 */

// ============== AMBIENTE ============== //

$app_env   = getenv('APP_ENV')   ?: 'development';
$app_debug = filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN);

ini_set('display_errors',         $app_debug ? 1 : 0);
ini_set('display_startup_errors', $app_debug ? 1 : 0);
error_reporting($app_debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);

// ============== ELASTICSEARCH ============== //

$_es_raw = getenv('ES_HOST') ?: (getenv('ELASTICSEARCH_HOST') ?: 'elasticsearch:9200');
// Garante que o prefixo http:// está presente
$hosts = [preg_match('#^https?://#', $_es_raw) ? $_es_raw : 'http://' . $_es_raw];

/* Índices do Elasticsearch */
$index          = getenv('ES_INDEX')          ?: 'prodmais_umc';
$index_cv       = getenv('ES_INDEX_CV')       ?: 'prodmais_umc_cv';
$index_ppg      = getenv('ES_INDEX_PPG')      ?: 'prodmais_umc_ppg';
$index_projetos = getenv('ES_INDEX_PROJETOS') ?: 'prodmais_umc_projetos';

// ============== MYSQL ============== //

$mysql_host = getenv('MYSQL_HOST')     ?: (getenv('DB_HOST') ?: 'db');
$mysql_db   = getenv('MYSQL_DB')       ?: (getenv('DB_NAME') ?: 'prodmais_umc');
$mysql_user = getenv('MYSQL_USER')     ?: (getenv('DB_USER') ?: 'prodmais');
$mysql_pass = getenv('MYSQL_PASS')     ?: (getenv('DB_PASS') ?: 'prodmais123');
$mysql_port = (int)(getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: 3306));

// ============== URLS E CAMINHOS ============== //

$url_base = rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/');

// ============== AUTENTICAÇÃO ============== //

/* Credenciais admin — ler de env vars; fallback apenas para dev local */
$login_user     = getenv('ADMIN_USER')     ?: 'admin';
$login_password = getenv('ADMIN_PASSWORD') ?: 'Admin@2025';

// ============== INSTITUIÇÃO UMC ============== //

/* Dados institucionais */
$instituicao = "Universidade de Mogi das Cruzes";
$instituicao_sigla = "UMC";
$branch = "Prodmais UMC";
$branch_description = "Sistema de Gestão de Produção Científica dos Programas de Pós-Graduação em Biotecnologia, Engenharia Biomédica, Políticas Públicas e Ciência e Tecnologia em Saúde";

/* Slogan */
$slogan = 'Consolidação, Análise e Interoperabilidade de Dados Científicos';

/* Imagem para redes sociais */
$facebook_image = $url_base . "/img/logo-umc.png";

// ============== PROGRAMAS DE PÓS-GRADUAÇÃO UMC ============== //

/* PPGs da UMC (conforme documentação PIVIC) */
$ppgs_umc = [
    [
        'nome' => 'Biotecnologia',
        'sigla' => 'Biotec',
        'codigo_capes' => '33002010191P0',
        'nivel' => 'Mestrado/Doutorado',
        'campus' => 'Mogi das Cruzes',
        'areas_concentracao' => [
            'Biotecnologia Industrial',
            'Biotecnologia Ambiental'
        ]
    ],
    [
        'nome' => 'Engenharia Biomédica',
        'sigla' => 'Eng. Biomédica',
        'codigo_capes' => '33002010192P0',
        'nivel' => 'Mestrado/Doutorado',
        'campus' => 'Mogi das Cruzes',
        'areas_concentracao' => [
            'Biomateriais',
            'Processamento de Sinais Biomédicos'
        ]
    ],
    [
        'nome' => 'Políticas Públicas',
        'sigla' => 'Pol. Públicas',
        'codigo_capes' => '33002010193P0',
        'nivel' => 'Mestrado/Doutorado',
        'campus' => 'Mogi das Cruzes',
        'areas_concentracao' => [
            'Análise de Políticas Públicas',
            'Gestão Pública'
        ]
    ],
    [
        'nome' => 'Ciência e Tecnologia em Saúde',
        'sigla' => 'C&T Saúde',
        'codigo_capes' => '33002010194P0',
        'nivel' => 'Mestrado/Doutorado',
        'campus' => 'Mogi das Cruzes',
        'areas_concentracao' => [
            'Inovação Tecnológica em Saúde',
            'Vigilância em Saúde'
        ]
    ]
];

// ============== CUSTOMIZAÇÃO DA INTERFACE ============== //

/* Funcionalidades visíveis */
$mostrar_instituicao = true;
$mostrar_area_concentracao = true;
$mostrar_existe_doi = true;
$mostrar_openalex = true;
$mostrar_link_dashboard = true;
$mostrar_orcid = true;
$mostrar_qualis = true;
$mostrar_projetos = true;

/* Tema visual (manter design UMC) */
$theme = 'UMC'; // Personalizado UMC

// ============== INTEGRAÇÕES ============== //

/* ORCID (conforme documentação - Consórcio CAPES-ORCID) */
$orcid_enabled = true;
$orcid_client_id = "";  // Configurar após registro no ORCID
$orcid_client_secret = "";

/* OpenAlex (conforme documentação) */
$openalex_enabled = true;
$openalex_email = "prodmais@umc.br";  // Melhor performance na API

/* BrCris (conforme documentação) */
$brcris_enabled = true;

/* Qualis CAPES (conforme documentação) */
$qualis_enabled = true;

// ============== LGPD (conforme documentação) ============== //

/* Conformidade LGPD - Art. 7º, §4º (dados públicos do Lattes) */
$lgpd_enabled = true;
$lgpd_log_access = true;
$lgpd_anonymize_sensitive = true;

/* Política de Privacidade */
$privacy_policy_url = "/config/privacy_policy.md";
$terms_of_use_url = "/config/terms_of_use.md";

/* Logs de auditoria (conforme Art. 46-52 LGPD) */
$log_database = __DIR__ . '/../data/logs.sqlite';

// ============== DASHBOARDS KIBANA ============== //

/* URLs dos dashboards (configurar após instalação do Kibana) */
$dashboard_lattes_producoes = "http://localhost:5601/app/dashboards#/view/producoes_umc";
$dashboard_lattes_cv = "http://localhost:5601/app/dashboards#/view/pesquisadores_umc";
$dashboard_source = "http://localhost:5601/app/dashboards#/view/geral_umc";

// ============== EXPORTAÇÃO ============== //

/* Formatos de exportação habilitados */
$export_formats = [
    'bibtex' => true,   // Para LaTeX
    'ris' => true,      // Para Mendeley/Zotero
    'endnote' => true,  // Para EndNote
    'csv' => true,      // Para Excel
    'json' => true,     // Para APIs
    'xml' => true,      // Para sistemas
    'orcid' => true,    // Exportação direta ORCID
    'brcris' => true    // Formato BrCris
];

// ============== AVALIAÇÃO CAPES ============== //

/* Períodos de avaliação */
$quadrienio_atual = [2021, 2024];
$quadrienio_anterior = [2017, 2020];

/* Indicadores CAPES */
$capes_indicators_enabled = true;

// ============== DESENVOLVIMENTO ============== //

/* Modo debug */
$debug_mode = $app_debug;

/* Cache */
$cache_enabled = true;
$cache_lifetime = 3600; // 1 hora

// ============== MENSAGENS DO SISTEMA ============== //

/* Mensagens personalizadas */
$welcome_message = "Bem-vindo ao Prodmais UMC - Sistema de Gestão de Produção Científica";
$maintenance_message = "Sistema em manutenção. Voltaremos em breve.";

// ============== FIM DA CONFIGURAÇÃO ============== //
