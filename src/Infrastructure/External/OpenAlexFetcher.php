<?php

class OpenAlexFetcher
{
    private string $baseUrl = 'https://api.openalex.org';
    private array $rateLimitInfo = ['remaining' => 100, 'reset_time' => 0];
    private $config;
    private $cacheDb = null;

    public function __construct($config = null, private ?string $email = null)
    {
        $this->config = $config;
        // OpenAlex solicita identificação por cortesia
        if ($this->email) {
            $this->baseUrl .= "?mailto={$this->email}";
        }
        $this->initCache();
    }

    private function initCache()
    {
        try {
            $cachePath = dirname(__DIR__) . '/data/cache_openalex.sqlite';
            $this->cacheDb = new \SQLite3($cachePath);
            $this->cacheDb->exec("CREATE TABLE IF NOT EXISTS doi_cache (doi TEXT PRIMARY KEY, json_data TEXT, created_at DATETIME)");
        } catch (\Exception $e) {
            error_log("Erro ao inicializar cache OpenAlex: " . $e->getMessage());
        }
    }

    private function getFromCache(string $doi): ?array
    {
        if (!$this->cacheDb) return null;
        
        $stmt = $this->cacheDb->prepare("SELECT json_data FROM doi_cache WHERE doi = ?");
        $stmt->bindValue(1, $doi);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        return $row ? json_decode($row['json_data'], true) : null;
    }

    private function saveToCache(string $doi, array $data)
    {
        if (!$this->cacheDb) return;
        
        $stmt = $this->cacheDb->prepare("INSERT OR REPLACE INTO doi_cache (doi, json_data, created_at) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $doi);
        $stmt->bindValue(2, json_encode($data));
        $stmt->bindValue(3, date('Y-m-d H:i:s'));
        $stmt->execute();
    }

    /**
     * Busca obras por DOI
     */
    public function searchByDoi(string $doi): ?array
    {
        // Tentar cache primeiro
        $cached = $this->getFromCache($doi);
        if ($cached) {
            return $cached;
        }

        $url = $this->baseUrl . "/works/https://doi.org/{$doi}";
        $data = $this->makeRequest($url);
        
        if ($data) {
            $this->saveToCache($doi, $data);
        }
        
        return $data;
    }

    /**
     * Busca obras por título
     */
    public function searchByTitle(string $title, int $limit = 5): array
    {
        $title = urlencode($title);
        $separator = strpos($this->baseUrl, '?') ? '&' : '?';
        $url = $this->baseUrl . "/works{$separator}search={$title}&per-page={$limit}";
        
        $response = $this->makeRequest($url);
        return $response['results'] ?? [];
    }

    /**
     * Busca obras por autor (ORCID)
     */
    public function searchByOrcid(string $orcid, int $limit = 100): array
    {
        $separator = strpos($this->baseUrl, '?') ? '&' : '?';
        $url = $this->baseUrl . "/works{$separator}filter=authorships.author.orcid:{$orcid}&per-page={$limit}";
        
        $response = $this->makeRequest($url);
        return $response['results'] ?? [];
    }

    /**
     * Busca autor por ORCID
     */
    public function getAuthorByOrcid(string $orcid): ?array
    {
        $url = $this->baseUrl . "/authors/https://orcid.org/{$orcid}";
        return $this->makeRequest($url);
    }

    /**
     * Busca autores por nome
     */
    public function searchAuthorByName(string $name, int $limit = 10): array
    {
        $name = urlencode($name);
        $separator = strpos($this->baseUrl, '?') ? '&' : '?';
        $url = $this->baseUrl . "/authors{$separator}search={$name}&per-page={$limit}";
        
        $response = $this->makeRequest($url);
        return $response['results'] ?? [];
    }

    /**
     * Enriquece dados de uma produção científica
     */
    public function enrichProduction(array $production): array
    {
        $enrichedData = [];

        // Tentar buscar por DOI primeiro
        if (!empty($production['doi'])) {
            $openAlexData = $this->searchByDoi($production['doi']);
            if ($openAlexData) {
                $enrichedData = $this->normalizeOpenAlexData($openAlexData);
            }
        }

        // Se não encontrou por DOI, tentar por título
        if (empty($enrichedData) && !empty($production['title'])) {
            $results = $this->searchByTitle($production['title'], 1);
            if (!empty($results)) {
                // Verificar similaridade do título
                $similarity = $this->calculateTitleSimilarity($production['title'], $results[0]['title'] ?? '');
                if ($similarity > 0.8) { // 80% de similaridade
                    $enrichedData = $this->normalizeOpenAlexData($results[0]);
                }
            }
        }

        // Mesclar dados enriquecidos com dados originais
        return array_merge($production, $enrichedData);
    }

    /**
     * Normaliza dados do OpenAlex para o formato interno
     */
    private function normalizeOpenAlexData(array $openAlexData): array
    {
        $normalized = [
            'openalex_id' => $openAlexData['id'] ?? null,
            'openalex_url' => $openAlexData['id'] ?? null,
            'cited_by_count' => $openAlexData['cited_by_count'] ?? 0,
            'publication_date' => $openAlexData['publication_date'] ?? null,
            'is_open_access' => $openAlexData['open_access']['is_oa'] ?? false,
            'open_access_url' => $openAlexData['open_access']['oa_url'] ?? null
        ];

        // Extrair dados da revista/fonte
        if (!empty($openAlexData['primary_location']['source'])) {
            $source = $openAlexData['primary_location']['source'];
            $normalized['journal_openalex'] = $source['display_name'] ?? null;
            $normalized['journal_issn'] = isset($source['issn']) ? implode(', ', $source['issn']) : null;
            $normalized['is_in_doaj'] = $source['is_in_doaj'] ?? false;
        }

        // Extrair áreas de conhecimento
        if (!empty($openAlexData['concepts'])) {
            $concepts = [];
            foreach ($openAlexData['concepts'] as $concept) {
                if (($concept['score'] ?? 0) > 0.3) { // Apenas conceitos com score > 30%
                    $concepts[] = [
                        'name' => $concept['display_name'],
                        'level' => $concept['level'],
                        'score' => $concept['score']
                    ];
                }
            }
            $normalized['openalex_concepts'] = $concepts;
        }

        // Extrair informações de coautoria
        if (!empty($openAlexData['authorships'])) {
            $coauthors = [];
            foreach ($openAlexData['authorships'] as $authorship) {
                $author = $authorship['author'] ?? [];
                if (!empty($author['display_name'])) {
                    $coauthors[] = [
                        'name' => $author['display_name'],
                        'orcid' => $author['orcid'] ?? null,
                        'institutions' => array_map(function($inst) {
                            return $inst['display_name'] ?? '';
                        }, $authorship['institutions'] ?? [])
                    ];
                }
            }
            $normalized['openalex_coauthors'] = $coauthors;
        }

        return $normalized;
    }

    /**
     * Calcula similaridade entre títulos
     */
    private function calculateTitleSimilarity(string $title1, string $title2): float
    {
        $title1 = strtolower(trim($title1));
        $title2 = strtolower(trim($title2));
        
        if (empty($title1) || empty($title2)) {
            return 0.0;
        }

        // Usar algoritmo de Levenshtein com normalização
        $maxLen = max(strlen($title1), strlen($title2));
        $distance = levenshtein($title1, $title2);
        
        return 1 - ($distance / $maxLen);
    }

    /**
     * Faz requisição HTTP para a API do OpenAlex
     */
    private function makeRequest(string $url): ?array
    {
        // Respeitar rate limiting
        if ($this->rateLimitInfo['remaining'] <= 1 && time() < $this->rateLimitInfo['reset_time']) {
            $sleepTime = $this->rateLimitInfo['reset_time'] - time() + 1;
            sleep($sleepTime);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Prodmais/1.0 (https://github.com/unifesp/prodmais)',
                    'Accept: application/json',
                    'Content-Type: application/json'
                ],
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            error_log("Erro ao fazer requisição para OpenAlex: {$url}");
            return null;
        }

        // Atualizar informações de rate limiting a partir dos headers
        if (!empty($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (strpos($header, 'x-ratelimit-remaining:') === 0) {
                    $this->rateLimitInfo['remaining'] = (int)trim(substr($header, 22));
                } elseif (strpos($header, 'x-ratelimit-reset:') === 0) {
                    $this->rateLimitInfo['reset_time'] = (int)trim(substr($header, 18));
                }
            }
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erro ao decodificar JSON do OpenAlex: " . json_last_error_msg());
            return null;
        }

        return $data;
    }

    /**
     * Busca trabalhos relacionados por área de conhecimento
     */
    public function getRelatedWorks(array $concepts, int $limit = 10): array
    {
        if (empty($concepts)) {
            return [];
        }

        // Pegar os conceitos mais relevantes
        $topConcepts = array_slice($concepts, 0, 3);
        $conceptIds = array_map(function($concept) {
            return str_replace('https://openalex.org/', '', $concept['id'] ?? '');
        }, $topConcepts);

        $conceptFilter = implode('|', $conceptIds);
        $separator = strpos($this->baseUrl, '?') ? '&' : '?';
        $url = $this->baseUrl . "/works{$separator}filter=concepts.id:{$conceptFilter}&per-page={$limit}&sort=cited_by_count:desc";

        $response = $this->makeRequest($url);
        return $response['results'] ?? [];
    }

    /**
     * Método legado para compatibilidade
     */
    public static function fetch($doi) {
        $fetcher = new self();
        return $fetcher->searchByDoi($doi);
    }
}
