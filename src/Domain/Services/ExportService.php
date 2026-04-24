<?php

class ExportService
{
    /**
     * Exporta dados em formato BibTeX
     */
    public function exportBibTeX(array $productions): string
    {
        $bibtex = "";
        
        foreach ($productions as $production) {
            $bibtex .= $this->formatBibTeXEntry($production) . "\n\n";
        }
        
        return $bibtex;
    }

    /**
     * Exporta dados em formato RIS
     */
    public function exportRIS(array $productions): string
    {
        $ris = "";
        
        foreach ($productions as $production) {
            $ris .= $this->formatRISEntry($production) . "\n";
        }
        
        return $ris;
    }

    /**
     * Exporta dados em formato CSV
     */
    public function exportCSV(array $productions): string
    {
        if (empty($productions)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // Cabeçalhos
        $headers = [
            'Título', 'Autor', 'Ano', 'Tipo', 'Subtipo', 'DOI', 'Revista/Veículo',
            'Volume', 'Páginas', 'Idioma', 'Instituição', 'Cidade', 'Estado',
            'País', 'ISBN', 'ISSN', 'Editora'
        ];
        
        fputcsv($output, $headers, ';');
        
        // Dados
        foreach ($productions as $production) {
            $row = [
                $production['title'] ?? '',
                $production['researcher_name'] ?? '',
                $production['year'] ?? '',
                $production['type'] ?? '',
                $production['subtype'] ?? '',
                $production['doi'] ?? '',
                $production['journal'] ?? $production['event_name'] ?? $production['book_title'] ?? '',
                $production['volume'] ?? '',
                $production['pages'] ?? '',
                $production['language'] ?? '',
                $production['institution'] ?? '',
                $production['city'] ?? '',
                $production['state'] ?? '',
                $production['country'] ?? '',
                $production['isbn'] ?? '',
                $production['issn'] ?? '',
                $production['publisher'] ?? ''
            ];
            
            fputcsv($output, $row, ';');
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Exporta dados em formato JSON
     */
    public function exportJSON(array $productions): string
    {
        return json_encode($productions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Exporta dados em formato XML
     */
    public function exportXML(array $productions): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><productions></productions>');
        
        foreach ($productions as $production) {
            $productionNode = $xml->addChild('production');
            $this->arrayToXML($production, $productionNode);
        }
        
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        return $dom->saveXML();
    }

    /**
     * Formata uma entrada BibTeX
     */
    private function formatBibTeXEntry(array $production): string
    {
        $type = $this->getBibTeXType($production['type'] ?? '');
        $key = $this->generateBibTeXKey($production);
        
        $entry = "@{$type}{{$key},\n";
        
        // Campos obrigatórios
        if (!empty($production['title'])) {
            $entry .= "  title = {" . $this->escapeBibTeX($production['title']) . "},\n";
        }
        
        if (!empty($production['researcher_name'])) {
            $entry .= "  author = {" . $this->escapeBibTeX($production['researcher_name']) . "},\n";
        }
        
        if (!empty($production['year'])) {
            $entry .= "  year = {" . $production['year'] . "},\n";
        }
        
        // Campos específicos por tipo
        switch ($production['type']) {
            case 'Artigo Publicado':
                if (!empty($production['journal'])) {
                    $entry .= "  journal = {" . $this->escapeBibTeX($production['journal']) . "},\n";
                }
                if (!empty($production['volume'])) {
                    $entry .= "  volume = {" . $production['volume'] . "},\n";
                }
                if (!empty($production['pages'])) {
                    $entry .= "  pages = {" . $production['pages'] . "},\n";
                }
                if (!empty($production['doi'])) {
                    $entry .= "  doi = {" . $production['doi'] . "},\n";
                }
                break;
                
            case 'Livro':
                if (!empty($production['publisher'])) {
                    $entry .= "  publisher = {" . $this->escapeBibTeX($production['publisher']) . "},\n";
                }
                if (!empty($production['city'])) {
                    $entry .= "  address = {" . $this->escapeBibTeX($production['city']) . "},\n";
                }
                if (!empty($production['isbn'])) {
                    $entry .= "  isbn = {" . $production['isbn'] . "},\n";
                }
                break;
                
            case 'Capítulo de Livro':
                if (!empty($production['book_title'])) {
                    $entry .= "  booktitle = {" . $this->escapeBibTeX($production['book_title']) . "},\n";
                }
                if (!empty($production['publisher'])) {
                    $entry .= "  publisher = {" . $this->escapeBibTeX($production['publisher']) . "},\n";
                }
                if (!empty($production['pages'])) {
                    $entry .= "  pages = {" . $production['pages'] . "},\n";
                }
                break;
        }
        
        $entry = rtrim($entry, ",\n") . "\n}";
        
        return $entry;
    }

    /**
     * Formata uma entrada RIS
     */
    private function formatRISEntry(array $production): string
    {
        $ris = "TY  - " . $this->getRISType($production['type'] ?? '') . "\n";
        
        if (!empty($production['title'])) {
            $ris .= "TI  - " . $production['title'] . "\n";
        }
        
        if (!empty($production['researcher_name'])) {
            $ris .= "AU  - " . $production['researcher_name'] . "\n";
        }
        
        if (!empty($production['year'])) {
            $ris .= "PY  - " . $production['year'] . "\n";
        }
        
        if (!empty($production['journal'])) {
            $ris .= "JO  - " . $production['journal'] . "\n";
        }
        
        if (!empty($production['volume'])) {
            $ris .= "VL  - " . $production['volume'] . "\n";
        }
        
        if (!empty($production['pages'])) {
            $ris .= "SP  - " . $production['pages'] . "\n";
        }
        
        if (!empty($production['doi'])) {
            $ris .= "DO  - " . $production['doi'] . "\n";
        }
        
        if (!empty($production['publisher'])) {
            $ris .= "PB  - " . $production['publisher'] . "\n";
        }
        
        $ris .= "ER  - \n";
        
        return $ris;
    }

    /**
     * Converte array para XML recursivamente
     */
    private function arrayToXML(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            $key = is_numeric($key) ? "item_{$key}" : $key;
            $key = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
            
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXML($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string)$value));
            }
        }
    }

    /**
     * Mapeia tipos para BibTeX
     */
    private function getBibTeXType(string $type): string
    {
        $mapping = [
            'Artigo Publicado' => 'article',
            'Livro' => 'book',
            'Capítulo de Livro' => 'incollection',
            'Trabalho em Evento' => 'inproceedings',
            'Texto em Jornal/Revista' => 'article',
            'Orientação' => 'mastersthesis',
            'Patente' => 'misc',
            'Produção Técnica' => 'misc'
        ];
        
        return $mapping[$type] ?? 'misc';
    }

    /**
     * Mapeia tipos para RIS
     */
    private function getRISType(string $type): string
    {
        $mapping = [
            'Artigo Publicado' => 'JOUR',
            'Livro' => 'BOOK',
            'Capítulo de Livro' => 'CHAP',
            'Trabalho em Evento' => 'CONF',
            'Texto em Jornal/Revista' => 'JOUR',
            'Orientação' => 'THES',
            'Patente' => 'PAT',
            'Produção Técnica' => 'COMP'
        ];
        
        return $mapping[$type] ?? 'GEN';
    }

    /**
     * Gera chave única para entrada BibTeX
     */
    private function generateBibTeXKey(array $production): string
    {
        $author = $production['researcher_name'] ?? 'Unknown';
        $year = $production['year'] ?? 'nodate';
        $title = $production['title'] ?? 'notitle';
        
        // Pegar primeira palavra do autor
        $authorParts = explode(' ', $author);
        $authorKey = strtolower(preg_replace('/[^a-zA-Z]/', '', $authorParts[0]));
        
        // Pegar primeira palavra significativa do título
        $titleWords = explode(' ', $title);
        $titleKey = '';
        foreach ($titleWords as $word) {
            if (strlen($word) > 3) {
                $titleKey = strtolower(preg_replace('/[^a-zA-Z]/', '', $word));
                break;
            }
        }
        
        return $authorKey . $year . $titleKey;
    }

    /**
     * Escapa caracteres especiais para BibTeX
     */
    private function escapeBibTeX(string $text): string
    {
        $replacements = [
            '&' => '\\&',
            '%' => '\\%',
            '$' => '\\$',
            '#' => '\\#',
            '^' => '\\^{}',
            '_' => '\\_',
            '{' => '\\{',
            '}' => '\\}',
            '~' => '\\textasciitilde{}',
            '\\' => '\\textbackslash{}'
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Prepara dados para exportação para ORCID
     */
    public function prepareForORCID(array $productions): array
    {
        $orcidWorks = [];
        
        foreach ($productions as $production) {
            $work = [
                'title' => [
                    'title' => [
                        'value' => $production['title'] ?? ''
                    ]
                ],
                'type' => $this->getORCIDType($production['type'] ?? ''),
                'external-ids' => [
                    'external-id' => []
                ]
            ];
            
            // Adicionar DOI se disponível
            if (!empty($production['doi'])) {
                $work['external-ids']['external-id'][] = [
                    'external-id-type' => 'doi',
                    'external-id-value' => $production['doi'],
                    'external-id-relationship' => 'self'
                ];
            }
            
            // Adicionar data de publicação
            if (!empty($production['year'])) {
                $work['publication-date'] = [
                    'year' => [
                        'value' => (string)$production['year']
                    ]
                ];
            }
            
            // Adicionar revista/jornal
            if (!empty($production['journal'])) {
                $work['journal-title'] = [
                    'value' => $production['journal']
                ];
            }
            
            $orcidWorks[] = $work;
        }
        
        return $orcidWorks;
    }

    /**
     * Mapeia tipos para ORCID
     */
    private function getORCIDType(string $type): string
    {
        $mapping = [
            'Artigo Publicado' => 'journal-article',
            'Livro' => 'book',
            'Capítulo de Livro' => 'book-chapter',
            'Trabalho em Evento' => 'conference-paper',
            'Texto em Jornal/Revista' => 'magazine-article',
            'Orientação' => 'supervised-student-publication',
            'Patente' => 'patent',
            'Produção Técnica' => 'other'
        ];
        
        return $mapping[$type] ?? 'other';
    }
}