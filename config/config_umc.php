<?php
/**
 * PRODMAIS UMC - Configuração Principal
 * Baseado em Prodmais UNIFESP + Design UMC
 * Seguindo documentação PIVIC 2025
 */

/* Exibir erros apenas em desenvolvimento */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============== ELASTICSEARCH ============== //

/* Configuração do Elasticsearch */
$hosts = ['localhost:9200'];
// $elasticsearch_user = "elastic";
// $elasticsearch_password = "";

/* Índices do Elasticsearch */
$index = "prodmais_umc";              // Produções científicas
$index_cv = "prodmais_umc_cv";        // Currículos Lattes
$index_ppg = "prodmais_umc_ppg";      // Programas de Pós-Graduação
$index_projetos = "prodmais_umc_projetos";  // Projetos de Pesquisa

// ============== URLS E CAMINHOS ============== //

/* Endereço base (ajustar conforme servidor) */
$url_base = "http://localhost:8000";

// ============== AUTENTICAÇÃO ============== //

/* Login administrativo */
$login_user = "admin";
$login_password = "admin123";

// ============== INSTITUIÇÃO UMC ============== //

/* Dados institucionais */
$instituicao = "Universidade de Mogi das Cruzes";
$instituicao_sigla = "UMC";
$branch = "Prodmais UMC";
$branch_description = "Sistema de Gestão de Produção Científica dos Programas de Pós-Graduação em Biotecnologia, Engenharia Biomédica, Políticas Públicas e Ciência e Tecnologia em Saúde";

/* Slogan */
$slogan = 'Consolidação, Análise e Interoperabilidade de Dados Científicos';

/* Imagem para redes sociais */
$facebook_image = "http://localhost:8000/img/logo-umc.png";

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
$debug_mode = true;

/* Cache */
$cache_enabled = true;
$cache_lifetime = 3600; // 1 hora

// ============== MENSAGENS DO SISTEMA ============== //

/* Mensagens personalizadas */
$welcome_message = "Bem-vindo ao Prodmais UMC - Sistema de Gestão de Produção Científica";
$maintenance_message = "Sistema em manutenção. Voltaremos em breve.";

// ============== FIM DA CONFIGURAÇÃO ============== //
