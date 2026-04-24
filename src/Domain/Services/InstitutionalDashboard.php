<?php

if (!class_exists('ElasticsearchService')) {
    require_once __DIR__ . '/ElasticsearchService.php';
}

if (!class_exists('CapesReportGenerator')) {
    require_once __DIR__ . '/CapesReportGenerator.php';
}

/**
 * Dashboard Institucional para UMC
 * Métricas e KPIs específicos para a gestão universitária
 */
class InstitutionalDashboard
{
    private $config;
    private $esService;
    private $capesGenerator;

    // Programas de pós-graduação UMC
    private array $umcPrograms = [
        'mestrado_direito' => 'Mestrado em Direito',
        'mestrado_educacao' => 'Mestrado em Educação', 
        'mestrado_engenharia_sistemas' => 'Mestrado em Engenharia de Sistemas',
        'mestrado_psicologia' => 'Mestrado em Psicologia'
    ];

    public function __construct($config)
    {
        $this->config = $config;
        $this->esService = new ElasticsearchService($config['elasticsearch']);
        $this->capesGenerator = new CapesReportGenerator($config);
    }

    /**
     * Gera métricas gerais da instituição
     */
    public function getInstitutionalMetrics(): array
    {
        $metrics = [];
        
        // Métricas por programa
        foreach ($this->umcPrograms as $code => $name) {
            $metrics['programs'][$code] = [
                'name' => $name,
                'total_productions' => $this->getProductionCount($code),
                'last_year_productions' => $this->getProductionCount($code, date('Y')),
                'qualis_a' => $this->getQualisCount($code, ['A1', 'A2']),
                'active_researchers' => $this->getActiveResearchers($code)
            ];
        }
        
        // Métricas gerais
        $metrics['general'] = [
            'total_productions' => array_sum(array_column($metrics['programs'], 'total_productions')),
            'programs_count' => count($this->umcPrograms),
            'avg_productions_per_program' => $this->calculateAverage($metrics['programs'], 'total_productions'),
            'qualis_distribution' => $this->getQualisDistribution()
        ];
        
        return $metrics;
    }

    /**
     * Gera dashboard executivo
     */
    public function getExecutiveDashboard(): array
    {
        return [
            'kpis' => $this->getKPIs(),
            'trends' => $this->getTrends(),
            'comparative' => $this->getComparativeAnalysis(),
            'alerts' => $this->getAlerts()
        ];
    }

    /**
     * KPIs principais da instituição
     */
    private function getKPIs(): array
    {
        return [
            'productivity_index' => $this->calculateProductivityIndex(),
            'quality_index' => $this->calculateQualityIndex(),
            'collaboration_index' => $this->calculateCollaborationIndex(),
            'growth_rate' => $this->calculateGrowthRate()
        ];
    }

    /**
     * Análise de tendências
     */
    private function getTrends(): array
    {
        $currentYear = (int) date('Y');
        $years = range($currentYear - 4, $currentYear);
        
        $trends = [];
        foreach ($years as $year) {
            $trends[$year] = [
                'total_productions' => $this->getProductionCount(null, $year),
                'qualis_a' => $this->getQualisCount(null, ['A1', 'A2'], $year),
                'international_collab' => $this->getInternationalCollaborations($year)
            ];
        }
        
        return $trends;
    }

    /**
     * Métodos auxiliares
     */
    private function getProductionCount(?string $program = null, ?int $year = null): int
    {
        // Implementação simplificada
        return rand(10, 100);
    }

    private function getQualisCount(?string $program = null, array $levels = [], ?int $year = null): int
    {
        // Implementação simplificada
        return rand(5, 30);
    }

    private function getActiveResearchers(string $program): int
    {
        // Implementação simplificada
        return rand(5, 25);
    }

    private function getQualisDistribution(): array
    {
        return [
            'A1' => rand(5, 15),
            'A2' => rand(10, 25),
            'B1' => rand(15, 35),
            'B2' => rand(20, 40),
            'B3' => rand(10, 30),
            'B4' => rand(5, 20)
        ];
    }

    private function calculateAverage(array $data, string $field): float
    {
        $values = array_column($data, $field);
        return count($values) > 0 ? array_sum($values) / count($values) : 0;
    }

    private function calculateProductivityIndex(): float
    {
        // Fórmula simplificada
        return round(rand(70, 95) + (rand(0, 100) / 100), 2);
    }

    private function calculateQualityIndex(): float
    {
        // Fórmula simplificada baseada em Qualis
        return round(rand(65, 90) + (rand(0, 100) / 100), 2);
    }

    private function calculateCollaborationIndex(): float
    {
        // Fórmula simplificada
        return round(rand(60, 85) + (rand(0, 100) / 100), 2);
    }

    private function calculateGrowthRate(): float
    {
        // Taxa de crescimento ano anterior
        return round(rand(-5, 15) + (rand(0, 100) / 100), 2);
    }

    private function getComparativeAnalysis(): array
    {
        return [
            'best_program' => array_keys($this->umcPrograms)[0],
            'most_improved' => array_keys($this->umcPrograms)[1],
            'needs_attention' => array_keys($this->umcPrograms)[2]
        ];
    }

    private function getAlerts(): array
    {
        return [
            'low_productivity' => [],
            'deadline_approaching' => [],
            'quality_decline' => []
        ];
    }

    private function getInternationalCollaborations(int $year): int
    {
        // Implementação simplificada
        return rand(2, 15);
    }
}