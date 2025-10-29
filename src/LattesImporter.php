<?php
/**
 * PRODMAIS UMC - Importador de Currículos Lattes XML
 * Sistema robusto para processar currículos extensos
 * Baseado em Prodmais UNIFESP + adaptações UMC
 */

namespace ProdmaisUMC;

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/UmcFunctions.php';

class LattesImporter {
    
    private $client;
    private $index_cv;
    private $index;
    private $index_projetos;
    private $ppg_default = null;
    private $campus_default = 'Mogi das Cruzes';
    
    // Configurações de memória para currículos extensos
    private $max_execution_time = 600; // 10 minutos
    private $memory_limit = '512M';
    
    public function __construct() {
        global $index_cv, $index, $index_projetos;
        
        $this->client = getElasticsearchClient();
        $this->index_cv = $index_cv;
        $this->index = $index;
        $this->index_projetos = $index_projetos;
        
        // Configurar limites para currículos extensos
        set_time_limit($this->max_execution_time);
        ini_set('memory_limit', $this->memory_limit);
    }
    
    /**
     * Importar currículo Lattes de arquivo XML
     */
    public function importFromXML($xml_file_path, $ppg_nome = null, $area_concentracao = null) {
        if (!file_exists($xml_file_path)) {
            throw new \Exception("Arquivo XML não encontrado: $xml_file_path");
        }
        
        echo "📄 Carregando arquivo XML...\n";
        
        // Usar XMLReader para arquivos grandes (não carrega tudo na memória)
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($xml_file_path, 'SimpleXMLElement', LIBXML_PARSEHUGE);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new \Exception("Erro ao parsear XML: " . implode(", ", array_map(function($e) { return $e->message; }, $errors)));
        }
        
        echo "✅ XML carregado com sucesso!\n";
        
        // Extrair dados do pesquisador
        $pesquisador = $this->extractPesquisadorData($xml, $ppg_nome, $area_concentracao);
        
        echo "👤 Pesquisador: {$pesquisador['nome_completo']}\n";
        echo "📊 Processando produções...\n";
        
        // Extrair produções
        $producoes = $this->extractProducoes($xml, $pesquisador['lattesID']);
        
        echo "✅ {$producoes['total']} produções extraídas\n";
        echo "   - Artigos: {$producoes['artigos']}\n";
        echo "   - Livros: {$producoes['livros']}\n";
        echo "   - Capítulos: {$producoes['capitulos']}\n";
        echo "   - Eventos: {$producoes['eventos']}\n";
        
        // Extrair projetos
        echo "🔬 Processando projetos de pesquisa...\n";
        $projetos = $this->extractProjetos($xml, $pesquisador['lattesID'], $ppg_nome);
        
        echo "✅ {$projetos['total']} projetos extraídos\n";
        
        // Atualizar contadores no perfil do pesquisador
        $pesquisador['total_producoes'] = $producoes['total'];
        $pesquisador['total_projetos'] = $projetos['total'];
        
        // Indexar no Elasticsearch
        echo "💾 Indexando no Elasticsearch...\n";
        
        $result = [
            'pesquisador_nome' => $pesquisador['nome_completo'],
            'lattesID' => $pesquisador['lattesID'],
            'foto_url' => $pesquisador['foto_url'],
            'pesquisador' => $this->indexPesquisador($pesquisador),
            'producoes' => $this->indexProducoes($producoes['items'], $pesquisador),
            'projetos' => $this->indexProjetos($projetos['items'], $pesquisador),
            'total_producoes' => $producoes['total'],
            'total_projetos' => $projetos['total'],
            'artigos' => $producoes['artigos'],
            'livros' => $producoes['livros'],
            'capitulos' => $producoes['capitulos'],
            'eventos' => $producoes['eventos']
        ];
        
        echo "🎉 Importação concluída com sucesso!\n";
        
        return $result;
    }
    
    /**
     * Extrair dados do pesquisador
     */
    private function extractPesquisadorData($xml, $ppg_nome, $area_concentracao) {
        $dados_gerais = $xml->{'DADOS-GERAIS'};
        
        // Extrair foto do perfil (se disponível)
        $foto_url = '';
        if (isset($dados_gerais['FOTO'])) {
            $foto_url = (string)$dados_gerais['FOTO'];
        }
        
        // Se não tiver foto no XML, tentar usar a URL pública do Lattes
        $lattesID = (string)$xml['NUMERO-IDENTIFICADOR'];
        if (empty($foto_url)) {
            // URL padrão da foto no Lattes (pode ou não estar disponível publicamente)
            $foto_url = "http://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&id={$lattesID}";
        }
        
        $pesquisador = [
            'nome_completo' => (string)$dados_gerais['NOME-COMPLETO'],
            'nome_citacao' => (string)$dados_gerais['NOME-EM-CITACOES-BIBLIOGRAFICAS'],
            'lattesID' => $lattesID,
            'data_atualizacao' => (string)$xml['DATA-ATUALIZACAO'],
            'orcidID' => '',
            'email' => '',
            'instituicao' => 'Universidade de Mogi das Cruzes',
            'ppg' => $ppg_nome,
            'area_concentracao' => $area_concentracao,
            'campus' => $this->campus_default,
            'foto_url' => $foto_url,
            'resumo_cv' => [],
            'total_producoes' => 0,  // Será atualizado depois
            'total_projetos' => 0    // Será atualizado depois
        ];
        
        // Extrair ORCID (se disponível)
        if (isset($dados_gerais['ORCID-ID'])) {
            $pesquisador['orcidID'] = (string)$dados_gerais['ORCID-ID'];
        }
        
        // Extrair resumo CV
        if (isset($xml->{'DADOS-GERAIS'}->{'RESUMO-CV'})) {
            $resumo = $xml->{'DADOS-GERAIS'}->{'RESUMO-CV'};
            $pesquisador['resumo_cv'] = [
                'texto_resumo_cv_rh' => (string)($resumo['TEXTO-RESUMO-CV-RH'] ?? ''),
                'texto_resumo_cv_rh_en' => (string)($resumo['TEXTO-RESUMO-CV-RH-EN'] ?? '')
            ];
        }
        
        // Extrair áreas de atuação
        $areas = [];
        if (isset($xml->{'DADOS-GERAIS'}->{'AREAS-DE-ATUACAO'})) {
            foreach ($xml->{'DADOS-GERAIS'}->{'AREAS-DE-ATUACAO'}->children() as $area) {
                $areas[] = [
                    'grande_area' => (string)($area['NOME-GRANDE-AREA-DO-CONHECIMENTO'] ?? ''),
                    'area' => (string)($area['NOME-DA-AREA-DO-CONHECIMENTO'] ?? ''),
                    'subarea' => (string)($area['NOME-DA-SUB-AREA-DO-CONHECIMENTO'] ?? '')
                ];
            }
        }
        $pesquisador['areas_atuacao'] = $areas;
        
        // Extrair formação acadêmica
        $formacao = [];
        if (isset($xml->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'})) {
            foreach ($xml->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->children() as $titulo) {
                $formacao[] = [
                    'nivel' => $titulo->getName(),
                    'titulo' => (string)($titulo['TITULO-DA-DISSERTACAO-TESE'] ?? ''),
                    'ano' => (string)($titulo['ANO-DE-CONCLUSAO'] ?? ''),
                    'instituicao' => (string)($titulo['NOME-INSTITUICAO'] ?? '')
                ];
            }
        }
        $pesquisador['formacao'] = $formacao;
        
        return $pesquisador;
    }
    
    /**
     * Extrair produções bibliográficas
     */
    private function extractProducoes($xml, $lattesID) {
        $producoes = [
            'items' => [],
            'total' => 0,
            'artigos' => 0,
            'livros' => 0,
            'capitulos' => 0,
            'eventos' => 0
        ];
        
        if (!isset($xml->{'PRODUCAO-BIBLIOGRAFICA'})) {
            return $producoes;
        }
        
        $prod_bib = $xml->{'PRODUCAO-BIBLIOGRAFICA'};
        
        // ARTIGOS PUBLICADOS
        if (isset($prod_bib->{'ARTIGOS-PUBLICADOS'})) {
            foreach ($prod_bib->{'ARTIGOS-PUBLICADOS'}->{'ARTIGO-PUBLICADO'} as $artigo) {
                $dados_basicos = $artigo->{'DADOS-BASICOS-DO-ARTIGO'};
                $detalhamento = $artigo->{'DETALHAMENTO-DO-ARTIGO'};
                
                $autores = [];
                if (isset($artigo->{'AUTORES'})) {
                    foreach ($artigo->{'AUTORES'} as $autor) {
                        $autores[] = (string)$autor['NOME-COMPLETO-DO-AUTOR'];
                    }
                }
                
                $producoes['items'][] = [
                    'tipo' => 'PERIODICO',
                    'natureza' => 'ARTIGO',
                    'titulo' => (string)$dados_basicos['TITULO-DO-ARTIGO'],
                    'ano' => (int)$dados_basicos['ANO-DO-ARTIGO'],
                    'autores' => implode('; ', $autores),
                    'periodico' => (string)($detalhamento['TITULO-DO-PERIODICO-OU-REVISTA'] ?? ''),
                    'issn' => (string)($detalhamento['ISSN'] ?? ''),
                    'volume' => (string)($detalhamento['VOLUME'] ?? ''),
                    'pagina_inicial' => (string)($detalhamento['PAGINA-INICIAL'] ?? ''),
                    'pagina_final' => (string)($detalhamento['PAGINA-FINAL'] ?? ''),
                    'doi' => (string)($dados_basicos['DOI'] ?? ''),
                    'idioma' => (string)($dados_basicos['IDIOMA'] ?? ''),
                    'lattesID' => $lattesID,
                    'timestamp_indexacao' => date('Y-m-d H:i:s')
                ];
                
                $producoes['artigos']++;
                $producoes['total']++;
            }
        }
        
        // LIVROS PUBLICADOS
        if (isset($prod_bib->{'LIVROS-E-CAPITULOS'}->{'LIVROS-PUBLICADOS-OU-ORGANIZADOS'})) {
            foreach ($prod_bib->{'LIVROS-E-CAPITULOS'}->{'LIVROS-PUBLICADOS-OU-ORGANIZADOS'}->{'LIVRO-PUBLICADO-OU-ORGANIZADO'} as $livro) {
                $dados_basicos = $livro->{'DADOS-BASICOS-DO-LIVRO'};
                $detalhamento = $livro->{'DETALHAMENTO-DO-LIVRO'};
                
                $autores = [];
                if (isset($livro->{'AUTORES'})) {
                    foreach ($livro->{'AUTORES'} as $autor) {
                        $autores[] = (string)$autor['NOME-COMPLETO-DO-AUTOR'];
                    }
                }
                
                $producoes['items'][] = [
                    'tipo' => 'LIVRO',
                    'natureza' => 'LIVRO_PUBLICADO',
                    'titulo' => (string)$dados_basicos['TITULO-DO-LIVRO'],
                    'ano' => (int)$dados_basicos['ANO'],
                    'autores' => implode('; ', $autores),
                    'editora' => (string)($detalhamento['NOME-DA-EDITORA'] ?? ''),
                    'isbn' => (string)($detalhamento['ISBN'] ?? ''),
                    'numero_paginas' => (string)($detalhamento['NUMERO-DE-PAGINAS'] ?? ''),
                    'doi' => (string)($dados_basicos['DOI'] ?? ''),
                    'idioma' => (string)($dados_basicos['IDIOMA'] ?? ''),
                    'lattesID' => $lattesID,
                    'timestamp_indexacao' => date('Y-m-d H:i:s')
                ];
                
                $producoes['livros']++;
                $producoes['total']++;
            }
        }
        
        // CAPÍTULOS DE LIVROS
        if (isset($prod_bib->{'LIVROS-E-CAPITULOS'}->{'CAPITULOS-DE-LIVROS-PUBLICADOS'})) {
            foreach ($prod_bib->{'LIVROS-E-CAPITULOS'}->{'CAPITULOS-DE-LIVROS-PUBLICADOS'}->{'CAPITULO-DE-LIVRO-PUBLICADO'} as $capitulo) {
                $dados_basicos = $capitulo->{'DADOS-BASICOS-DO-CAPITULO'};
                $detalhamento = $capitulo->{'DETALHAMENTO-DO-CAPITULO'};
                
                $autores = [];
                if (isset($capitulo->{'AUTORES'})) {
                    foreach ($capitulo->{'AUTORES'} as $autor) {
                        $autores[] = (string)$autor['NOME-COMPLETO-DO-AUTOR'];
                    }
                }
                
                $producoes['items'][] = [
                    'tipo' => 'CAPITULO',
                    'natureza' => 'CAPITULO_LIVRO',
                    'titulo' => (string)$dados_basicos['TITULO-DO-CAPITULO-DO-LIVRO'],
                    'ano' => (int)$dados_basicos['ANO'],
                    'autores' => implode('; ', $autores),
                    'titulo_livro' => (string)($detalhamento['TITULO-DO-LIVRO'] ?? ''),
                    'editora' => (string)($detalhamento['NOME-DA-EDITORA'] ?? ''),
                    'isbn' => (string)($detalhamento['ISBN'] ?? ''),
                    'pagina_inicial' => (string)($detalhamento['PAGINA-INICIAL'] ?? ''),
                    'pagina_final' => (string)($detalhamento['PAGINA-FINAL'] ?? ''),
                    'doi' => (string)($dados_basicos['DOI'] ?? ''),
                    'idioma' => (string)($dados_basicos['IDIOMA'] ?? ''),
                    'lattesID' => $lattesID,
                    'timestamp_indexacao' => date('Y-m-d H:i:s')
                ];
                
                $producoes['capitulos']++;
                $producoes['total']++;
            }
        }
        
        // TRABALHOS EM EVENTOS
        if (isset($prod_bib->{'TRABALHOS-EM-EVENTOS'})) {
            foreach ($prod_bib->{'TRABALHOS-EM-EVENTOS'}->{'TRABALHO-EM-EVENTOS'} as $evento) {
                $dados_basicos = $evento->{'DADOS-BASICOS-DO-TRABALHO'};
                $detalhamento = $evento->{'DETALHAMENTO-DO-TRABALHO'};
                
                $autores = [];
                if (isset($evento->{'AUTORES'})) {
                    foreach ($evento->{'AUTORES'} as $autor) {
                        $autores[] = (string)$autor['NOME-COMPLETO-DO-AUTOR'];
                    }
                }
                
                $producoes['items'][] = [
                    'tipo' => 'EVENTO',
                    'natureza' => (string)($dados_basicos['NATUREZA'] ?? 'COMPLETO'),
                    'titulo' => (string)$dados_basicos['TITULO-DO-TRABALHO'],
                    'ano' => (int)$dados_basicos['ANO-DO-TRABALHO'],
                    'autores' => implode('; ', $autores),
                    'nome_evento' => (string)($detalhamento['NOME-DO-EVENTO'] ?? ''),
                    'titulo_anais' => (string)($detalhamento['TITULO-DOS-ANAIS-OU-PROCEEDINGS'] ?? ''),
                    'isbn' => (string)($detalhamento['ISBN'] ?? ''),
                    'doi' => (string)($dados_basicos['DOI'] ?? ''),
                    'idioma' => (string)($dados_basicos['IDIOMA'] ?? ''),
                    'lattesID' => $lattesID,
                    'timestamp_indexacao' => date('Y-m-d H:i:s')
                ];
                
                $producoes['eventos']++;
                $producoes['total']++;
            }
        }
        
        return $producoes;
    }
    
    /**
     * Extrair projetos de pesquisa
     */
    private function extractProjetos($xml, $lattesID, $ppg_nome) {
        $projetos = [
            'items' => [],
            'total' => 0
        ];
        
        if (!isset($xml->{'DADOS-GERAIS'}->{'PARTICIPACAO-EM-PROJETO'})) {
            return $projetos;
        }
        
        foreach ($xml->{'DADOS-GERAIS'}->{'PARTICIPACAO-EM-PROJETO'}->{'PARTICIPACAO-EM-PROJETO-DE-PESQUISA'} as $projeto) {
            $ano_inicio = (int)($projeto['ANO-INICIO'] ?? 0);
            $ano_fim = (int)($projeto['ANO-FIM'] ?? 0);
            
            $equipe = [];
            if (isset($projeto->{'EQUIPE-DO-PROJETO'})) {
                foreach ($projeto->{'EQUIPE-DO-PROJETO'}->children() as $membro) {
                    $equipe[] = [
                        'nome' => (string)$membro['NOME-COMPLETO'],
                        'funcao' => (string)($membro['NOME-DA-FUNCAO-NO-PROJETO'] ?? 'Pesquisador')
                    ];
                }
            }
            
            $projetos['items'][] = [
                'titulo' => (string)($projeto['NOME-DO-PROJETO'] ?? ''),
                'descricao' => (string)($projeto['DESCRICAO-DO-PROJETO'] ?? ''),
                'ano_inicio' => $ano_inicio,
                'ano_fim' => $ano_fim > 0 ? $ano_fim : null,
                'situacao' => $ano_fim > 0 && $ano_fim <= date('Y') ? 'Concluído' : 'Em andamento',
                'natureza' => (string)($projeto['NATUREZA'] ?? 'PESQUISA'),
                'financiamento' => (string)($projeto['NOME-DA-INSTITUICAO-FINANCIADORA'] ?? ''),
                'equipe' => $equipe,
                'ppg' => $ppg_nome,
                'lattesID' => $lattesID,
                'timestamp_indexacao' => date('Y-m-d H:i:s')
            ];
            
            $projetos['total']++;
        }
        
        return $projetos;
    }
    
    /**
     * Indexar pesquisador no Elasticsearch
     */
    private function indexPesquisador($pesquisador) {
        if (!$this->client) {
            echo "⚠️ Elasticsearch não disponível. Pesquisador não indexado.\n";
            return false;
        }
        
        try {
            $params = [
                'index' => $this->index_cv,
                'id' => $pesquisador['lattesID'],
                'body' => $pesquisador
            ];
            
            $response = $this->client->index($params);
            echo "✅ Pesquisador indexado: {$pesquisador['nome_completo']}\n";
            return $response;
        } catch (\Exception $e) {
            echo "❌ Erro ao indexar pesquisador: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Indexar produções no Elasticsearch
     */
    private function indexProducoes($producoes, $pesquisador) {
        if (!$this->client || empty($producoes)) {
            return 0;
        }
        
        $indexed = 0;
        
        foreach ($producoes as $producao) {
            // Adicionar dados do PPG
            $producao['ppg'] = $pesquisador['ppg'];
            $producao['area_concentracao'] = $pesquisador['area_concentracao'];
            $producao['campus'] = $pesquisador['campus'];
            
            try {
                $id = md5($producao['titulo'] . $producao['ano'] . $producao['lattesID']);
                
                $params = [
                    'index' => $this->index,
                    'id' => $id,
                    'body' => $producao
                ];
                
                $this->client->index($params);
                $indexed++;
            } catch (\Exception $e) {
                echo "⚠️ Erro ao indexar produção: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ {$indexed} produções indexadas\n";
        return $indexed;
    }
    
    /**
     * Indexar projetos no Elasticsearch
     */
    private function indexProjetos($projetos, $pesquisador) {
        if (!$this->client || empty($projetos)) {
            return 0;
        }
        
        $indexed = 0;
        
        foreach ($projetos as $projeto) {
            try {
                $id = md5($projeto['titulo'] . $projeto['ano_inicio'] . $projeto['lattesID']);
                
                $params = [
                    'index' => $this->index_projetos,
                    'id' => $id,
                    'body' => $projeto
                ];
                
                $this->client->index($params);
                $indexed++;
            } catch (\Exception $e) {
                echo "⚠️ Erro ao indexar projeto: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ {$indexed} projetos indexados\n";
        return $indexed;
    }
}

// Uso via CLI
if (php_sapi_name() === 'cli') {
    $opts = getopt('f:p:a:', ['file:', 'ppg:', 'area:']);
    
    $xml_file = $opts['f'] ?? $opts['file'] ?? null;
    $ppg = $opts['p'] ?? $opts['ppg'] ?? null;
    $area = $opts['a'] ?? $opts['area'] ?? null;
    
    if (!$xml_file) {
        echo "Uso: php LattesImporter.php -f <arquivo.xml> [-p <ppg>] [-a <area>]\n";
        echo "\nExemplo:\n";
        echo "  php LattesImporter.php -f curriculo.xml -p \"Biotecnologia\" -a \"Biotecnologia Industrial\"\n";
        exit(1);
    }
    
    echo "\n";
    echo "╔═══════════════════════════════════════════════════╗\n";
    echo "║   PRODMAIS UMC - Importador de Currículos Lattes ║\n";
    echo "╚═══════════════════════════════════════════════════╝\n";
    echo "\n";
    
    try {
        $importer = new LattesImporter();
        $result = $importer->importFromXML($xml_file, $ppg, $area);
        
        echo "\n";
        echo "╔═══════════════════════════════════════════════════╗\n";
        echo "║              IMPORTAÇÃO CONCLUÍDA!                ║\n";
        echo "╚═══════════════════════════════════════════════════╝\n";
        echo "\n";
        
    } catch (\Exception $e) {
        echo "\n❌ ERRO: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}
