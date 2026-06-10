<?php

if (!class_exists('LogService')) {
    require_once __DIR__ . '/LogService.php';
}

/**
 * Validador de Produção Científica para UMC
 * Implementa regras específicas da CAPES e institucionais
 */
class ProductionValidator
{
    private $config;
    private $logService;

    // Regras de validação CAPES
    private array $capesRules = [
        'artigo' => [
            'required_fields' => ['titulo', 'autores', 'periodico', 'ano', 'volume'],
            'min_authors' => 1,
            'max_title_length' => 500,
            'year_range' => [1990, null] // null = ano atual
        ],
        'livro' => [
            'required_fields' => ['titulo', 'autores', 'editora', 'ano', 'isbn'],
            'min_pages' => 50,
            'min_authors' => 1
        ],
        'capitulo' => [
            'required_fields' => ['titulo', 'autores', 'livro', 'editora', 'ano', 'paginas'],
            'min_pages' => 10,
            'min_authors' => 1
        ],
        'evento' => [
            'required_fields' => ['titulo', 'autores', 'evento', 'ano', 'local'],
            'min_authors' => 1
        ]
    ];

    // Programas UMC e suas especificidades
    private array $umcPrograms = [
        'mestrado_direito' => [
            'preferred_journals' => ['direito', 'juridico', 'law'],
            'min_qualis' => 'B2'
        ],
        'mestrado_educacao' => [
            'preferred_journals' => ['educacao', 'education', 'pedagogia'],
            'min_qualis' => 'B2'
        ],
        'mestrado_engenharia_sistemas' => [
            'preferred_journals' => ['engineering', 'systems', 'computer'],
            'min_qualis' => 'B1'
        ],
        'mestrado_psicologia' => [
            'preferred_journals' => ['psicologia', 'psychology', 'mental'],
            'min_qualis' => 'B2'
        ]
    ];

    public function __construct($config)
    {
        $this->config = $config;
        $this->logService = new LogService($config);
    }

    /**
     * Valida uma produção científica completa
     */
    public function validateProduction(array $production): array
    {
        $errors = [];
        $warnings = [];
        
        // Validações básicas
        $basicErrors = $this->validateBasicFields($production);
        $errors = array_merge($errors, $basicErrors);
        
        // Validações por tipo
        if (isset($production['tipo'])) {
            $typeErrors = $this->validateByType($production);
            $errors = array_merge($errors, $typeErrors);
        }
        
        // Validações UMC específicas
        if (isset($production['programa_umc'])) {
            $umcWarnings = $this->validateUmcSpecific($production);
            $warnings = array_merge($warnings, $umcWarnings);
        }
        
        // Validações CAPES
        $capesErrors = $this->validateCapesCompliance($production);
        $errors = array_merge($errors, $capesErrors);
        
        // Log da validação
        $this->logService->log('INFO', 'Validação de produção realizada', [
            'production_id' => $production['id'] ?? 'N/A',
            'errors_count' => count($errors),
            'warnings_count' => count($warnings)
        ]);
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'score' => $this->calculateQualityScore($production, $errors, $warnings)
        ];
    }

    /**
     * Validações básicas obrigatórias
     */
    private function validateBasicFields(array $production): array
    {
        $errors = [];
        
        if (empty($production['titulo'])) {
            $errors[] = 'Título é obrigatório';
        }
        
        if (empty($production['autores'])) {
            $errors[] = 'Pelo menos um autor é obrigatório';
        }
        
        if (empty($production['ano']) || !is_numeric($production['ano'])) {
            $errors[] = 'Ano válido é obrigatório';
        }
        
        if (empty($production['tipo'])) {
            $errors[] = 'Tipo de produção é obrigatório';
        }
        
        return $errors;
    }

    /**
     * Validações específicas por tipo de produção
     */
    private function validateByType(array $production): array
    {
        $errors = [];
        $tipo = strtolower($production['tipo']);
        
        if (!isset($this->capesRules[$tipo])) {
            $errors[] = "Tipo de produção '$tipo' não reconhecido";
            return $errors;
        }
        
        $rules = $this->capesRules[$tipo];
        
        // Campos obrigatórios
        foreach ($rules['required_fields'] as $field) {
            if (empty($production[$field])) {
                $errors[] = "Campo '$field' é obrigatório para tipo '$tipo'";
            }
        }
        
        // Validações específicas
        if (isset($rules['min_authors']) && count($production['autores']) < $rules['min_authors']) {
            $errors[] = "Mínimo de {$rules['min_authors']} autor(es) necessário";
        }
        
        if (isset($rules['year_range'])) {
            $minYear = $rules['year_range'][0];
            $maxYear = $rules['year_range'][1] ?? (int) date('Y');
            
            if ($production['ano'] < $minYear || $production['ano'] > $maxYear) {
                $errors[] = "Ano deve estar entre $minYear e $maxYear";
            }
        }
        
        return $errors;
    }

    /**
     * Validações específicas UMC
     */
    private function validateUmcSpecific(array $production): array
    {
        $warnings = [];
        $programa = $production['programa_umc'];
        
        if (!isset($this->umcPrograms[$programa])) {
            $warnings[] = "Programa UMC '$programa' não reconhecido";
            return $warnings;
        }
        
        $programRules = $this->umcPrograms[$programa];
        
        // Verifica adequação ao programa
        if (isset($programRules['preferred_journals']) && isset($production['periodico'])) {
            $periodico = strtolower($production['periodico']);
            $isRelevant = false;
            
            foreach ($programRules['preferred_journals'] as $keyword) {
                if (strpos($periodico, $keyword) !== false) {
                    $isRelevant = true;
                    break;
                }
            }
            
            if (!$isRelevant) {
                $warnings[] = "Periódico pode não ser relevante para o programa $programa";
            }
        }
        
        // Verifica Qualis mínimo
        if (isset($programRules['min_qualis']) && isset($production['qualis'])) {
            if (!$this->isQualisAdequate($production['qualis'], $programRules['min_qualis'])) {
                $warnings[] = "Qualis {$production['qualis']} abaixo do recomendado para o programa";
            }
        }
        
        return $warnings;
    }

    /**
     * Validações de conformidade CAPES
     */
    private function validateCapesCompliance(array $production): array
    {
        $errors = [];
        
        // Verifica se há DOI para artigos
        if ($production['tipo'] === 'artigo' && empty($production['doi'])) {
            $errors[] = 'DOI é altamente recomendado para artigos (CAPES)';
        }
        
        // Verifica afiliação institucional
        if (empty($production['afiliacao_umc'])) {
            $errors[] = 'Afiliação à UMC deve estar presente';
        }
        
        return $errors;
    }

    /**
     * Calcula score de qualidade da produção
     */
    private function calculateQualityScore(array $production, array $errors, array $warnings): int
    {
        $score = 100;
        
        // Penaliza por erros
        $score -= count($errors) * 15;
        
        // Penaliza por warnings
        $score -= count($warnings) * 5;
        
        // Bônus por completude
        if (isset($production['doi']) && !empty($production['doi'])) {
            $score += 5;
        }
        
        if (isset($production['qualis']) && in_array($production['qualis'], ['A1', 'A2'])) {
            $score += 10;
        }
        
        return max(0, min(100, $score));
    }

    /**
     * Verifica se Qualis é adequado
     */
    private function isQualisAdequate(string $atual, string $minimo): bool
    {
        $hierarchy = ['A1' => 7, 'A2' => 6, 'B1' => 5, 'B2' => 4, 'B3' => 3, 'B4' => 2, 'C' => 1];
        
        return ($hierarchy[$atual] ?? 0) >= ($hierarchy[$minimo] ?? 0);
    }

    /**
     * Valida lote de produções
     */
    public function validateBatch(array $productions): array
    {
        $results = [];
        
        foreach ($productions as $index => $production) {
            $results[$index] = $this->validateProduction($production);
        }
        
        return [
            'total' => count($productions),
            'valid' => count(array_filter($results, fn($r) => $r['valid'])),
            'invalid' => count(array_filter($results, fn($r) => !$r['valid'])),
            'details' => $results,
            'summary' => $this->generateValidationSummary($results)
        ];
    }

    /**
     * Gera resumo da validação
     */
    private function generateValidationSummary(array $results): array
    {
        $commonErrors = [];
        $commonWarnings = [];
        
        foreach ($results as $result) {
            foreach ($result['errors'] as $error) {
                $commonErrors[$error] = ($commonErrors[$error] ?? 0) + 1;
            }
            foreach ($result['warnings'] as $warning) {
                $commonWarnings[$warning] = ($commonWarnings[$warning] ?? 0) + 1;
            }
        }
        
        return [
            'most_common_errors' => array_slice($commonErrors, 0, 5, true),
            'most_common_warnings' => array_slice($commonWarnings, 0, 5, true),
            'average_score' => $this->calculateAverageScore($results)
        ];
    }

    private function calculateAverageScore(array $results): float
    {
        $scores = array_column($results, 'score');
        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }
}