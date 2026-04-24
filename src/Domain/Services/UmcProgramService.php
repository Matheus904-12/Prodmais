<?php

/**
 * Serviço de Programas de Pós-Graduação UMC
 * 
 * Gerencia dados específicos dos 4 PPGs da UMC conforme documentação PIVIC 2025
 */

class UmcProgramService
{
    private $config;
    private $umcConfig;
    private $elasticsearch;
    
    public function __construct($config)
    {
        $this->config = $config;
        $this->umcConfig = require __DIR__ . '/../config/umc_config.php';
        
        // Include ElasticsearchService if not already loaded
        if (!class_exists('ElasticsearchService')) {
            require_once __DIR__ . '/ElasticsearchService.php';
        }
        
        $this->elasticsearch = new ElasticsearchService($config);
    }
    
    /**
     * Obter informações de todos os programas UMC
     */
    public function getAllPrograms()
    {
        return $this->umcConfig['postgraduate_programs'];
    }
    
    /**
     * Obter informações de um programa específico
     */
    public function getProgram($programCode)
    {
        $programs = $this->umcConfig['postgraduate_programs'];
        
        foreach ($programs as $key => $program) {
            if ($program['code'] === $programCode || $key === $programCode) {
                return $program;
            }
        }
        
        return null;
    }
    
    /**
     * Obter linhas de pesquisa por programa
     */
    public function getResearchLinesByProgram($programCode)
    {
        $program = $this->getProgram($programCode);
        return $program ? $program['linhas_pesquisa'] : [];
    }
    
    /**
     * Buscar docentes por programa
     */
    public function getFacultyByProgram($programCode, $filters = [])
    {
        $searchParams = [
            'index' => $this->config['app']['index_name'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['programa_ppg.keyword' => $programCode]]
                        ]
                    ]
                ],
                'aggs' => [
                    'docentes' => [
                        'terms' => [
                            'field' => 'nome_completo.keyword',
                            'size' => 1000
                        ],
                        'aggs' => [
                            'ultima_producao' => [
                                'max' => ['field' => 'ano_producao']
                            ],
                            'total_producoes' => [
                                'value_count' => ['field' => 'id_lattes']
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        // Aplicar filtros adicionais
        if (!empty($filters['linha_pesquisa'])) {
            $searchParams['body']['query']['bool']['must'][] = [
                'term' => ['linha_pesquisa.keyword' => $filters['linha_pesquisa']]
            ];
        }
        
        if (!empty($filters['campus'])) {
            $searchParams['body']['query']['bool']['must'][] = [
                'term' => ['campus.keyword' => $filters['campus']]
            ];
        }
        
        try {
            $response = $this->elasticsearch->getClient()->search($searchParams);
            return $this->formatFacultyResponse($response);
        } catch (Exception $e) {
            error_log("Erro ao buscar docentes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter estatísticas de produção por programa
     */
    public function getProductionStatsByProgram($programCode, $year = null)
    {
        $searchParams = [
            'index' => $this->config['app']['index_name'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['programa_ppg.keyword' => $programCode]]
                        ]
                    ]
                ],
                'aggs' => [
                    'por_tipo' => [
                        'terms' => [
                            'field' => 'tipo_producao.keyword'
                        ]
                    ],
                    'por_ano' => [
                        'date_histogram' => [
                            'field' => 'ano_producao',
                            'calendar_interval' => 'year'
                        ]
                    ],
                    'indexacao' => [
                        'terms' => [
                            'field' => 'indexacao.keyword'
                        ]
                    ],
                    'qualis' => [
                        'terms' => [
                            'field' => 'qualis_capes.keyword'
                        ]
                    ]
                ],
                'size' => 0
            ]
        ];
        
        if ($year) {
            $searchParams['body']['query']['bool']['must'][] = [
                'term' => ['ano_producao' => $year]
            ];
        }
        
        try {
            $response = $this->elasticsearch->getClient()->search($searchParams);
            return $this->formatStatsResponse($response);
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Gerar relatório CAPES para um programa
     */
    public function generateCapesReport($programCode, $period = 'quadrienal')
    {
        $program = $this->getProgram($programCode);
        if (!$program) {
            throw new Exception("Programa não encontrado: $programCode");
        }
        
        // Calcular anos do período
        $currentYear = date('Y');
        $yearsBack = $period === 'quadrienal' ? 4 : 3;
        $startYear = $currentYear - $yearsBack;
        
        $report = [
            'programa' => $program,
            'periodo' => [
                'tipo' => $period,
                'ano_inicio' => $startYear,
                'ano_fim' => $currentYear
            ],
            'metricas' => $this->calculateCapesMetrics($programCode, $startYear, $currentYear),
            'docentes' => $this->getFacultyByProgram($programCode),
            'producao_intelectual' => $this->getIntellectualProduction($programCode, $startYear, $currentYear),
            'formacao_rh' => $this->getHumanResourcesFormation($programCode, $startYear, $currentYear),
            'impacto_sociedade' => $this->getSocialImpact($programCode, $startYear, $currentYear),
            'internacionalizacao' => $this->getInternationalization($programCode, $startYear, $currentYear)
        ];
        
        return $report;
    }
    
    /**
     * Calcular métricas CAPES para um programa
     */
    private function calculateCapesMetrics($programCode, $startYear, $endYear)
    {
        $indicators = $this->umcConfig['institutional_metrics']['capes_indicators'];
        $metrics = [];
        
        foreach ($indicators as $indicator => $config) {
            $value = $this->calculateIndicatorValue($programCode, $indicator, $startYear, $endYear);
            $metrics[$indicator] = [
                'value' => $value,
                'weight' => $config['weight'],
                'weighted_score' => $value * $config['weight']
            ];
        }
        
        $metrics['total_score'] = array_sum(array_column($metrics, 'weighted_score'));
        
        return $metrics;
    }
    
    /**
     * Obter produção intelectual do programa
     */
    private function getIntellectualProduction($programCode, $startYear, $endYear)
    {
        $searchParams = [
            'index' => $this->config['app']['index_name'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['programa_ppg.keyword' => $programCode]],
                            ['range' => ['ano_producao' => ['gte' => $startYear, 'lte' => $endYear]]]
                        ]
                    ]
                ],
                'aggs' => [
                    'artigos_periodicos' => [
                        'filter' => ['term' => ['tipo_producao.keyword' => 'artigo']],
                        'aggs' => [
                            'por_qualis' => [
                                'terms' => ['field' => 'qualis_capes.keyword']
                            ]
                        ]
                    ],
                    'livros_capitulos' => [
                        'filter' => ['terms' => ['tipo_producao.keyword' => ['livro', 'capitulo']]],
                        'aggs' => [
                            'total' => ['value_count' => ['field' => '_id']]
                        ]
                    ],
                    'trabalhos_eventos' => [
                        'filter' => ['term' => ['tipo_producao.keyword' => 'evento']],
                        'aggs' => [
                            'total' => ['value_count' => ['field' => '_id']]
                        ]
                    ]
                ]
            ]
        ];
        
        try {
            $response = $this->elasticsearch->getClient()->search($searchParams);
            return $this->formatProductionResponse($response);
        } catch (Exception $e) {
            error_log("Erro ao obter produção intelectual: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter dados de formação de recursos humanos
     */
    private function getHumanResourcesFormation($programCode, $startYear, $endYear)
    {
        $searchParams = [
            'index' => $this->config['app']['index_name'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['programa_ppg.keyword' => $programCode]],
                            ['terms' => ['tipo_producao.keyword' => ['orientacao_mestrado', 'orientacao_doutorado', 'supervisao_pos_doc']]],
                            ['range' => ['ano_conclusao' => ['gte' => $startYear, 'lte' => $endYear]]]
                        ]
                    ]
                ],
                'aggs' => [
                    'por_tipo' => [
                        'terms' => ['field' => 'tipo_producao.keyword']
                    ],
                    'por_ano' => [
                        'date_histogram' => [
                            'field' => 'ano_conclusao',
                            'calendar_interval' => 'year'
                        ]
                    ]
                ]
            ]
        ];
        
        try {
            $response = $this->elasticsearch->getClient()->search($searchParams);
            return $this->formatFormationResponse($response);
        } catch (Exception $e) {
            error_log("Erro ao obter formação RH: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcular valor de um indicador específico
     */
    private function calculateIndicatorValue($programCode, $indicator, $startYear, $endYear)
    {
        // Implementação específica para cada indicador CAPES
        switch ($indicator) {
            case 'producao_intelectual':
                return $this->calculateIntellectualProductionScore($programCode, $startYear, $endYear);
            case 'formacao_recursos_humanos':
                return $this->calculateHumanResourcesScore($programCode, $startYear, $endYear);
            case 'impacto_sociedade':
                return $this->calculateSocialImpactScore($programCode, $startYear, $endYear);
            case 'internacionalizacao':
                return $this->calculateInternationalizationScore($programCode, $startYear, $endYear);
            default:
                return 0.0;
        }
    }
    
    /**
     * Verificar conformidade LGPD
     */
    public function checkLgpdCompliance($data)
    {
        $compliance = [
            'status' => 'compliant',
            'issues' => [],
            'recommendations' => []
        ];
        
        // Verificar se há dados pessoais sensíveis
        $sensitiveFields = ['cpf', 'rg', 'endereco_residencial', 'telefone_pessoal'];
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $compliance['issues'][] = "Campo sensível detectado: $field";
                $compliance['status'] = 'non_compliant';
            }
        }
        
        // Verificar se dados são da Plataforma Lattes (públicos)
        if (!isset($data['fonte']) || $data['fonte'] !== 'lattes') {
            $compliance['recommendations'][] = 'Confirmar que dados são de fonte pública (Plataforma Lattes)';
        }
        
        // Verificar anonimização quando necessária
        if (isset($data['nivel_privacidade']) && $data['nivel_privacidade'] === 'anonymous') {
            $requiredAnonymization = ['nome_completo', 'email', 'id_lattes'];
            foreach ($requiredAnonymization as $field) {
                if (isset($data[$field]) && !$this->isAnonymized($data[$field])) {
                    $compliance['issues'][] = "Campo não anonimizado: $field";
                    $compliance['status'] = 'requires_action';
                }
            }
        }
        
        return $compliance;
    }
    
    /**
     * Verificar se um valor está anonimizado
     */
    private function isAnonymized($value)
    {
        // Verifica se o valor parece ser um hash ou está mascarado
        return preg_match('/^[a-f0-9]{32,}$/', $value) || strpos($value, '***') !== false;
    }
    
    /**
     * Formatar resposta de docentes
     */
    private function formatFacultyResponse($response)
    {
        $faculty = [];
        
        if (isset($response['aggregations']['docentes']['buckets'])) {
            foreach ($response['aggregations']['docentes']['buckets'] as $bucket) {
                $faculty[] = [
                    'nome' => $bucket['key'],
                    'total_producoes' => $bucket['total_producoes']['value'],
                    'ultima_producao' => $bucket['ultima_producao']['value'] ?? null
                ];
            }
        }
        
        return $faculty;
    }
    
    /**
     * Formatar resposta de estatísticas
     */
    private function formatStatsResponse($response)
    {
        $stats = [
            'por_tipo' => [],
            'por_ano' => [],
            'indexacao' => [],
            'qualis' => []
        ];
        
        if (isset($response['aggregations'])) {
            $aggs = $response['aggregations'];
            
            if (isset($aggs['por_tipo']['buckets'])) {
                foreach ($aggs['por_tipo']['buckets'] as $bucket) {
                    $stats['por_tipo'][$bucket['key']] = $bucket['doc_count'];
                }
            }
            
            if (isset($aggs['por_ano']['buckets'])) {
                foreach ($aggs['por_ano']['buckets'] as $bucket) {
                    $year = date('Y', $bucket['key'] / 1000);
                    $stats['por_ano'][$year] = $bucket['doc_count'];
                }
            }
            
            if (isset($aggs['indexacao']['buckets'])) {
                foreach ($aggs['indexacao']['buckets'] as $bucket) {
                    $stats['indexacao'][$bucket['key']] = $bucket['doc_count'];
                }
            }
            
            if (isset($aggs['qualis']['buckets'])) {
                foreach ($aggs['qualis']['buckets'] as $bucket) {
                    $stats['qualis'][$bucket['key']] = $bucket['doc_count'];
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Obter dados de impacto social
     */
    private function getSocialImpact($programCode, $startYear, $endYear)
    {
        // Implementação para métricas de impacto social
        return [
            'produtos_tecnicos' => 0,
            'patentes' => 0,
            'transferencia_tecnologia' => 0
        ];
    }
    
    /**
     * Obter dados de internacionalização
     */
    private function getInternationalization($programCode, $startYear, $endYear)
    {
        // Implementação para métricas de internacionalização
        return [
            'colaboracoes_internacionais' => 0,
            'publicacoes_internacionais' => 0,
            'intercambios' => 0
        ];
    }
    
    /**
     * Calcular scores específicos para cada métrica CAPES
     */
    private function calculateIntellectualProductionScore($programCode, $startYear, $endYear)
    {
        // Implementação do cálculo de pontuação da produção intelectual
        return 3.5; // Exemplo
    }
    
    private function calculateHumanResourcesScore($programCode, $startYear, $endYear)
    {
        // Implementação do cálculo de pontuação da formação de RH
        return 4.0; // Exemplo
    }
    
    private function calculateSocialImpactScore($programCode, $startYear, $endYear)
    {
        // Implementação do cálculo de pontuação do impacto social
        return 2.8; // Exemplo
    }
    
    private function calculateInternationalizationScore($programCode, $startYear, $endYear)
    {
        // Implementação do cálculo de pontuação da internacionalização
        return 3.2; // Exemplo
    }
    
    /**
     * Formatar resposta de produção
     */
    private function formatProductionResponse($response)
    {
        // Implementação da formatação da resposta de produção
        return [];
    }
    
    /**
     * Formatar resposta de formação
     */
    private function formatFormationResponse($response)
    {
        // Implementação da formatação da resposta de formação
        return [];
    }
}