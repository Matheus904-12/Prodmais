<?php

class OrcidFetcher
{
    private string $baseUrl = 'https://pub.orcid.org/v3.0';
    private array $headers;
    private $config;

    public function __construct($config = null)
    {
        $this->config = $config;
        $this->headers = [
            'Accept: application/json',
            'User-Agent: Prodmais/1.0 (https://github.com/unifesp/prodmais)',
            'Content-Type: application/json'
        ];
    }

    /**
     * Busca o perfil completo de um pesquisador
     */
    public function getProfile(string $orcid): ?array
    {
        $orcid = $this->normalizeOrcid($orcid);
        $url = "{$this->baseUrl}/{$orcid}";
        
        $response = $this->makeRequest($url);
        if (!$response) {
            return null;
        }

        return $this->normalizeProfileData($response);
    }

    /**
     * Busca as obras/publicações de um pesquisador
     */
    public function getWorks(string $orcid): array
    {
        $orcid = $this->normalizeOrcid($orcid);
        $url = "{$this->baseUrl}/{$orcid}/works";
        
        $response = $this->makeRequest($url);
        if (!$response || empty($response['group'])) {
            return [];
        }

        $works = [];
        foreach ($response['group'] as $group) {
            foreach ($group['work-summary'] as $workSummary) {
                $putCode = $workSummary['put-code'];
                $workDetail = $this->getWorkDetail($orcid, $putCode);
                if ($workDetail) {
                    $works[] = $this->normalizeWorkData($workDetail);
                }
            }
        }

        return $works;
    }

    /**
     * Busca detalhes de uma obra específica
     */
    public function getWorkDetail(string $orcid, int $putCode): ?array
    {
        $orcid = $this->normalizeOrcid($orcid);
        $url = "{$this->baseUrl}/{$orcid}/work/{$putCode}";
        
        return $this->makeRequest($url);
    }

    /**
     * Busca informações de educação/formação
     */
    public function getEducation(string $orcid): array
    {
        $orcid = $this->normalizeOrcid($orcid);
        $url = "{$this->baseUrl}/{$orcid}/educations";
        
        $response = $this->makeRequest($url);
        if (!$response || empty($response['education-summary'])) {
            return [];
        }

        $educations = [];
        foreach ($response['education-summary'] as $summary) {
            $putCode = $summary['put-code'];
            $detail = $this->getEducationDetail($orcid, $putCode);
            if ($detail) {
                $educations[] = $this->normalizeEducationData($detail);
            }
        }

        return $educations;
    }

    /**
     * Busca detalhes de formação específica
     */
    public function getEducationDetail(string $orcid, int $putCode): ?array
    {
        $orcid = $this->normalizeOrcid($orcid);
        $url = "{$this->baseUrl}/{$orcid}/education/{$putCode}";
        
        return $this->makeRequest($url);
    }

    /**
     * Busca informações de emprego
     */
    public function getEmployments(string $orcid): array
    {
        $orcid = $this->normalizeOrcid($orcid);
        $url = "{$this->baseUrl}/{$orcid}/employments";
        
        $response = $this->makeRequest($url);
        if (!$response || empty($response['employment-summary'])) {
            return [];
        }

        $employments = [];
        foreach ($response['employment-summary'] as $summary) {
            $putCode = $summary['put-code'];
            $detail = $this->getEmploymentDetail($orcid, $putCode);
            if ($detail) {
                $employments[] = $this->normalizeEmploymentData($detail);
            }
        }

        return $employments;
    }

    /**
     * Busca detalhes de emprego específico
     */
    public function getEmploymentDetail(string $orcid, int $putCode): ?array
    {
        $orcid = $this->normalizeOrcid($orcid);
        $url = "{$this->baseUrl}/{$orcid}/employment/{$putCode}";
        
        return $this->makeRequest($url);
    }

    /**
     * Busca dados completos do pesquisador (perfil + obras + educação + emprego)
     */
    public function getCompleteProfile(string $orcid): array
    {
        $profile = $this->getProfile($orcid);
        if (!$profile) {
            return [];
        }

        return [
            'profile' => $profile,
            'works' => $this->getWorks($orcid),
            'education' => $this->getEducation($orcid),
            'employments' => $this->getEmployments($orcid)
        ];
    }

    /**
     * Normaliza dados do perfil
     */
    private function normalizeProfileData(array $data): array
    {
        $person = $data['person'] ?? [];
        $name = $person['name'] ?? [];
        
        return [
            'orcid' => $data['orcid-identifier']['path'] ?? null,
            'given_name' => $name['given-names']['value'] ?? null,
            'family_name' => $name['family-name']['value'] ?? null,
            'credit_name' => $name['credit-name']['value'] ?? null,
            'other_names' => $this->extractOtherNames($person['other-names'] ?? []),
            'biography' => $person['biography']['content'] ?? null,
            'keywords' => $this->extractKeywords($person['keywords'] ?? []),
            'researcher_urls' => $this->extractResearcherUrls($person['researcher-urls'] ?? []),
            'external_identifiers' => $this->extractExternalIdentifiers($person['external-identifiers'] ?? [])
        ];
    }

    /**
     * Normaliza dados de uma obra
     */
    private function normalizeWorkData(array $data): array
    {
        $title = $data['title']['title']['value'] ?? 'Título não disponível';
        $journal = $data['journal-title']['value'] ?? null;
        $year = null;
        
        if (!empty($data['publication-date'])) {
            $pubDate = $data['publication-date'];
            $year = (int)($pubDate['year']['value'] ?? 0);
        }

        $normalized = [
            'title' => $title,
            'type' => $data['type'] ?? 'unknown',
            'journal' => $journal,
            'year' => $year,
            'url' => $data['url']['value'] ?? null,
            'external_ids' => $this->extractExternalIds($data['external-ids'] ?? []),
            'contributors' => $this->extractContributors($data['contributors'] ?? []),
            'source' => 'ORCID'
        ];

        // Extrair DOI se disponível
        foreach ($normalized['external_ids'] as $extId) {
            if ($extId['type'] === 'doi') {
                $normalized['doi'] = $extId['value'];
                break;
            }
        }

        return $normalized;
    }

    /**
     * Normaliza dados de educação
     */
    private function normalizeEducationData(array $data): array
    {
        $org = $data['organization'] ?? [];
        $startDate = $data['start-date'] ?? [];
        $endDate = $data['end-date'] ?? [];
        
        return [
            'institution' => $org['name'] ?? null,
            'department' => $data['department-name'] ?? null,
            'degree' => $data['role-title'] ?? null,
            'start_year' => $startDate['year']['value'] ?? null,
            'end_year' => $endDate['year']['value'] ?? null,
            'city' => $org['address']['city'] ?? null,
            'country' => $org['address']['country'] ?? null
        ];
    }

    /**
     * Normaliza dados de emprego
     */
    private function normalizeEmploymentData(array $data): array
    {
        $org = $data['organization'] ?? [];
        $startDate = $data['start-date'] ?? [];
        $endDate = $data['end-date'] ?? [];
        
        return [
            'institution' => $org['name'] ?? null,
            'department' => $data['department-name'] ?? null,
            'role' => $data['role-title'] ?? null,
            'start_year' => $startDate['year']['value'] ?? null,
            'end_year' => $endDate['year']['value'] ?? null,
            'city' => $org['address']['city'] ?? null,
            'country' => $org['address']['country'] ?? null
        ];
    }

    /**
     * Extrai outros nomes
     */
    private function extractOtherNames(array $otherNames): array
    {
        $names = [];
        foreach ($otherNames['other-name'] ?? [] as $name) {
            $names[] = $name['content'] ?? '';
        }
        return array_filter($names);
    }

    /**
     * Extrai palavras-chave
     */
    private function extractKeywords(array $keywords): array
    {
        $keywordList = [];
        foreach ($keywords['keyword'] ?? [] as $keyword) {
            $keywordList[] = $keyword['content'] ?? '';
        }
        return array_filter($keywordList);
    }

    /**
     * Extrai URLs do pesquisador
     */
    private function extractResearcherUrls(array $urls): array
    {
        $urlList = [];
        foreach ($urls['researcher-url'] ?? [] as $url) {
            $urlList[] = [
                'name' => $url['url-name'] ?? '',
                'url' => $url['url']['value'] ?? ''
            ];
        }
        return $urlList;
    }

    /**
     * Extrai identificadores externos
     */
    private function extractExternalIdentifiers(array $identifiers): array
    {
        $idList = [];
        foreach ($identifiers['external-identifier'] ?? [] as $id) {
            $idList[] = [
                'type' => $id['external-id-type'] ?? '',
                'value' => $id['external-id-value'] ?? ''
            ];
        }
        return $idList;
    }

    /**
     * Extrai IDs externos de uma obra
     */
    private function extractExternalIds(array $externalIds): array
    {
        $ids = [];
        foreach ($externalIds['external-id'] ?? [] as $id) {
            $ids[] = [
                'type' => $id['external-id-type'] ?? '',
                'value' => $id['external-id-value'] ?? ''
            ];
        }
        return $ids;
    }

    /**
     * Extrai contribuidores
     */
    private function extractContributors(array $contributors): array
    {
        $contribList = [];
        foreach ($contributors['contributor'] ?? [] as $contrib) {
            $name = $contrib['credit-name'] ?? [];
            $contribList[] = [
                'name' => $name['value'] ?? '',
                'role' => $contrib['contributor-attributes']['contributor-role'] ?? ''
            ];
        }
        return $contribList;
    }

    /**
     * Normaliza ORCID ID para formato padrão
     */
    private function normalizeOrcid(string $orcid): string
    {
        // Remove qualquer URL base e mantém apenas o ID
        $orcid = str_replace(['https://orcid.org/', 'http://orcid.org/'], '', $orcid);
        
        // Remove espaços e outros caracteres não relevantes
        $orcid = trim($orcid);
        
        // Valida formato básico
        if (!preg_match('/^\d{4}-\d{4}-\d{4}-(\d{3}X|\d{4})$/', $orcid)) {
            throw new \InvalidArgumentException("ORCID ID inválido: {$orcid}");
        }
        
        return $orcid;
    }

    /**
     * Faz requisição HTTP para a API do ORCID
     */
    private function makeRequest(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $this->headers),
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            error_log("Erro ao fazer requisição para ORCID: {$url}");
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erro ao decodificar JSON do ORCID: " . json_last_error_msg());
            return null;
        }

        return $data;
    }

    /**
     * Método legado para compatibilidade
     */
    public static function fetch($orcid) {
        $fetcher = new self();
        return $fetcher->getWorks($orcid);
    }
}
