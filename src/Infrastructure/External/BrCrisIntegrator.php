<?php

if (!class_exists('ElasticsearchService')) {
    require_once __DIR__ . '/ElasticsearchService.php';
}

if (!class_exists('LogService')) {
    require_once __DIR__ . '/LogService.php';
}

/**
 * Integrador BrCris - Sistema Nacional de Pesquisa e Inovação
 * Especificamente adaptado para UMC
 */
class BrCrisIntegrator
{
    private $config;
    private $esService;
    private $logService;
    private string $baseUrl = 'https://brcris.ibict.br';

    public function __construct($config)
    {
        $this->config = $config;
        $this->esService = new ElasticsearchService($config['elasticsearch']);
        $this->logService = new LogService($config);
    }

    /**
     * Sincroniza dados de produção científica com BrCris
     */
    public function syncProductions(array $productions): array
    {
        $results = [];
        
        foreach ($productions as $production) {
            try {
                $result = $this->sendToBrCris($production);
                $results[] = $result;
                
                $this->logService->log('INFO', 'Sincronização BrCris realizada', [
                    'production_id' => $production['id'] ?? 'N/A',
                    'status' => $result['status']
                ]);
                
            } catch (Exception $e) {
                $this->logService->log('ERROR', 'Erro na sincronização BrCris', [
                    'production_id' => $production['id'] ?? 'N/A',
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Envia produção para BrCris
     */
    private function sendToBrCris(array $production): array
    {
        // Simula envio para BrCris (implementação simplificada)
        return [
            'status' => 'success',
            'brcris_id' => 'BR_' . uniqid(),
            'production_id' => $production['id'] ?? 'N/A',
            'timestamp' => date('c')
        ];
    }

    /**
     * Valida se produção atende critérios BrCris
     */
    public function validateForBrCris(array $production): array
    {
        $errors = [];
        
        if (empty($production['titulo'])) {
            $errors[] = 'Título é obrigatório';
        }
        
        if (empty($production['autores'])) {
            $errors[] = 'Pelo menos um autor é obrigatório';
        }
        
        if (empty($production['ano'])) {
            $errors[] = 'Ano de publicação é obrigatório';
        }
        
        return $errors;
    }

    /**
     * Gera relatório de sincronização
     */
    public function getSyncReport(string $startDate, string $endDate): array
    {
        // Implementação simplificada do relatório
        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'total_synced' => 0,
            'successful' => 0,
            'failed' => 0,
            'details' => []
        ];
    }
}