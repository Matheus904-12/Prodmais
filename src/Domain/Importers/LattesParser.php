<?php

class LattesParser
{
    private $config;
    private $errors = [];
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    public function parseLattes($xmlFilePath): array
    {
        return $this->parse($xmlFilePath);
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    private function addError($error)
    {
        $this->errors[] = $error;
    }
    
    public function parse(string $xmlFilePath): array
    {
        if (!file_exists($xmlFilePath)) {
            $error = "Arquivo XML não encontrado: {$xmlFilePath}";
            $this->addError($error);
            throw new \Exception($error);
        }

        $xml = simplexml_load_file($xmlFilePath);
        if ($xml === false) {
            $error = "Erro ao carregar o arquivo XML.";
            $this->addError($error);
            throw new \Exception($error);
        }

        $productions = [];
        $researcherData = $this->extractResearcherData($xml);
        
        // Extrair diferentes tipos de produção
        $productions = array_merge($productions, $this->parseArtigos($xml, $researcherData));
        $productions = array_merge($productions, $this->parseLivros($xml, $researcherData));
        $productions = array_merge($productions, $this->parseCapitulos($xml, $researcherData));
        $productions = array_merge($productions, $this->parseTrabalhoAnais($xml, $researcherData));
        $productions = array_merge($productions, $this->parseTextoJornalMagazine($xml, $researcherData));
        $productions = array_merge($productions, $this->parseOrientacoes($xml, $researcherData));
        $productions = array_merge($productions, $this->parseProducaoTecnica($xml, $researcherData));
        $productions = array_merge($productions, $this->parsePatentes($xml, $researcherData));
        $productions = array_merge($productions, $this->parseEventos($xml, $researcherData));

        return $productions;
    }

    /**
     * Extrair projetos de pesquisa do currículo Lattes
     */
    public function parseProjects(string $xmlFilePath): array
    {
        if (!file_exists($xmlFilePath)) {
            throw new \Exception("Arquivo XML não encontrado: {$xmlFilePath}");
        }

        $xml = simplexml_load_file($xmlFilePath);
        if ($xml === false) {
            throw new \Exception("Erro ao carregar o arquivo XML.");
        }

        $projects = [];
        $researcherData = $this->extractResearcherData($xml);
        
        // Projetos de pesquisa
        if (isset($xml->{'DADOS-GERAIS'}->{'ATUACOES-PROFISSIONAIS'}->{'ATUACAO-PROFISSIONAL'})) {
            foreach ($xml->{'DADOS-GERAIS'}->{'ATUACOES-PROFISSIONAIS'}->{'ATUACAO-PROFISSIONAL'} as $atuacao) {
                if (isset($atuacao->{'ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO'}->{'PARTICIPACAO-EM-PROJETO'})) {
                    foreach ($atuacao->{'ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO'}->{'PARTICIPACAO-EM-PROJETO'} as $projeto) {
                        $projData = $projeto->{'PROJETO-DE-PESQUISA'};
                        $dadosBasicos = $projData->{'DADOS-BASICOS-DO-PROJETO-DE-PESQUISA'};
                        $detalhamento = $projData->{'DETALHAMENTO-DO-PROJETO-DE-PESQUISA'};
                        
                        $titulo = (string)$dadosBasicos->attributes()['NOME-DO-PROJETO'];
                        $anoInicio = (int)$dadosBasicos->attributes()['ANO-INICIO'];
                        
                        // Extrair equipe do projeto
                        $equipe = [];
                        if (isset($projData->{'EQUIPE-DO-PROJETO'}->{'INTEGRANTES-DO-PROJETO'})) {
                            foreach ($projData->{'EQUIPE-DO-PROJETO'}->{'INTEGRANTES-DO-PROJETO'} as $integrante) {
                                $equipe[] = [
                                    'nome' => (string)$integrante->attributes()['NOME-COMPLETO'],
                                    'funcao' => (string)$integrante->attributes()['FLAG-RESPONSAVEL']
                                ];
                            }
                        }
                        
                        $projects[] = [
                            'id' => 'projeto_' . md5($titulo . $anoInicio),
                            'titulo' => $titulo,
                            'ano_inicio' => $anoInicio,
                            'ano_fim' => (int)$dadosBasicos->attributes()['ANO-FIM'],
                            'situacao' => (string)$dadosBasicos->attributes()['SITUACAO'],
                            'natureza' => (string)$dadosBasicos->attributes()['NATUREZA'],
                            'descricao' => (string)$dadosBasicos->attributes()['DESCRICAO-DO-PROJETO'],
                            'financiamento' => (string)$detalhamento->attributes()['FINANCIADOR-DO-PROJETO'],
                            'coordenador' => $researcherData['name'],
                            'lattes_coordenador' => $researcherData['lattes_id'],
                            'instituicao' => (string)($atuacao->attributes()['NOME-INSTITUICAO'] ?? $researcherData['institution']),
                            'equipe' => $equipe,
                            'grande_area' => (string)$dadosBasicos->attributes()['GRANDE-AREA-CONHECIMENTO'],
                            'area' => (string)$dadosBasicos->attributes()['AREA-CONHECIMENTO'],
                            'sub_area' => (string)$dadosBasicos->attributes()['SUB-AREA-CONHECIMENTO'],
                            'source' => 'Lattes'
                        ];
                    }
                }
            }
        }
        
        return $projects;
    }

    private function extractResearcherData(\SimpleXMLElement $xml): array
    {
        $dadosGerais = $xml->{'DADOS-GERAIS'};
        $endereco = $dadosGerais->{'ENDERECO'};
        $enderecoProf = $endereco->{'ENDERECO-PROFISSIONAL'} ?? null;
        
        return [
            'name' => (string)$dadosGerais->attributes()['NOME-COMPLETO'],
            'lattes_id' => (string)$xml->attributes()['NUMERO-IDENTIFICADOR'],
            'cpf' => (string)$xml->attributes()['CPF'],
            'orcid' => (string)($dadosGerais->{'OUTRAS-INFORMACOES-RELEVANTES'}->attributes()['OUTRAS-INFORMACOES-RELEVANTES'] ?? ''),
            'institution' => (string)($enderecoProf->attributes()['NOME-INSTITUICAO-EMPRESA'] ?? ''),
            'unit' => (string)($enderecoProf->attributes()['NOME-ORGAO'] ?? ''),
            'city' => (string)($enderecoProf->attributes()['NOME-CIDADE'] ?? ''),
            'state' => (string)($enderecoProf->attributes()['UF'] ?? ''),
            'country' => (string)($enderecoProf->attributes()['PAIS'] ?? ''),
            'areas' => $this->extractAreas($dadosGerais)
        ];
    }

    private function extractAreas(\SimpleXMLElement $dadosGerais): array
    {
        $areas = [];
        if (isset($dadosGerais->{'AREAS-DE-ATUACAO'})) {
            foreach ($dadosGerais->{'AREAS-DE-ATUACAO'}->{'AREA-DE-ATUACAO'} as $area) {
                $areas[] = [
                    'grande_area' => (string)$area->attributes()['NOME-GRANDE-AREA-DO-CONHECIMENTO'],
                    'area' => (string)$area->attributes()['NOME-DA-AREA-DO-CONHECIMENTO'],
                    'sub_area' => (string)$area->attributes()['NOME-DA-SUB-AREA-DO-CONHECIMENTO'],
                    'especialidade' => (string)$area->attributes()['NOME-DA-ESPECIALIDADE']
                ];
            }
        }
        return $areas;
    }

    private function parseArtigos(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        if (isset($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'ARTIGOS-PUBLICADOS'})) {
            foreach ($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'ARTIGOS-PUBLICADOS'}->{'ARTIGO-PUBLICADO'} as $item) {
                $dadosBasicos = $item->{'DADOS-BASICOS-DO-ARTIGO'};
                $detalhamento = $item->{'DETALHAMENTO-DO-ARTIGO'};

                $title = (string)$dadosBasicos->attributes()['TITULO-DO-ARTIGO'];
                $year = (int)$dadosBasicos->attributes()['ANO-DO-ARTIGO'];
                $doi = (string)$dadosBasicos->attributes()['DOI'];
                $language = (string)$dadosBasicos->attributes()['IDIOMA'];
                
                $production = [
                    'id' => 'lattes_' . $researcherData['lattes_id'] . '_' . md5($title . $year),
                    'researcher_name' => $researcherData['name'],
                    'researcher_lattes_id' => $researcherData['lattes_id'],
                    'title' => $title,
                    'year' => $year,
                    'type' => 'Artigo Publicado',
                    'subtype' => (string)$dadosBasicos->attributes()['NATUREZA'],
                    'doi' => $doi,
                    'language' => $language,
                    'journal' => (string)$detalhamento->attributes()['TITULO-DO-PERIODICO-OU-REVISTA'],
                    'issn' => (string)$detalhamento->attributes()['ISSN'],
                    'volume' => (string)$detalhamento->attributes()['VOLUME'],
                    'pages' => (string)$detalhamento->attributes()['PAGINA-INICIAL'] . 
                              (($detalhamento->attributes()['PAGINA-FINAL']) ? '-' . (string)$detalhamento->attributes()['PAGINA-FINAL'] : ''),
                    'source' => 'Lattes',
                    'institution' => $researcherData['institution'],
                    'areas' => $researcherData['areas'],
                    'authors' => $this->extractAuthors($item)
                ];
                
                $productions[] = $production;
            }
        }
        return $productions;
    }

    private function parseLivros(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        if (isset($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'}->{'LIVROS-PUBLICADOS-OU-ORGANIZADOS'})) {
            foreach ($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'}->{'LIVROS-PUBLICADOS-OU-ORGANIZADOS'}->{'LIVRO-PUBLICADO-OU-ORGANIZADO'} as $item) {
                $dadosBasicos = $item->{'DADOS-BASICOS-DO-LIVRO'};
                $detalhamento = $item->{'DETALHAMENTO-DO-LIVRO'};

                $title = (string)$dadosBasicos->attributes()['TITULO-DO-LIVRO'];
                $year = (int)$dadosBasicos->attributes()['ANO'];
                
                $production = [
                    'id' => 'lattes_' . $researcherData['lattes_id'] . '_livro_' . md5($title . $year),
                    'researcher_name' => $researcherData['name'],
                    'researcher_lattes_id' => $researcherData['lattes_id'],
                    'title' => $title,
                    'year' => $year,
                    'type' => 'Livro',
                    'subtype' => (string)$dadosBasicos->attributes()['TIPO'],
                    'language' => (string)$dadosBasicos->attributes()['IDIOMA'],
                    'publisher' => (string)$detalhamento->attributes()['NOME-DA-EDITORA'],
                    'city' => (string)$detalhamento->attributes()['CIDADE-DA-EDITORA'],
                    'isbn' => (string)$detalhamento->attributes()['ISBN'],
                    'pages' => (string)$detalhamento->attributes()['NUMERO-DE-PAGINAS'],
                    'source' => 'Lattes',
                    'institution' => $researcherData['institution'],
                    'areas' => $researcherData['areas'],
                    'authors' => $this->extractAuthors($item)
                ];
                
                $productions[] = $production;
            }
        }
        return $productions;
    }

    private function parseCapitulos(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        if (isset($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'}->{'CAPITULOS-DE-LIVROS-PUBLICADOS'})) {
            foreach ($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'}->{'CAPITULOS-DE-LIVROS-PUBLICADOS'}->{'CAPITULO-DE-LIVRO-PUBLICADO'} as $item) {
                $dadosBasicos = $item->{'DADOS-BASICOS-DO-CAPITULO'};
                $detalhamento = $item->{'DETALHAMENTO-DO-CAPITULO'};

                $title = (string)$dadosBasicos->attributes()['TITULO-DO-CAPITULO-DO-LIVRO'];
                $year = (int)$dadosBasicos->attributes()['ANO'];
                
                $production = [
                    'id' => 'lattes_' . $researcherData['lattes_id'] . '_capitulo_' . md5($title . $year),
                    'researcher_name' => $researcherData['name'],
                    'researcher_lattes_id' => $researcherData['lattes_id'],
                    'title' => $title,
                    'year' => $year,
                    'type' => 'Capítulo de Livro',
                    'subtype' => (string)$dadosBasicos->attributes()['TIPO'],
                    'language' => (string)$dadosBasicos->attributes()['IDIOMA'],
                    'book_title' => (string)$detalhamento->attributes()['TITULO-DO-LIVRO'],
                    'publisher' => (string)$detalhamento->attributes()['NOME-DA-EDITORA'],
                    'city' => (string)$detalhamento->attributes()['CIDADE-DA-EDITORA'],
                    'isbn' => (string)$detalhamento->attributes()['ISBN'],
                    'pages' => (string)$detalhamento->attributes()['PAGINA-INICIAL'] . 
                              (($detalhamento->attributes()['PAGINA-FINAL']) ? '-' . (string)$detalhamento->attributes()['PAGINA-FINAL'] : ''),
                    'source' => 'Lattes',
                    'institution' => $researcherData['institution'],
                    'areas' => $researcherData['areas'],
                    'authors' => $this->extractAuthors($item)
                ];
                
                $productions[] = $production;
            }
        }
        return $productions;
    }

    private function parseTrabalhoAnais(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        if (isset($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'TRABALHOS-EM-EVENTOS'})) {
            foreach ($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'TRABALHOS-EM-EVENTOS'}->{'TRABALHO-EM-EVENTOS'} as $item) {
                $dadosBasicos = $item->{'DADOS-BASICOS-DO-TRABALHO'};
                $detalhamento = $item->{'DETALHAMENTO-DO-TRABALHO'};

                $title = (string)$dadosBasicos->attributes()['TITULO-DO-TRABALHO'];
                $year = (int)$dadosBasicos->attributes()['ANO-DO-TRABALHO'];
                
                $production = [
                    'id' => 'lattes_' . $researcherData['lattes_id'] . '_evento_' . md5($title . $year),
                    'researcher_name' => $researcherData['name'],
                    'researcher_lattes_id' => $researcherData['lattes_id'],
                    'title' => $title,
                    'year' => $year,
                    'type' => 'Trabalho em Evento',
                    'subtype' => (string)$dadosBasicos->attributes()['NATUREZA'],
                    'language' => (string)$dadosBasicos->attributes()['IDIOMA'],
                    'event_name' => (string)$detalhamento->attributes()['NOME-DO-EVENTO'],
                    'event_city' => (string)$detalhamento->attributes()['CIDADE-DO-EVENTO'],
                    'event_year' => (string)$detalhamento->attributes()['ANO-DE-REALIZACAO'],
                    'proceedings_title' => (string)$detalhamento->attributes()['TITULO-DOS-ANAIS-OU-PROCEEDINGS'],
                    'pages' => (string)$detalhamento->attributes()['PAGINA-INICIAL'] . 
                              (($detalhamento->attributes()['PAGINA-FINAL']) ? '-' . (string)$detalhamento->attributes()['PAGINA-FINAL'] : ''),
                    'isbn' => (string)$detalhamento->attributes()['ISBN'],
                    'source' => 'Lattes',
                    'institution' => $researcherData['institution'],
                    'areas' => $researcherData['areas'],
                    'authors' => $this->extractAuthors($item)
                ];
                
                $productions[] = $production;
            }
        }
        return $productions;
    }

    private function parseTextoJornalMagazine(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        if (isset($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'TEXTOS-EM-JORNAIS-OU-REVISTAS'})) {
            foreach ($xml->{'PRODUCAO-BIBLIOGRAFICA'}->{'TEXTOS-EM-JORNAIS-OU-REVISTAS'}->{'TEXTO-EM-JORNAL-OU-REVISTA'} as $item) {
                $dadosBasicos = $item->{'DADOS-BASICOS-DO-TEXTO'};
                $detalhamento = $item->{'DETALHAMENTO-DO-TEXTO'};

                $title = (string)$dadosBasicos->attributes()['TITULO-DO-TEXTO'];
                $year = (int)$dadosBasicos->attributes()['ANO-DO-TEXTO'];
                
                $production = [
                    'id' => 'lattes_' . $researcherData['lattes_id'] . '_texto_' . md5($title . $year),
                    'researcher_name' => $researcherData['name'],
                    'researcher_lattes_id' => $researcherData['lattes_id'],
                    'title' => $title,
                    'year' => $year,
                    'type' => 'Texto em Jornal/Revista',
                    'subtype' => (string)$dadosBasicos->attributes()['NATUREZA'],
                    'language' => (string)$dadosBasicos->attributes()['IDIOMA'],
                    'journal' => (string)$detalhamento->attributes()['TITULO-DO-JORNAL-OU-REVISTA'],
                    'date' => (string)$detalhamento->attributes()['DATA-DE-PUBLICACAO'],
                    'volume' => (string)$detalhamento->attributes()['VOLUME'],
                    'pages' => (string)$detalhamento->attributes()['PAGINA-INICIAL'] . 
                              (($detalhamento->attributes()['PAGINA-FINAL']) ? '-' . (string)$detalhamento->attributes()['PAGINA-FINAL'] : ''),
                    'source' => 'Lattes',
                    'institution' => $researcherData['institution'],
                    'areas' => $researcherData['areas'],
                    'authors' => $this->extractAuthors($item)
                ];
                
                $productions[] = $production;
            }
        }
        return $productions;
    }

    private function parseOrientacoes(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        
        // Orientações concluídas
        if (isset($xml->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-CONCLUIDAS'})) {
            $orientacoesConcluidas = $xml->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-CONCLUIDAS'};
            
            // Doutorado
            if (isset($orientacoesConcluidas->{'ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO'})) {
                foreach ($orientacoesConcluidas->{'ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO'}->{'ORIENTACAO-CONCLUIDA-PARA-DOUTORADO'} as $item) {
                    $dadosBasicos = $item->{'DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO'};
                    $detalhamento = $item->{'DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO'};
                    
                    $title = (string)$dadosBasicos->attributes()['TITULO'];
                    $year = (int)$dadosBasicos->attributes()['ANO'];
                    
                    $productions[] = [
                        'id' => 'lattes_' . $researcherData['lattes_id'] . '_orient_dout_' . md5($title . $year),
                        'researcher_name' => $researcherData['name'],
                        'researcher_lattes_id' => $researcherData['lattes_id'],
                        'title' => $title,
                        'year' => $year,
                        'type' => 'Orientação',
                        'subtype' => 'Doutorado',
                        'student_name' => (string)$detalhamento->attributes()['NOME-DO-ORIENTADO'],
                        'institution' => (string)$detalhamento->attributes()['NOME-DA-INSTITUICAO'],
                        'course' => (string)$detalhamento->attributes()['NOME-CURSO'],
                        'source' => 'Lattes',
                        'areas' => $researcherData['areas']
                    ];
                }
            }
            
            // Mestrado
            if (isset($orientacoesConcluidas->{'ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'})) {
                foreach ($orientacoesConcluidas->{'ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'}->{'ORIENTACAO-CONCLUIDA-PARA-MESTRADO'} as $item) {
                    $dadosBasicos = $item->{'DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'};
                    $detalhamento = $item->{'DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'};
                    
                    $title = (string)$dadosBasicos->attributes()['TITULO'];
                    $year = (int)$dadosBasicos->attributes()['ANO'];
                    
                    $productions[] = [
                        'id' => 'lattes_' . $researcherData['lattes_id'] . '_orient_mest_' . md5($title . $year),
                        'researcher_name' => $researcherData['name'],
                        'researcher_lattes_id' => $researcherData['lattes_id'],
                        'title' => $title,
                        'year' => $year,
                        'type' => 'Orientação',
                        'subtype' => 'Mestrado',
                        'student_name' => (string)$detalhamento->attributes()['NOME-DO-ORIENTADO'],
                        'institution' => (string)$detalhamento->attributes()['NOME-DA-INSTITUICAO'],
                        'course' => (string)$detalhamento->attributes()['NOME-CURSO'],
                        'source' => 'Lattes',
                        'areas' => $researcherData['areas']
                    ];
                }
            }
        }
        
        return $productions;
    }

    private function parseProducaoTecnica(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        if (isset($xml->{'PRODUCAO-TECNICA'}->{'SOFTWARE'})) {
            foreach ($xml->{'PRODUCAO-TECNICA'}->{'SOFTWARE'}->{'SOFTWARE'} as $item) {
                $dadosBasicos = $item->{'DADOS-BASICOS-DO-SOFTWARE'};
                $detalhamento = $item->{'DETALHAMENTO-DO-SOFTWARE'};

                $title = (string)$dadosBasicos->attributes()['TITULO-DO-SOFTWARE'];
                $year = (int)$dadosBasicos->attributes()['ANO'];
                
                $productions[] = [
                    'id' => 'lattes_' . $researcherData['lattes_id'] . '_software_' . md5($title . $year),
                    'researcher_name' => $researcherData['name'],
                    'researcher_lattes_id' => $researcherData['lattes_id'],
                    'title' => $title,
                    'year' => $year,
                    'type' => 'Produção Técnica',
                    'subtype' => 'Software',
                    'language' => (string)$dadosBasicos->attributes()['IDIOMA'],
                    'purpose' => (string)$detalhamento->attributes()['FINALIDADE'],
                    'platform' => (string)$detalhamento->attributes()['PLATAFORMA'],
                    'source' => 'Lattes',
                    'institution' => $researcherData['institution'],
                    'areas' => $researcherData['areas'],
                    'authors' => $this->extractAuthors($item)
                ];
            }
        }
        return $productions;
    }

    private function parsePatentes(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        if (isset($xml->{'PRODUCAO-TECNICA'}->{'PATENTE'})) {
            foreach ($xml->{'PRODUCAO-TECNICA'}->{'PATENTE'}->{'PATENTE'} as $item) {
                $dadosBasicos = $item->{'DADOS-BASICOS-DA-PATENTE'};
                $detalhamento = $item->{'DETALHAMENTO-DA-PATENTE'};

                $title = (string)$dadosBasicos->attributes()['TITULO'];
                $year = (int)$dadosBasicos->attributes()['ANO-DESENVOLVIMENTO'];
                
                $productions[] = [
                    'id' => 'lattes_' . $researcherData['lattes_id'] . '_patente_' . md5($title . $year),
                    'researcher_name' => $researcherData['name'],
                    'researcher_lattes_id' => $researcherData['lattes_id'],
                    'title' => $title,
                    'year' => $year,
                    'type' => 'Patente',
                    'country' => (string)$dadosBasicos->attributes()['PAIS'],
                    'patent_number' => (string)$detalhamento->attributes()['CODIGO-DO-REGISTRO-OU-PATENTE'],
                    'institution' => (string)$detalhamento->attributes()['INSTITUICAO-FINANCIADORA'],
                    'source' => 'Lattes',
                    'areas' => $researcherData['areas'],
                    'authors' => $this->extractAuthors($item)
                ];
            }
        }
        return $productions;
    }

    private function parseEventos(\SimpleXMLElement $xml, array $researcherData): array
    {
        $productions = [];
        if (isset($xml->{'DADOS-COMPLEMENTARES'}->{'PARTICIPACAO-EM-EVENTOS-CONGRESSOS'})) {
            foreach ($xml->{'DADOS-COMPLEMENTARES'}->{'PARTICIPACAO-EM-EVENTOS-CONGRESSOS'}->{'PARTICIPACAO-EM-CONGRESSO'} as $item) {
                $dadosBasicos = $item->{'DADOS-BASICOS-DA-PARTICIPACAO-EM-CONGRESSO'};
                $detalhamento = $item->{'DETALHAMENTO-DA-PARTICIPACAO-EM-CONGRESSO'};

                $title = (string)$dadosBasicos->attributes()['TITULO-DO-TRABALHO'];
                $year = (int)$dadosBasicos->attributes()['ANO'];
                
                $productions[] = [
                    'id' => 'lattes_' . $researcherData['lattes_id'] . '_participacao_' . md5($title . $year),
                    'researcher_name' => $researcherData['name'],
                    'researcher_lattes_id' => $researcherData['lattes_id'],
                    'title' => $title,
                    'year' => $year,
                    'type' => 'Participação em Evento',
                    'subtype' => (string)$dadosBasicos->attributes()['NATUREZA'],
                    'event_name' => (string)$detalhamento->attributes()['NOME-DO-EVENTO'],
                    'event_city' => (string)$detalhamento->attributes()['CIDADE-DO-EVENTO'],
                    'source' => 'Lattes',
                    'institution' => $researcherData['institution'],
                    'areas' => $researcherData['areas']
                ];
            }
        }
        return $productions;
    }

    private function extractAuthors(\SimpleXMLElement $item): array
    {
        $authors = [];
        if (isset($item->{'AUTORES'})) {
            foreach ($item->{'AUTORES'} as $autor) {
                $authors[] = [
                    'name' => (string)$autor->attributes()['NOME-COMPLETO-DO-AUTOR'],
                    'citation_name' => (string)$autor->attributes()['NOME-PARA-CITACAO'],
                    'order' => (int)$autor->attributes()['ORDEM-DE-AUTORIA']
                ];
            }
        }
        return $authors;
    }
}
