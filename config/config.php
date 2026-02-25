<?php

/**
 * Configuração do Sistema Prodmais
 * 
 * Este arquivo contém todas as configurações principais do sistema.
 * Copie este arquivo para config.php e ajuste conforme necessário.
 */

return [
    // ================================
    // CONFIGURAÇÕES ELASTICSEARCH
    // ================================
    'elasticsearch' => [
        'hosts' => [
    // ================================
    // CONFIGURAÇÕES DO BANCO DE DADOS
    // ================================
    'database' => [
        'host' => 'localhost',
        'name' => 'prodmais_umc',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],


            'http://localhost:9200' // Endereço do servidor Elasticsearch
        ],
        'username' => null, // Usuário (se autenticação habilitada)
        'password' => null, // Senha (se autenticação habilitada)
        'timeout' => 30,    // Timeout em segundos
        'retries' => 3      // Número de tentativas em caso de falha
    ],

    // ================================
    // CAMINHOS DE DADOS
    // ================================
    'data_paths' => [
        'lattes_xml' => __DIR__ . '/../data/lattes_xml',
        'uploads' => __DIR__ . '/../data/uploads',
        'logs' => __DIR__ . '/../data/logs.sqlite',
        'db' => __DIR__ . '/../data/prodmais.sqlite'
    ],

    // ================================
    // CONFIGURAÇÕES DA APLICAÇÃO
    // ================================
    'app' => [
        'index_name' => 'prodmais_cientifica', // Nome do índice no Elasticsearch
        'timezone' => 'America/Sao_Paulo',    // Fuso horário
        'debug' => true,                     // Modo debug (apenas desenvolvimento)
        'version' => '2.0.0'                 // Versão do sistema
    ],

    // ================================
    // INTEGRAÇÕES COM APIs EXTERNAS
    // ================================
    'integrations' => [
        'openalex' => [
            'enabled' => true,
            'email' => 'contato@umc.br', // Email para identificação (cortesia)
            'rate_limit' => 10 // Requisições por segundo
        ],
        'orcid' => [
            'enabled' => true,
            'api_url' => 'https://pub.orcid.org/v3.0',
            'timeout' => 30
        ]
    ],

    // ================================
    // CONFIGURAÇÕES DE PRIVACIDADE/LGPD
    // ================================
    'privacy' => [
        'anonymization_salt' => 'prodmais_umc_salt_2025',
        'default_level' => 'standard', // minimal, standard, full, statistical
        'log_retention_days' => 365,   // Dias para manter logs
        'enable_data_export' => true,  // Permitir exportação de dados pessoais
        'enable_data_deletion' => true // Permitir exclusão de dados pessoais
    ],

    // ================================
    // CONFIGURAÇÕES DE EXPORTAÇÃO
    // ================================
    'export' => [
        'max_records' => 1000,    // Máximo de registros por exportação
        'allowed_formats' => [    // Formatos permitidos
            'csv', 'bibtex', 'ris', 'json', 'xml'
        ],
        'include_personal_data' => false // Incluir dados pessoais nas exportações
    ],

    // ================================
    // CONFIGURAÇÕES DE INTERFACE
    // ================================
    'ui' => [
        'results_per_page' => 50,     // Resultados padrão por página
        'max_results_per_page' => 100, // Máximo de resultados por página
        'enable_charts' => true,      // Habilitar gráficos estatísticos
        'enable_researcher_search' => true, // Habilitar busca de pesquisadores
        'institution_name' => 'Universidade de Mogi das Cruzes', // Nome da instituição
        'contact_email' => 'prodmais@umc.br'
    ],

    // ================================
    // CONFIGURAÇÕES DE CACHE
    // ================================
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // Time to live em segundos (1 hora)
        'driver' => 'file', // file, redis, memcache
        'path' => __DIR__ . '/../data/cache'
    ],

    // ================================
    // CONFIGURAÇÕES DE LOG
    // ================================
    'logging' => [
        'level' => 'info', // debug, info, warning, error
        'max_files' => 10, // Máximo de arquivos de log
        'max_size' => '10MB', // Tamanho máximo por arquivo
        'channels' => [
            'application' => __DIR__ . '/../data/logs/app.log',
            'elasticsearch' => __DIR__ . '/../data/logs/elasticsearch.log',
            'integrations' => __DIR__ . '/../data/logs/integrations.log'
        ]
    ],

    // ================================
    // CONFIGURAÇÕES DE SEGURANÇA
    // ================================
    'security' => [
        'session_timeout' => 3600, // Timeout da sessão em segundos
        'max_login_attempts' => 5, // Máximo de tentativas de login
        'lockout_duration' => 900, // Duração do bloqueio em segundos
        'csrf_protection' => true, // Proteção CSRF
        'secure_cookies' => false, // Cookies seguros (apenas HTTPS)
        'admin_users' => [
            // Lista de usuários administradores
            // 'usuario' => 'senha_hash'
        ]
    ],

    // ================================
    // CONFIGURAÇÕES DE BACKUP
    // ================================
    'backup' => [
        'enabled' => false,
        'schedule' => '0 2 * * *', // Cron expression (2h da manhã)
        'retention_days' => 30,    // Dias para manter backups
        'path' => __DIR__ . '/../data/backups',
        'compress' => true
    ]
];