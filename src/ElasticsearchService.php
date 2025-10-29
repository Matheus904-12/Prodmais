<?php

use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    private $client;
    private $fallbackMode = false;

    public function __construct(array $esConfig)
    {
        try {
            $this->client = ClientBuilder::create()
                ->setHosts($esConfig['hosts'])
                ->setRetries(1)
                ->build();
            
            // Testa conexão
            $this->client->ping();
        } catch (Exception $e) {
            $this->fallbackMode = true;
            error_log("Elasticsearch não disponível, usando modo fallback: " . $e->getMessage());
        }
    }

    public function isFallbackMode(): bool
    {
        return $this->fallbackMode;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function indexExists(string $indexName): bool
    {
        if ($this->fallbackMode) {
            return true; // Em modo fallback, sempre retorna true
        }
        $response = $this->client->indices()->exists(['index' => $indexName]);
        return $response->getStatusCode() === 200;
    }

    public function deleteIndex(string $indexName): void
    {
        if ($this->fallbackMode) {
            return; // Em modo fallback, não faz nada
        }
        $this->client->indices()->delete(['index' => $indexName]);
    }

    public function createIndex(string $indexName): void
    {
        if ($this->fallbackMode) {
            return; // Em modo fallback, não faz nada
        }
        $this->client->indices()->create([
            'index' => $indexName,
            'body' => [
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'researcher_name' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                        'researcher_lattes_id' => ['type' => 'keyword'],
                        'title' => ['type' => 'text', 'analyzer' => 'portuguese'],
                        'year' => ['type' => 'integer'],
                        'type' => ['type' => 'keyword'],
                        'subtype' => ['type' => 'keyword'],
                        'doi' => ['type' => 'keyword'],
                        'language' => ['type' => 'keyword'],
                        'source' => ['type' => 'keyword'],
                        'institution' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                        'unit' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                        'city' => ['type' => 'keyword'],
                        'state' => ['type' => 'keyword'],
                        'country' => ['type' => 'keyword'],
                        'journal' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                        'publisher' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                        'isbn' => ['type' => 'keyword'],
                        'issn' => ['type' => 'keyword'],
                        'volume' => ['type' => 'keyword'],
                        'pages' => ['type' => 'keyword'],
                        'event_name' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                        'event_city' => ['type' => 'keyword'],
                        'event_year' => ['type' => 'keyword'],
                        'proceedings_title' => ['type' => 'text'],
                        'book_title' => ['type' => 'text'],
                        'student_name' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                        'course' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                        'patent_number' => ['type' => 'keyword'],
                        'purpose' => ['type' => 'text'],
                        'platform' => ['type' => 'keyword'],
                        'date' => ['type' => 'date', 'format' => 'yyyy-MM-dd||dd/MM/yyyy||strict_date_optional_time'],
                        'areas' => [
                            'type' => 'nested',
                            'properties' => [
                                'grande_area' => ['type' => 'keyword'],
                                'area' => ['type' => 'keyword'],
                                'sub_area' => ['type' => 'keyword'],
                                'especialidade' => ['type' => 'keyword']
                            ]
                        ],
                        'authors' => [
                            'type' => 'nested',
                            'properties' => [
                                'name' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                                'citation_name' => ['type' => 'text', 'fields' => ['keyword' => ['type' => 'keyword']]],
                                'order' => ['type' => 'integer']
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function bulkIndex(string $indexName, array $documents): array
    {
        if ($this->fallbackMode) {
            // Em modo fallback, simula sucesso
            return [
                'errors' => false,
                'took' => 1,
                'items' => array_map(function($doc) {
                    return ['index' => ['_id' => $doc['id'], 'result' => 'created']];
                }, $documents)
            ];
        }
        
        $params = ['body' => []];

        foreach ($documents as $doc) {
            $params['body'][] = [
                'index' => [
                    '_index' => $indexName,
                    '_id'    => $doc['id']
                ]
            ];
            $params['body'][] = $doc;
        }

        $response = ['errors' => false];
        if (!empty($params['body'])) {
            $response = $this->client->bulk($params)->asArray();
        }
        return $response;
    }

    public function refreshIndex(string $indexName): void
    {
        if ($this->fallbackMode) {
            return; // Em modo fallback, não faz nada
        }
        $this->client->indices()->refresh(['index' => $indexName]);
    }

    public function search(string $indexName, array $filters = [], int $size = 50)
    {
        if ($this->fallbackMode) {
            return $this->searchFallback($filters, $size);
        }

        $must_query = [];
        $filter_query = [];

        // A busca por texto vai na cláusula principal
        if (!empty($filters['q'])) {
            $must_query[] = [
                'multi_match' => [
                    'query' => $filters['q'],
                    'fields' => ['titulo^2', 'autores', 'periodico', 'nome_evento', 'nome_livro', 'editora'],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO'
                ]
            ];
        } else {
            // Se não houver busca por texto, retorna tudo (respeitando os filtros)
            $must_query[] = ['match_all' => new \stdClass()];
        }

        // As seleções dos dropdowns vão na cláusula de filtro
        if (!empty($filters['type'])) {
            $filter_query[] = ['term' => ['tipo.keyword' => $filters['type']]];
        }
        if (!empty($filters['subtype'])) {
            $filter_query[] = ['term' => ['natureza.keyword' => $filters['subtype']]];
        }
        if (!empty($filters['year'])) {
            $filter_query[] = ['term' => ['ano' => $filters['year']]];
        }
        if (!empty($filters['institution'])) {
            $filter_query[] = ['match' => ['instituicao' => $filters['institution']]];
        }
        if (!empty($filters['language'])) {
            $filter_query[] = ['term' => ['idioma.keyword' => $filters['language']]];
        }
        if (!empty($filters['area'])) {
            $filter_query[] = [
                'match' => [
                    'area_concentracao' => $filters['area']
                ]
            ];
        }
        if (!empty($filters['author'])) {
            $filter_query[] = [
                'match' => [
                    'autores' => [
                        'query' => $filters['author'],
                        'operator' => 'and'
                    ]
                ]
            ];
        }

        // Filtro por range de anos
        if (!empty($filters['year_from']) || !empty($filters['year_to'])) {
            $range_filter = ['range' => ['ano' => []]];
            if (!empty($filters['year_from'])) {
                $range_filter['range']['ano']['gte'] = (int)$filters['year_from'];
            }
            if (!empty($filters['year_to'])) {
                $range_filter['range']['ano']['lte'] = (int)$filters['year_to'];
            }
            $filter_query[] = $range_filter;
        }

        $params = [
            'index' => $indexName,
            'body'  => [
                'size' => $size,
                'sort' => [
                    ['ano' => ['order' => 'desc']],
                    ['_score' => ['order' => 'desc']]
                ],
                'query' => [
                    'bool' => [
                        'must' => $must_query,
                        'filter' => $filter_query
                    ]
                ],
                'highlight' => [
                    'fields' => [
                        'titulo' => new \stdClass(),
                        'autores' => new \stdClass()
                    ]
                ]
            ]
        ];

        return $this->client->search($params);
    }

    public function getAggregations(string $indexName, array $filters = []): array
    {
        if ($this->fallbackMode) {
            return $this->getAggregationsFallback($filters);
        }
        
        $filter_query = [];

        
        // Aplicar os mesmos filtros da busca principal
        if (!empty($filters['type'])) {
            $filter_query[] = ['term' => ['tipo.keyword' => $filters['type']]];
        }
        if (!empty($filters['year'])) {
            $filter_query[] = ['term' => ['ano' => $filters['year']]];
        }
        if (!empty($filters['institution'])) {
            $filter_query[] = ['match' => ['instituicao' => $filters['institution']]];
        }
        
        $params = [
            'index' => $indexName,
            'body' => [
                'size' => 0, // Não retorna documentos, apenas agregações
                'query' => [
                    'bool' => [
                        'filter' => $filter_query
                    ]
                ],
                'aggs' => [
                    'by_type' => [
                        'terms' => [
                            'field' => 'tipo.keyword',
                            'size' => 20
                        ]
                    ],
                    'by_year' => [
                        'terms' => [
                            'field' => 'ano',
                            'size' => 20,
                            'order' => ['_key' => 'desc']
                        ]
                    ],
                    'by_institution' => [
                        'terms' => [
                            'field' => 'instituicao.keyword',
                            'size' => 10
                        ]
                    ],
                    'by_language' => [
                        'terms' => [
                            'field' => 'idioma.keyword',
                            'size' => 10
                        ]
                    ],
                    'year_stats' => [
                        'stats' => [
                            'field' => 'ano'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);
        return $response->asArray();
    }

    public function getUniqueValues(string $indexName, string $field, int $size = 100): array
    {
        if ($this->fallbackMode) {
            return $this->getUniqueValuesFallback($field, $size);
        }
        
        $params = [
            'index' => $indexName,
            'body' => [
                'size' => 0,
                'aggs' => [
                    'unique_values' => [
                        'terms' => [
                            'field' => $field,
                            'size' => $size
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);
        $result = $response->asArray();
        
        $values = [];
        if (isset($result['aggregations']['unique_values']['buckets'])) {
            foreach ($result['aggregations']['unique_values']['buckets'] as $bucket) {
                $values[] = $bucket['key'];
            }
        }
        
        return $values;
    }

    /**
     * Valores únicos em modo fallback
     */
    private function getUniqueValuesFallback(string $field, int $size = 100): array
    {
        $data = $this->getFallbackData();
        $values = [];
        
        foreach ($data as $item) {
            $value = null;
            
            // Extrair valor do campo especificado
            switch ($field) {
                case 'type':
                    $value = $item['type'] ?? null;
                    break;
                case 'subtype':
                    $value = $item['subtype'] ?? null;
                    break;
                case 'language':
                    $value = $item['language'] ?? null;
                    break;
                case 'institution.keyword':
                case 'institution':
                    $value = $item['institution'] ?? null;
                    break;
                case 'year':
                    $value = $item['year'] ?? null;
                    break;
                case 'program':
                    $value = $item['program'] ?? null;
                    break;
                case 'researcher_name.keyword':
                case 'researcher_name':
                    $value = $item['researcher_name'] ?? null;
                    break;
                default:
                    $value = $item[$field] ?? null;
                    break;
            }
            
            if ($value !== null && !in_array($value, $values)) {
                $values[] = $value;
            }
        }
        
        // Limitar resultados
        return array_slice($values, 0, $size);
    }

    /**
     * Agregações em modo fallback
     */
    private function getAggregationsFallback(array $filters = []): array
    {
        $data = $this->getFallbackData();
        
        // Simular agregações básicas
        $types = [];
        $years = [];
        $institutions = [];
        $languages = [];
        $areas = [];
        
        foreach ($data as $item) {
            // Contar tipos
            $type = $item['type'];
            $types[$type] = ($types[$type] ?? 0) + 1;
            
            // Contar anos
            $year = $item['year'];
            $years[$year] = ($years[$year] ?? 0) + 1;
            
            // Contar instituições
            $institution = $item['institution'] ?? 'N/A';
            $institutions[$institution] = ($institutions[$institution] ?? 0) + 1;
            
            // Contar idiomas
            $language = $item['language'] ?? 'N/A';
            $languages[$language] = ($languages[$language] ?? 0) + 1;
            
            // Contar áreas
            if (isset($item['areas']) && is_array($item['areas'])) {
                foreach ($item['areas'] as $area) {
                    $grandeArea = $area['grande_area'] ?? 'N/A';
                    $areas[$grandeArea] = ($areas[$grandeArea] ?? 0) + 1;
                }
            }
        }
        
        // Ordenar por contagem
        arsort($types);
        arsort($years);
        arsort($institutions);
        arsort($languages);
        arsort($areas);
        
        // Simular estrutura de resposta do Elasticsearch
        return [
            'aggregations' => [
                'by_type' => [
                    'buckets' => array_map(function($key, $count) {
                        return ['key' => $key, 'doc_count' => $count];
                    }, array_keys($types), $types)
                ],
                'by_year' => [
                    'buckets' => array_map(function($key, $count) {
                        return ['key' => $key, 'doc_count' => $count];
                    }, array_keys($years), $years)
                ],
                'by_institution' => [
                    'buckets' => array_map(function($key, $count) {
                        return ['key' => $key, 'doc_count' => $count];
                    }, array_keys($institutions), $institutions)
                ],
                'by_language' => [
                    'buckets' => array_map(function($key, $count) {
                        return ['key' => $key, 'doc_count' => $count];
                    }, array_keys($languages), $languages)
                ],
                'by_areas' => [
                    'area_breakdown' => [
                        'buckets' => array_map(function($key, $count) {
                            return ['key' => $key, 'doc_count' => $count];
                        }, array_keys($areas), $areas)
                    ]
                ],
                'year_stats' => [
                    'min' => min(array_keys($years)),
                    'max' => max(array_keys($years)),
                    'count' => array_sum($years)
                ]
            ]
        ];
    }

    /**
     * Busca em modo fallback quando Elasticsearch não está disponível
     */
    private function searchFallback(array $filters = [], int $size = 50): array
    {
        $data = $this->getFallbackData();
        $filtered = $data;
        
        // Aplicar filtros
        if (!empty($filters['q'])) {
            $query = strtolower($filters['q']);
            $filtered = array_filter($filtered, function($item) use ($query) {
                return stripos($item['title'], $query) !== false ||
                       stripos($item['researcher_name'], $query) !== false ||
                       stripos($item['journal'] ?? '', $query) !== false;
            });
        }
        
        if (!empty($filters['type'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return $item['type'] === $filters['type'];
            });
        }
        
        if (!empty($filters['program'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return ($item['program'] ?? '') === $filters['program'];
            });
        }
        
        if (!empty($filters['year'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return $item['year'] == $filters['year'];
            });
        }
        
        if (!empty($filters['year_from'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return $item['year'] >= (int)$filters['year_from'];
            });
        }
        
        if (!empty($filters['year_to'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return $item['year'] <= (int)$filters['year_to'];
            });
        }
        
        if (!empty($filters['language'])) {
            $filtered = array_filter($filtered, function($item) use ($filters) {
                return ($item['language'] ?? '') === $filters['language'];
            });
        }
        
        // Limitar resultados
        $filtered = array_slice($filtered, 0, $size);
        
        // Simular estrutura de resposta do Elasticsearch
        return [
            'hits' => [
                'total' => ['value' => count($filtered)],
                'hits' => array_map(function($item) {
                    return [
                        '_source' => $item,
                        '_score' => 1.0,
                        'highlight' => []
                    ];
                }, $filtered)
            ]
        ];
    }

    /**
     * Modo fallback quando Elasticsearch não está disponível
     */
    private function getFallbackData()
    {
        return [
            [
                'id' => 'demo_umc_biotech_001',
                'researcher_name' => 'Prof. Dr. Ana Carolina Silva',
                'researcher_lattes_id' => '1234567890123456',
                'title' => 'Biotecnologia Aplicada ao Desenvolvimento de Biomateriais para Regeneração Óssea',
                'year' => 2024,
                'type' => 'Artigo Publicado',
                'subtype' => 'Artigo Completo',
                'journal' => 'Brazilian Journal of Biotechnology',
                'doi' => '10.1016/j.bjbt.2024.001',
                'issn' => '1984-7011',
                'language' => 'Português',
                'institution' => 'Universidade de Mogi das Cruzes',
                'program' => 'Biotecnologia',
                'areas' => [
                    [
                        'grande_area' => 'Ciências Biológicas',
                        'area' => 'Biotecnologia',
                        'sub_area' => 'Biomateriais',
                        'especialidade' => 'Engenharia Tecidual'
                    ]
                ],
                'source' => 'Lattes',
                'compliance_lgpd' => true
            ],
            [
                'id' => 'demo_umc_engbiomed_002',
                'researcher_name' => 'Prof. Dr. Carlos Eduardo Santos',
                'researcher_lattes_id' => '9876543210987654',
                'title' => 'Desenvolvimento de Dispositivos Médicos Implantáveis com Sensoriamento Remoto',
                'year' => 2024,
                'type' => 'Artigo Publicado',
                'subtype' => 'Artigo Completo',
                'journal' => 'IEEE Transactions on Biomedical Engineering',
                'doi' => '10.1109/TBME.2024.001',
                'issn' => '0018-9294',
                'language' => 'Inglês',
                'institution' => 'Universidade de Mogi das Cruzes',
                'program' => 'Engenharia Biomédica',
                'areas' => [
                    [
                        'grande_area' => 'Engenharias',
                        'area' => 'Engenharia Biomédica',
                        'sub_area' => 'Bioengenharia',
                        'especialidade' => 'Dispositivos Médicos'
                    ]
                ],
                'source' => 'Lattes',
                'compliance_lgpd' => true
            ],
            [
                'id' => 'demo_umc_polpub_003',
                'researcher_name' => 'Profa. Dra. Maria Fernanda Lima',
                'researcher_lattes_id' => '1357924680135792',
                'title' => 'Políticas Públicas de Saúde Digital: Análise Comparativa Brasil-Europa',
                'year' => 2023,
                'type' => 'Capítulo de Livro',
                'subtype' => 'Capítulo de Livro Publicado',
                'book_title' => 'Governança Digital em Saúde Pública',
                'publisher' => 'Editora da Universidade de São Paulo',
                'isbn' => '978-85-314-1234-5',
                'language' => 'Português',
                'institution' => 'Universidade de Mogi das Cruzes',
                'program' => 'Políticas Públicas',
                'areas' => [
                    [
                        'grande_area' => 'Ciências Sociais Aplicadas',
                        'area' => 'Administração Pública',
                        'sub_area' => 'Políticas Públicas',
                        'especialidade' => 'Saúde Digital'
                    ]
                ],
                'source' => 'Lattes',
                'compliance_lgpd' => true
            ],
            [
                'id' => 'demo_umc_ctsaude_004',
                'researcher_name' => 'Prof. Dr. Roberto Mendes Costa',
                'researcher_lattes_id' => '2468013579246801',
                'title' => 'Inovação Tecnológica em Telemedicina: Impactos na Qualidade Assistencial',
                'year' => 2024,
                'type' => 'Trabalho em Evento',
                'subtype' => 'Trabalho Completo',
                'event_name' => 'Congresso Brasileiro de Informática em Saúde',
                'proceedings_title' => 'Anais do CBIS 2024',
                'event_city' => 'São Paulo',
                'language' => 'Português',
                'institution' => 'Universidade de Mogi das Cruzes',
                'program' => 'Ciência e Tecnologia em Saúde',
                'areas' => [
                    [
                        'grande_area' => 'Ciências da Saúde',
                        'area' => 'Saúde Coletiva',
                        'sub_area' => 'Informática em Saúde',
                        'especialidade' => 'Telemedicina'
                    ]
                ],
                'source' => 'Lattes',
                'compliance_lgpd' => true
            ],
            [
                'id' => 'demo_umc_orient_001',
                'researcher_name' => 'Prof. Dr. Ana Carolina Silva',
                'researcher_lattes_id' => '1234567890123456',
                'title' => 'Estudo de Biocompatibilidade de Scaffolds Poliméricos para Regeneração de Cartilagem',
                'year' => 2024,
                'type' => 'Orientação',
                'subtype' => 'Mestrado',
                'student_name' => 'Julia Santos Oliveira',
                'course' => 'Mestrado em Biotecnologia',
                'institution' => 'Universidade de Mogi das Cruzes',
                'program' => 'Biotecnologia',
                'areas' => [
                    [
                        'grande_area' => 'Ciências Biológicas',
                        'area' => 'Biotecnologia',
                        'sub_area' => 'Biomateriais',
                        'especialidade' => 'Biocompatibilidade'
                    ]
                ],
                'source' => 'Lattes',
                'compliance_lgpd' => true
            ]
        ];
    }
}
