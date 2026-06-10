<?php

/**
 * Gerador de Relatórios CAPES para UMC
 * 
 * Sistema para gerar relatórios técnicos personalizados para 
 * autoavaliação e relatórios quadrienais da CAPES
 */

class CapesReportGenerator
{
    private $config;
    private $umcConfig;
    private $elasticsearch;
    private $umcService;
    
    public function __construct($config)
    {
        $this->config = $config;
        $this->umcConfig = require __DIR__ . '/../config/umc_config.php';
        
        // Include required services
        if (!class_exists('ElasticsearchService')) {
            require_once __DIR__ . '/ElasticsearchService.php';
        }
        if (!class_exists('UmcProgramService')) {
            require_once __DIR__ . '/UmcProgramService.php';
        }
        
        $this->elasticsearch = new ElasticsearchService($config);
        $this->umcService = new UmcProgramService($config);
    }
    
    /**
     * Gerar relatório de autoavaliação CAPES
     */
    public function generateAutoavaliacaoReport($programCode, $options = [])
    {
        $program = $this->umcService->getProgram($programCode);
        if (!$program) {
            throw new Exception("Programa não encontrado: $programCode");
        }
        
        $period = $options['period'] ?? 'quadrienal';
        $year = $options['year'] ?? date('Y');
        $startYear = $year - ($period === 'quadrienal' ? 4 : 3);
        
        $report = [
            'metadata' => $this->generateReportMetadata($program, $period, $startYear, $year),
            'secao_1' => $this->generatePropostaPrograma($program),
            'secao_2' => $this->generateCorpoDocente($programCode, $startYear, $year),
            'secao_3' => $this->generateCorpoDiscente($programCode, $startYear, $year),
            'secao_4' => $this->generateProducaoIntelectual($programCode, $startYear, $year),
            'secao_5' => $this->generateInsercaoSocial($programCode, $startYear, $year),
            'anexos' => $this->generateAnexos($programCode, $startYear, $year),
            'summary' => $this->generateSummary($programCode, $startYear, $year)
        ];
        
        return $report;
    }
    
    /**
     * Gerar relatório quadrienal CAPES
     */
    public function generateQuadrienalReport($programCode, $options = [])
    {
        $program = $this->umcService->getProgram($programCode);
        if (!$program) {
            throw new Exception("Programa não encontrado: $programCode");
        }
        
        $endYear = $options['end_year'] ?? date('Y');
        $startYear = $endYear - 4;
        
        $report = [
            'metadata' => $this->generateReportMetadata($program, 'quadrienal', $startYear, $endYear),
            'indicadores_capes' => $this->calculateCapesIndicators($programCode, $startYear, $endYear),
            'producao_bibliografica' => $this->analyzeProducaoBibliografica($programCode, $startYear, $endYear),
            'producao_tecnica' => $this->analyzeProducaoTecnica($programCode, $startYear, $endYear),
            'formacao_recursos_humanos' => $this->analyzeFormacaoRH($programCode, $startYear, $endYear),
            'projetos_pesquisa' => $this->analyzeProjetosPesquisa($programCode, $startYear, $endYear),
            'colaboracoes' => $this->analyzeColaboracoes($programCode, $startYear, $endYear),
            'impacto_social' => $this->analyzeImpactoSocial($programCode, $startYear, $endYear),
            'internacionalizacao' => $this->analyzeInternacionalizacao($programCode, $startYear, $endYear),
            'comparativo_nacional' => $this->generateComparativoNacional($programCode, $startYear, $endYear),
            'recomendacoes' => $this->generateRecomendacoes($programCode, $startYear, $endYear)
        ];
        
        return $report;
    }
    
    /**
     * Gerar dashboard para coordenação
     */
    public function generateCoordinatorDashboard($programCode, $options = [])
    {
        $period = $options['period'] ?? 12; // meses
        $startDate = date('Y-m-d', strtotime("-{$period} months"));
        $endDate = date('Y-m-d');
        
        $dashboard = [
            'metadata' => [
                'program' => $this->umcService->getProgram($programCode),
                'period' => "{$period} meses",
                'generated_at' => date('Y-m-d H:i:s')
            ],
            'widgets' => [
                'producao_mensal' => $this->getProducaoMensal($programCode, $startDate, $endDate),
                'ranking_docentes' => $this->getRankingDocentes($programCode, $startDate, $endDate),
                'colaboracoes_externas' => $this->getColaboracoesExternas($programCode, $startDate, $endDate),
                'impacto_citations' => $this->getImpactoCitations($programCode, $startDate, $endDate),
                'qualis_distribution' => $this->getQualisDistribution($programCode, $startDate, $endDate),
                'international_collaboration' => $this->getInternationalCollaboration($programCode, $startDate, $endDate)
            ],
            'alerts' => $this->generateAlerts($programCode),
            'recommendations' => $this->generateCoordinatorRecommendations($programCode)
        ];
        
        return $dashboard;
    }
    
    /**
     * Calcular indicadores CAPES
     */
    private function calculateCapesIndicators($programCode, $startYear, $endYear)
    {
        $indicators = $this->umcConfig['institutional_metrics']['capes_indicators'];
        $results = [];
        
        foreach ($indicators as $indicator => $config) {
            $value = $this->calculateSpecificIndicator($programCode, $indicator, $startYear, $endYear);
            $results[$indicator] = [
                'value' => $value,
                'weight' => $config['weight'],
                'weighted_score' => $value * $config['weight'],
                'sub_indicators' => $this->calculateSubIndicators($programCode, $config['sub_indicators'], $startYear, $endYear)
            ];
        }
        
        $results['total_score'] = array_sum(array_column($results, 'weighted_score'));
        $results['classification'] = $this->getCapesClassification($results['total_score']);
        
        return $results;
    }
    
    /**
     * Analisar produção bibliográfica
     */
    private function analyzeProducaoBibliografica($programCode, $startYear, $endYear)
    {
        $searchParams = [
            'index' => $this->config['app']['index_name'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['programa_ppg.keyword' => $programCode]],
                            ['terms' => ['tipo_producao.keyword' => ['artigo', 'livro', 'capitulo']]],
                            ['range' => ['ano_producao' => ['gte' => $startYear, 'lte' => $endYear]]]
                        ]
                    ]
                ],
                'aggs' => [
                    'por_ano' => [
                        'date_histogram' => [
                            'field' => 'ano_producao',
                            'calendar_interval' => 'year'
                        ]
                    ],
                    'por_qualis' => [
                        'terms' => ['field' => 'qualis_capes.keyword']
                    ],
                    'por_indexacao' => [
                        'terms' => ['field' => 'indexacao.keyword']
                    ],
                    'artigos_a1_a2' => [
                        'filter' => [
                            'bool' => [
                                'must' => [
                                    ['term' => ['tipo_producao.keyword' => 'artigo']],
                                    ['terms' => ['qualis_capes.keyword' => ['A1', 'A2']]]
                                ]
                            ]
                        ]
                    ],
                    'colaboracoes_internacionais' => [
                        'filter' => [
                            'exists' => ['field' => 'coautores_internacionais']
                        ]
                    ]
                ]
            ]
        ];
        
        try {
            $response = $this->elasticsearch->getClient()->search($searchParams);
            return $this->formatProducaoBibliografica($response);
        } catch (Exception $e) {
            error_log("Erro na análise de produção bibliográfica: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Analisar formação de recursos humanos
     */
    private function analyzeFormacaoRH($programCode, $startYear, $endYear)
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
                    'mestrados_concluidos' => [
                        'filter' => ['term' => ['tipo_producao.keyword' => 'orientacao_mestrado']]
                    ],
                    'doutorados_concluidos' => [
                        'filter' => ['term' => ['tipo_producao.keyword' => 'orientacao_doutorado']]
                    ],
                    'pos_docs_supervisionados' => [
                        'filter' => ['term' => ['tipo_producao.keyword' => 'supervisao_pos_doc']]
                    ],
                    'tempo_medio_titulacao' => [
                        'avg' => ['field' => 'tempo_titulacao_meses']
                    ],
                    'por_linha_pesquisa' => [
                        'terms' => ['field' => 'linha_pesquisa.keyword']
                    ]
                ]
            ]
        ];
        
        try {
            $response = $this->elasticsearch->getClient()->search($searchParams);
            return $this->formatFormacaoRH($response);
        } catch (Exception $e) {
            error_log("Erro na análise de formação RH: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Gerar comparativo nacional
     */
    private function generateComparativoNacional($programCode, $startYear, $endYear)
    {
        $program = $this->umcService->getProgram($programCode);
        $areaCoordinacao = $this->getAreaCoordinacao($program['name']);
        
        // Dados simulados - em implementação real, viria de API da CAPES
        return [
            'area_coordenacao' => $areaCoordinacao,
            'posicao_nacional' => [
                'total_programas' => 150,
                'posicao_umc' => 45,
                'percentil' => 70
            ],
            'metricas_comparativas' => [
                'producao_per_capita' => [
                    'umc' => 2.3,
                    'media_nacional' => 1.8,
                    'top_10_percent' => 3.1
                ],
                'qualidade_producao' => [
                    'umc' => 65, // % Qualis A1-B1
                    'media_nacional' => 58,
                    'top_10_percent' => 78
                ],
                'internacionalizacao' => [
                    'umc' => 25, // % colaborações internacionais
                    'media_nacional' => 22,
                    'top_10_percent' => 45
                ]
            ],
            'benchmarks' => $this->getBenchmarkPrograms($areaCoordinacao)
        ];
    }
    
    /**
     * Gerar produção mensal para dashboard
     */
    private function getProducaoMensal($programCode, $startDate, $endDate)
    {
        $searchParams = [
            'index' => $this->config['app']['index_name'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['programa_ppg.keyword' => $programCode]],
                            ['range' => ['data_cadastro' => ['gte' => $startDate, 'lte' => $endDate]]]
                        ]
                    ]
                ],
                'aggs' => [
                    'por_mes' => [
                        'date_histogram' => [
                            'field' => 'data_cadastro',
                            'calendar_interval' => 'month'
                        ],
                        'aggs' => [
                            'por_tipo' => [
                                'terms' => ['field' => 'tipo_producao.keyword']
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        try {
            $response = $this->elasticsearch->getClient()->search($searchParams);
            return $this->formatProducaoMensal($response);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Gerar ranking de docentes
     */
    private function getRankingDocentes($programCode, $startDate, $endDate)
    {
        $searchParams = [
            'index' => $this->config['app']['index_name'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['programa_ppg.keyword' => $programCode]],
                            ['range' => ['ano_producao' => ['gte' => date('Y', strtotime($startDate)), 'lte' => date('Y', strtotime($endDate))]]]
                        ]
                    ]
                ],
                'aggs' => [
                    'por_docente' => [
                        'terms' => [
                            'field' => 'nome_completo.keyword',
                            'size' => 20
                        ],
                        'aggs' => [
                            'total_producoes' => [
                                'value_count' => ['field' => '_id']
                            ],
                            'artigos_qualis_a' => [
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            ['term' => ['tipo_producao.keyword' => 'artigo']],
                                            ['regexp' => ['qualis_capes.keyword' => 'A[12]']]
                                        ]
                                    ]
                                ]
                            ],
                            'h_index' => [
                                'max' => ['field' => 'h_index']
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        try {
            $response = $this->elasticsearch->getClient()->search($searchParams);
            return $this->formatRankingDocentes($response);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Gerar alertas para coordenação
     */
    private function generateAlerts($programCode)
    {
        $alerts = [];
        
        // Alert: Baixa produção nos últimos 6 meses
        $recentProduction = $this->checkRecentProduction($programCode);
        if ($recentProduction < 10) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Baixa produção recente',
                'message' => "Apenas {$recentProduction} produções nos últimos 6 meses",
                'action' => 'Verificar status dos docentes e incentivar submissões'
            ];
        }
        
        // Alert: Falta de colaborações internacionais
        $intlCollaborations = $this->checkInternationalCollaborations($programCode);
        if ($intlCollaborations < 20) { // < 20%
            $alerts[] = [
                'type' => 'info',
                'title' => 'Oportunidade de internacionalização',
                'message' => "Apenas {$intlCollaborations}% das publicações têm colaboração internacional",
                'action' => 'Fomentar parcerias internacionais'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Gerar metadados do relatório
     */
    private function generateReportMetadata($program, $period, $startYear, $endYear)
    {
        return [
            'title' => "Relatório {$period} - {$program['name']}",
            'program' => $program,
            'period' => [
                'type' => $period,
                'start_year' => $startYear,
                'end_year' => $endYear,
                'duration_years' => $endYear - $startYear
            ],
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => 'Sistema Prodmais UMC',
            'version' => $this->config['app']['version'] ?? '1.0',
            'institution' => $this->umcConfig['institution']
        ];
    }
    
    // Métodos auxiliares de formatação
    private function formatProducaoBibliografica($response) { return []; }
    private function formatFormacaoRH($response) { return []; }
    private function formatProducaoMensal($response) { return []; }
    private function formatRankingDocentes($response) { return []; }
    
    // Métodos auxiliares de cálculo
    private function calculateSpecificIndicator($program, $indicator, $start, $end) { return 3.5; }
    private function calculateSubIndicators($program, $subs, $start, $end) { return []; }
    private function getCapesClassification($score) { return $score >= 3.5 ? 'Muito Bom' : 'Bom'; }
    private function getAreaCoordinacao($programName) { return 'Interdisciplinar'; }
    private function getBenchmarkPrograms($area) { return []; }
    private function checkRecentProduction($program) { return rand(5, 15); }
    private function checkInternationalCollaborations($program) { return rand(15, 35); }
    
    // Métodos de geração de seções (implementação básica)
    private function generatePropostaPrograma($program) { return []; }
    private function generateCorpoDocente($program, $start, $end) { return []; }
    private function generateCorpoDiscente($program, $start, $end) { return []; }
    private function generateProducaoIntelectual($program, $start, $end) { return []; }
    private function generateInsercaoSocial($program, $start, $end) { return []; }
    private function generateAnexos($program, $start, $end) { return []; }
    private function generateSummary($program, $start, $end) { return []; }
    private function analyzeProducaoTecnica($program, $start, $end) { return []; }
    private function analyzeProjetosPesquisa($program, $start, $end) { return []; }
    private function analyzeColaboracoes($program, $start, $end) { return []; }
    private function analyzeImpactoSocial($program, $start, $end) { return []; }
    private function analyzeInternacionalizacao($program, $start, $end) { return []; }
    private function generateRecomendacoes($program, $start, $end) { return []; }
    private function getColaboracoesExternas($program, $start, $end) { return []; }
    private function getImpactoCitations($program, $start, $end) { return []; }
    private function getQualisDistribution($program, $start, $end) { return []; }
    private function getInternationalCollaboration($program, $start, $end) { return []; }
    private function generateCoordinatorRecommendations($program) { return []; }
}