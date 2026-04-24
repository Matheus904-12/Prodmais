<?php

class Anonymizer
{
    private array $sensitiveFields = [
        'cpf',
        'rg', 
        'email',
        'telefone',
        'endereco',
        'data_nascimento',
        'nome_completo',
        'orcid' // Pode ser sensível dependendo da política
    ];

    private array $hashSalt;
    private bool $preserveStatistics;

    public function __construct(string $salt = null, bool $preserveStatistics = true)
    {
        $this->hashSalt = $salt ? str_split($salt) : str_split('prodmais_default_salt_2025');
        $this->preserveStatistics = $preserveStatistics;
    }

    /**
     * Anonimiza dados pessoais de acordo com LGPD
     */
    public function anonymize(array $data, array $options = []): array
    {
        $anonymizationLevel = $options['level'] ?? 'standard';
        $fieldsToKeep = $options['keep_fields'] ?? [];
        $fieldsToRemove = $options['remove_fields'] ?? [];

        switch ($anonymizationLevel) {
            case 'minimal':
                return $this->minimalAnonymization($data, $fieldsToKeep, $fieldsToRemove);
            case 'standard':
                return $this->standardAnonymization($data, $fieldsToKeep, $fieldsToRemove);
            case 'full':
                return $this->fullAnonymization($data, $fieldsToKeep, $fieldsToRemove);
            case 'statistical':
                return $this->statisticalAnonymization($data);
            default:
                throw new \InvalidArgumentException("Nível de anonimização inválido: {$anonymizationLevel}");
        }
    }

    /**
     * Anonimização mínima - remove apenas dados altamente sensíveis
     */
    private function minimalAnonymization(array $data, array $keep = [], array $remove = []): array
    {
        $criticalFields = ['cpf', 'rg', 'email', 'telefone', 'endereco', 'data_nascimento'];
        $fieldsToProcess = array_merge($criticalFields, $remove);
        
        foreach ($fieldsToProcess as $field) {
            if (!in_array($field, $keep) && isset($data[$field])) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Anonimização padrão - remove dados pessoais mas mantém identificadores acadêmicos
     */
    private function standardAnonymization(array $data, array $keep = [], array $remove = []): array
    {
        $fieldsToProcess = array_merge($this->sensitiveFields, $remove);
        
        foreach ($fieldsToProcess as $field) {
            if (!in_array($field, $keep) && isset($data[$field])) {
                if ($field === 'researcher_name' || $field === 'nome_completo') {
                    // Para nomes, usar hash ou iniciais dependendo da configuração
                    $data[$field] = $this->anonymizeName($data[$field]);
                } elseif ($field === 'researcher_lattes_id') {
                    // Para IDs Lattes, usar hash preservando formato
                    $data[$field] = $this->hashPreservingFormat($data[$field]);
                } else {
                    unset($data[$field]);
                }
            }
        }

        // Anonimizar dados aninhados (autores, colaboradores)
        if (isset($data['authors']) && is_array($data['authors'])) {
            $data['authors'] = $this->anonymizeAuthors($data['authors']);
        }

        if (isset($data['openalex_coauthors']) && is_array($data['openalex_coauthors'])) {
            $data['openalex_coauthors'] = $this->anonymizeCoauthors($data['openalex_coauthors']);
        }

        return $data;
    }

    /**
     * Anonimização completa - remove todos os identificadores pessoais
     */
    private function fullAnonymization(array $data, array $keep = [], array $remove = []): array
    {
        $personalFields = [
            'researcher_name', 'researcher_lattes_id', 'cpf', 'rg', 'email', 
            'telefone', 'endereco', 'data_nascimento', 'orcid', 'student_name',
            'authors', 'openalex_coauthors'
        ];
        
        $fieldsToProcess = array_merge($personalFields, $remove);
        
        foreach ($fieldsToProcess as $field) {
            if (!in_array($field, $keep) && isset($data[$field])) {
                if ($this->preserveStatistics && ($field === 'researcher_name' || $field === 'researcher_lattes_id')) {
                    // Manter hash para preservar contagens únicas
                    $data[$field] = $this->generateConsistentHash($data[$field]);
                } else {
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * Anonimização estatística - preserva apenas dados agregados
     */
    private function statisticalAnonymization(array $data): array
    {
        $statisticalFields = [
            'year', 'type', 'subtype', 'language', 'institution', 'city', 
            'state', 'country', 'areas', 'journal', 'publisher', 'event_name'
        ];

        $anonymized = [];
        foreach ($statisticalFields as $field) {
            if (isset($data[$field])) {
                $anonymized[$field] = $data[$field];
            }
        }

        // Adicionar ID único anonimizado para contagens
        $anonymized['anonymous_id'] = $this->generateConsistentHash(
            ($data['researcher_lattes_id'] ?? '') . 
            ($data['title'] ?? '') . 
            ($data['year'] ?? '')
        );

        return $anonymized;
    }

    /**
     * Anonimiza nome mantendo formato ou usando iniciais
     */
    private function anonymizeName(string $name): string
    {
        $parts = explode(' ', trim($name));
        
        if (count($parts) <= 1) {
            return 'Pesquisador ' . substr($this->generateConsistentHash($name), 0, 8);
        }

        // Manter primeira letra de cada nome
        $anonymized = [];
        foreach ($parts as $part) {
            if (strlen($part) > 0) {
                $anonymized[] = strtoupper($part[0]) . '.';
            }
        }

        return implode(' ', $anonymized);
    }

    /**
     * Anonimiza lista de autores
     */
    private function anonymizeAuthors(array $authors): array
    {
        foreach ($authors as &$author) {
            if (isset($author['name'])) {
                $author['name'] = $this->anonymizeName($author['name']);
            }
            if (isset($author['citation_name'])) {
                $author['citation_name'] = $this->anonymizeName($author['citation_name']);
            }
        }
        
        return $authors;
    }

    /**
     * Anonimiza lista de coautores do OpenAlex
     */
    private function anonymizeCoauthors(array $coauthors): array
    {
        foreach ($coauthors as &$coauthor) {
            if (isset($coauthor['name'])) {
                $coauthor['name'] = $this->anonymizeName($coauthor['name']);
            }
            if (isset($coauthor['orcid'])) {
                $coauthor['orcid'] = $this->hashPreservingFormat($coauthor['orcid']);
            }
        }
        
        return $coauthors;
    }

    /**
     * Gera hash consistente para o mesmo input
     */
    private function generateConsistentHash(string $input): string
    {
        $salt = implode('', $this->hashSalt);
        return substr(hash('sha256', $salt . $input), 0, 16);
    }

    /**
     * Cria hash preservando formato original (para IDs)
     */
    private function hashPreservingFormat(string $input): string
    {
        $hash = $this->generateConsistentHash($input);
        
        // Se parece com ID Lattes (16 dígitos), manter formato
        if (preg_match('/^\d{16}$/', $input)) {
            return substr(str_pad($hash, 16, '0'), 0, 16);
        }
        
        // Se parece com ORCID, manter formato
        if (preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[0-9X]$/', $input)) {
            $digits = preg_replace('/[^0-9X]/', '', $hash . '0000000000000000');
            return substr($digits, 0, 4) . '-' . 
                   substr($digits, 4, 4) . '-' . 
                   substr($digits, 8, 4) . '-' . 
                   substr($digits, 12, 4);
        }
        
        return $hash;
    }

    /**
     * Verifica se dados contêm informações pessoais
     */
    public function containsPersonalData(array $data): bool
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Gera relatório de anonimização
     */
    public function generateAnonymizationReport(array $originalData, array $anonymizedData): array
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'original_fields_count' => count($originalData),
            'anonymized_fields_count' => count($anonymizedData),
            'removed_fields' => [],
            'anonymized_fields' => [],
            'preserved_fields' => []
        ];

        foreach ($originalData as $key => $value) {
            if (!isset($anonymizedData[$key])) {
                $report['removed_fields'][] = $key;
            } elseif ($originalData[$key] !== $anonymizedData[$key]) {
                $report['anonymized_fields'][] = $key;
            } else {
                $report['preserved_fields'][] = $key;
            }
        }

        return $report;
    }

    /**
     * Anonimiza em lote respeitando limites de memória
     */
    public function anonymizeBatch(array $dataArray, array $options = [], int $batchSize = 1000): \Generator
    {
        $total = count($dataArray);
        $processed = 0;

        while ($processed < $total) {
            $batch = array_slice($dataArray, $processed, $batchSize);
            $anonymizedBatch = [];

            foreach ($batch as $item) {
                $anonymizedBatch[] = $this->anonymize($item, $options);
            }

            yield [
                'data' => $anonymizedBatch,
                'progress' => [
                    'processed' => $processed + count($batch),
                    'total' => $total,
                    'percentage' => round((($processed + count($batch)) / $total) * 100, 2)
                ]
            ];

            $processed += count($batch);
        }
    }

    /**
     * Método legado para compatibilidade
     */
    public static function anonymize_static($data): array
    {
        $anonymizer = new self();
        return $anonymizer->anonymize($data, ['level' => 'minimal']);
    }
}
