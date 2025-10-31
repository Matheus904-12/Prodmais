<?php
/**
 * PRODMAIS UMC - Funções Principais
 * Integração UNIFESP + UMC
 * Conformidade LGPD e CAPES
 */

/* Load libraries for PHP composer */
require(__DIR__ . '/../vendor/autoload.php');

/* Load configuration */
require(__DIR__ . '/../config/config_umc.php');

/* Load UMC classes */
require_once(__DIR__ . '/ElasticsearchService.php');
require_once(__DIR__ . '/LattesParser.php');
require_once(__DIR__ . '/OrcidFetcher.php');
require_once(__DIR__ . '/OpenAlexFetcher.php');
require_once(__DIR__ . '/ExportService.php');
require_once(__DIR__ . '/LogService.php');
require_once(__DIR__ . '/Anonymizer.php');

// Carregar camada relacional
require_once(__DIR__ . '/DatabaseService.php');

/* Load Elasticsearch Client */
use Elastic\Elasticsearch\ClientBuilder;

/**
 * Criar cliente Elasticsearch
 */
function getElasticsearchClient() {
    global $hosts, $elasticsearch_user, $elasticsearch_password;
    
    try {
        if (isset($elasticsearch_user) && !empty($elasticsearch_user)) {
            $client = ClientBuilder::create()
                ->setHosts($hosts)
                ->setBasicAuthentication($elasticsearch_user, $elasticsearch_password)
                ->setCABundle(__DIR__ . '/http_ca.crt')
                ->build();
        } else {
            $client = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        }
        return $client;
    } catch (Exception $e) {
        error_log("Erro ao conectar no Elasticsearch: " . $e->getMessage());
        return null;
    }
}

/**
 * Verificar e criar índices do Elasticsearch
 */
function initializeElasticsearchIndexes() {
    global $index, $index_cv, $index_ppg, $index_projetos;
    
    $client = getElasticsearchClient();
    if (!$client) {
        return false;
    }
    
    $indexes = [
        $index => 'produções científicas',
        $index_cv => 'currículos Lattes',
        $index_ppg => 'programas de pós-graduação',
        $index_projetos => 'projetos de pesquisa',
        'qualis' => 'classificação Qualis CAPES',
        'openalexcitedworks' => 'citações OpenAlex'
    ];
    
    foreach ($indexes as $idx => $description) {
        try {
            $indexParams['index'] = $idx;
            $exists = $client->indices()->exists($indexParams);
            
            if (!$exists) {
                createIndex($idx, $client, $description);
                applyMappings($idx, $client);
            }
        } catch (Exception $e) {
            error_log("Erro ao verificar índice $idx: " . $e->getMessage());
        }
    }
    
    return true;
}

/**
 * Criar índice no Elasticsearch
 */
function createIndex($indexName, $client, $description = '') {
    try {
        $params = [
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'analysis' => [
                        'analyzer' => [
                            'brazilian' => [
                                'type' => 'standard',
                                'stopwords' => '_brazilian_'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $client->indices()->create($params);
        error_log("Índice $indexName criado com sucesso" . ($description ? " ($description)" : ""));
        return true;
    } catch (Exception $e) {
        error_log("Erro ao criar índice $indexName: " . $e->getMessage());
        return false;
    }
}

/**
 * Aplicar mappings ao índice
 */
function applyMappings($indexName, $client) {
    // Mappings específicos por tipo de índice
    $mappings = [];
    
    if (strpos($indexName, '_cv') !== false) {
        // Mappings para currículos
        $mappings = [
            'properties' => [
                'nome_completo' => ['type' => 'text', 'analyzer' => 'brazilian'],
                'lattesID' => ['type' => 'keyword'],
                'orcidID' => ['type' => 'keyword'],
                'email' => ['type' => 'keyword'],
                'instituicao' => ['type' => 'text'],
                'ppg' => ['type' => 'keyword'],
                'area_concentracao' => ['type' => 'keyword'],
                'campus' => ['type' => 'keyword'],
                'producoes' => ['type' => 'nested'],
                'projetos' => ['type' => 'nested']
            ]
        ];
    } elseif (strpos($indexName, '_ppg') !== false) {
        // Mappings para PPGs
        $mappings = [
            'properties' => [
                'nome' => ['type' => 'text'],
                'codigo_capes' => ['type' => 'keyword'],
                'nivel' => ['type' => 'keyword'],
                'campus' => ['type' => 'keyword'],
                'areas_concentracao' => ['type' => 'keyword'],
                'docentes' => ['type' => 'nested'],
                'producoes_totais' => ['type' => 'integer']
            ]
        ];
    } elseif (strpos($indexName, '_projetos') !== false) {
        // Mappings para projetos
        $mappings = [
            'properties' => [
                'titulo' => ['type' => 'text', 'analyzer' => 'brazilian'],
                'ano_inicio' => ['type' => 'integer'],
                'ano_fim' => ['type' => 'integer'],
                'situacao' => ['type' => 'keyword'],
                'financiamento' => ['type' => 'text'],
                'equipe' => ['type' => 'nested'],
                'ppg' => ['type' => 'keyword']
            ]
        ];
    } else {
        // Mappings para produções científicas
        $mappings = [
            'properties' => [
                'titulo' => ['type' => 'text', 'analyzer' => 'brazilian'],
                'autores' => ['type' => 'text', 'analyzer' => 'brazilian'],
                'ano' => ['type' => 'integer'],
                'tipo' => ['type' => 'keyword'],
                'doi' => ['type' => 'keyword'],
                'issn' => ['type' => 'keyword'],
                'qualis' => ['type' => 'keyword'],
                'ppg' => ['type' => 'keyword'],
                'area_concentracao' => ['type' => 'keyword'],
                'idioma' => ['type' => 'keyword'],
                'citacoes' => ['type' => 'integer'],
                'openalex_id' => ['type' => 'keyword']
            ]
        ];
    }
    
    if (!empty($mappings)) {
        try {
            $params = [
                'index' => $indexName,
                'body' => $mappings
            ];
            $client->indices()->putMapping($params);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao aplicar mappings no índice $indexName: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Classe para busca multi-índice (UNIFESP style)
 */
class MultiIndexSearch {
    private $client;
    
    public function __construct() {
        $this->client = getElasticsearchClient();
    }
    
    /**
     * Busca em múltiplos índices
     */
    public function search($query, $indexes = null) {
        global $index, $index_cv, $index_projetos;
        
        if ($indexes === null) {
            $indexes = [$index, $index_cv, $index_projetos];
        }
        
        $results = [];
        
        foreach ($indexes as $idx) {
            try {
                $params = [
                    'index' => $idx,
                    'body' => [
                        'query' => [
                            'query_string' => [
                                'query' => $query,
                                'default_operator' => 'OR'
                            ]
                        ],
                        'size' => 50
                    ]
                ];
                
                $response = $this->client->search($params);
                $results[$idx] = [
                    'total' => $response['hits']['total']['value'] ?? 0,
                    'hits' => $response['hits']['hits'] ?? []
                ];
            } catch (Exception $e) {
                $results[$idx] = ['total' => 0, 'hits' => []];
            }
        }
        
        return $results;
    }
    
    /**
     * Contar resultados por índice
     */
    public function count($query, $index) {
        try {
            $params = [
                'index' => $index,
                'body' => [
                    'query' => [
                        'query_string' => [
                            'query' => $query
                        ]
                    ]
                ]
            ];
            
            $response = $this->client->count($params);
            return $response['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

/**
 * Classe para processamento de requisições (UNIFESP style)
 */
class RequestProcessor {
    
    /**
     * Processar POST de busca
     */
    public static function parseSearchPost($post) {
        $search = $post['search'] ?? '';
        $page = isset($post['page']) ? (int)$post['page'] : 1;
        $limit = isset($post['limit']) ? (int)$post['limit'] : 50;
        
        // Filtros
        $filters = [];
        
        if (isset($post['ppg']) && !empty($post['ppg'])) {
            $filters['ppg'] = $post['ppg'];
        }
        
        if (isset($post['area_concentracao']) && !empty($post['area_concentracao'])) {
            $filters['area_concentracao'] = $post['area_concentracao'];
        }
        
        if (isset($post['ano_inicio']) && !empty($post['ano_inicio'])) {
            $filters['ano_inicio'] = (int)$post['ano_inicio'];
        }
        
        if (isset($post['ano_fim']) && !empty($post['ano_fim'])) {
            $filters['ano_fim'] = (int)$post['ano_fim'];
        }
        
        if (isset($post['tipo']) && !empty($post['tipo'])) {
            $filters['tipo'] = $post['tipo'];
        }
        
        if (isset($post['qualis']) && !empty($post['qualis'])) {
            $filters['qualis'] = $post['qualis'];
        }
        
        // Construir query Elasticsearch
        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'query_string' => [
                                'query' => $search,
                                'default_operator' => 'OR'
                            ]
                        ]
                    ]
                ]
            ],
            'from' => ($page - 1) * $limit,
            'size' => $limit,
            'sort' => [
                ['ano' => ['order' => 'desc']],
                ['_score' => ['order' => 'desc']]
            ]
        ];
        
        // Aplicar filtros
        foreach ($filters as $field => $value) {
            if ($field === 'ano_inicio' || $field === 'ano_fim') {
                $query['query']['bool']['filter'][] = [
                    'range' => [
                        'ano' => [
                            'gte' => $filters['ano_inicio'] ?? 1900,
                            'lte' => $filters['ano_fim'] ?? date('Y')
                        ]
                    ]
                ];
            } else {
                $query['query']['bool']['filter'][] = [
                    'term' => [$field => $value]
                ];
            }
        }
        
        return [
            'query' => $query,
            'page' => $page,
            'limit' => $limit,
            'filters' => $filters
        ];
    }
}

/**
 * Função auxiliar para obter PPG por código CAPES
 */
function getPPGByCodigo($codigo_capes) {
    global $ppgs_umc;
    foreach ($ppgs_umc as $ppg) {
        if ($ppg['codigo_capes'] === $codigo_capes) {
            return $ppg;
        }
    }
    return null;
}

/**
 * Função auxiliar para listar todos os PPGs
 */
function getAllPPGs() {
    global $ppgs_umc;
    return $ppgs_umc;
}

/**
 * Função auxiliar para verificar se está em modo de desenvolvimento
 */
function isDebugMode() {
    global $debug_mode;
    return $debug_mode ?? false;
}


/**
 * Inicializar Elasticsearch e banco relacional na primeira execução
 */
if (php_sapi_name() !== 'cli') {
    initializeElasticsearchIndexes();
    // Inicializa banco relacional (SQLite por padrão)
    try {
        $dbService = new DatabaseService($config ?? []);
    } catch (Exception $e) {
        error_log('Erro ao inicializar banco relacional: ' . $e->getMessage());
    }
}

/**
 * Log de acesso (LGPD)
 */
if (($lgpd_log_access ?? false) && class_exists('LogService')) {
    try {
        $logService = new LogService();
        $logService->logAction($_SERVER['REMOTE_ADDR'] ?? 'unknown', 'access:' . ($_SERVER['REQUEST_URI'] ?? '/'));
    } catch (Exception $e) {
        // Silenciar erro se LogService não estiver disponível
    }
}
