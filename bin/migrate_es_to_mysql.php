#!/usr/bin/env php
<?php
/**
 * PRODMAIS UMC - Script de Migração
 * Elasticsearch → MySQL
 * 
 * Este script migra todos os dados do Elasticsearch para MySQL
 * Execução: php bin/migrate_es_to_mysql.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

// =====================================================
// CONFIGURAÇÃO
// =====================================================

echo "\n╔═══════════════════════════════════════════════════╗\n";
echo "║   PRODMAIS UMC - Migração ES → MySQL            ║\n";
echo "╚═══════════════════════════════════════════════════╝\n\n";

// Verificar se .env existe
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die("❌ Arquivo .env não encontrado!\n   Crie o arquivo com as credenciais MySQL.\n\n");
}

// Carregar variáveis de ambiente
$env = parse_ini_file($envFile);

// =====================================================
// CONEXÃO MYSQL
// =====================================================
echo "🔌 Conectando ao MySQL...\n";

try {
    $mysql = new PDO(
        "mysql:host={$env['MYSQL_HOST']};dbname={$env['MYSQL_DB']};charset=utf8mb4",
        $env['MYSQL_USER'],
        $env['MYSQL_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    echo "✅ Conectado ao MySQL: {$env['MYSQL_HOST']}/{$env['MYSQL_DB']}\n\n";
} catch (PDOException $e) {
    die("❌ Erro ao conectar MySQL: " . $e->getMessage() . "\n\n");
}

// =====================================================
// CONEXÃO ELASTICSEARCH
// =====================================================
echo "🔌 Conectando ao Elasticsearch...\n";

try {
    $es = getElasticsearchClient();
    if ($es === null) {
        throw new Exception("Cliente Elasticsearch não disponível");
    }
    
    $health = $es->cluster()->health();
    echo "✅ Conectado ao Elasticsearch: {$health['cluster_name']}\n";
    echo "   Status: {$health['status']}\n\n";
} catch (Exception $e) {
    die("❌ Erro ao conectar Elasticsearch: " . $e->getMessage() . "\n\n");
}

// =====================================================
// ESTATÍSTICAS INICIAIS
// =====================================================
echo "📊 Contando documentos...\n";

$stats = [
    'cv' => 0,
    'producoes' => 0,
    'projetos' => 0
];

try {
    $stats['cv'] = $es->count(['index' => $index_cv])['count'] ?? 0;
    $stats['producoes'] = $es->count(['index' => $index])['count'] ?? 0;
    $stats['projetos'] = $es->count(['index' => $index_projetos])['count'] ?? 0;
    
    echo "   Currículos: {$stats['cv']}\n";
    echo "   Produções: {$stats['producoes']}\n";
    echo "   Projetos: {$stats['projetos']}\n\n";
} catch (Exception $e) {
    echo "⚠️  Erro ao contar: " . $e->getMessage() . "\n\n";
}

// =====================================================
// FASE 1: MIGRAR PESQUISADORES
// =====================================================
echo "═══════════════════════════════════════════════════\n";
echo "FASE 1: Migrando Pesquisadores\n";
echo "═══════════════════════════════════════════════════\n";

$pesquisadores_migrados = 0;
$pesquisadores_map = []; // Nome => ID MySQL

try {
    $result = $es->search([
        'index' => $index_cv,
        'size' => 1000,
        'body' => ['query' => ['match_all' => (object)[]]]
    ]);
    
    $stmt = $mysql->prepare("
        INSERT INTO pesquisadores (nome, cpf, lattes_id, orcid, email, ppg, departamento, link_lattes, link_openalex)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            nome = VALUES(nome),
            email = VALUES(email),
            ppg = VALUES(ppg),
            departamento = VALUES(departamento),
            link_lattes = VALUES(link_lattes),
            link_openalex = VALUES(link_openalex),
            updated_at = CURRENT_TIMESTAMP
    ");
    
    foreach ($result['hits']['hits'] as $hit) {
        $cv = $hit['_source'];
        
        $nome = $cv['nome'] ?? $cv['name'] ?? 'Nome Desconhecido';
        $cpf = $cv['cpf'] ?? null;
        $lattes_id = $cv['lattes_id'] ?? $cv['lattesId'] ?? null;
        $orcid = $cv['orcid'] ?? null;
        $email = $cv['email'] ?? null;
        $ppg = $cv['ppg'] ?? null;
        $departamento = $cv['departamento'] ?? $cv['department'] ?? null;
        $link_lattes = $cv['link_lattes'] ?? ($lattes_id ? "http://lattes.cnpq.br/{$lattes_id}" : null);
        $link_openalex = $cv['link_openalex'] ?? $cv['openalex_url'] ?? null;
        
        try {
            $stmt->execute([
                $nome,
                $cpf,
                $lattes_id,
                $orcid,
                $email,
                $ppg,
                $departamento,
                $link_lattes,
                $link_openalex
            ]);
            
            $id = $mysql->lastInsertId() ?: $mysql->query("SELECT id FROM pesquisadores WHERE nome = " . $mysql->quote($nome) . " LIMIT 1")->fetchColumn();
            $pesquisadores_map[$nome] = $id;
            
            $pesquisadores_migrados++;
            echo "  ✓ {$nome} (ID: {$id})\n";
        } catch (PDOException $e) {
            echo "  ✗ Erro ao inserir {$nome}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Migrados: {$pesquisadores_migrados} pesquisadores\n\n";
} catch (Exception $e) {
    echo "❌ Erro na migração de pesquisadores: " . $e->getMessage() . "\n\n";
}

// =====================================================
// FASE 2: MIGRAR PRODUÇÕES
// =====================================================
echo "═══════════════════════════════════════════════════\n";
echo "FASE 2: Migrando Produções Científicas\n";
echo "═══════════════════════════════════════════════════\n";

$producoes_migradas = 0;

try {
    $result = $es->search([
        'index' => $index,
        'size' => 10000,
        'body' => ['query' => ['match_all' => (object)[]]]
    ]);
    
    $stmt = $mysql->prepare("
        INSERT INTO producoes (pesquisador_id, tipo, titulo, ano, revista, qualis, ppg, doi, issn, autores, tags, link_openalex, citacoes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($result['hits']['hits'] as $hit) {
        $prod = $hit['_source'];
        
        // Tentar encontrar o pesquisador
        $autor_principal = $prod['autores'][0] ?? $prod['authors'][0] ?? 'Desconhecido';
        if (is_array($autor_principal)) {
            $autor_principal = $autor_principal['nome'] ?? $autor_principal['name'] ?? 'Desconhecido';
        }
        
        $pesquisador_id = $pesquisadores_map[$autor_principal] ?? null;
        
        // Se não encontrou, criar pesquisador genérico
        if (!$pesquisador_id) {
            try {
                $mysql->exec("INSERT IGNORE INTO pesquisadores (nome) VALUES (" . $mysql->quote($autor_principal) . ")");
                $pesquisador_id = $mysql->lastInsertId() ?: $mysql->query("SELECT id FROM pesquisadores WHERE nome = " . $mysql->quote($autor_principal) . " LIMIT 1")->fetchColumn();
                $pesquisadores_map[$autor_principal] = $pesquisador_id;
            } catch (PDOException $e) {
                echo "  ⚠️  Não foi possível criar pesquisador: {$autor_principal}\n";
                continue;
            }
        }
        
        $tipo = $prod['tipo'] ?? $prod['type'] ?? 'Outros';
        $titulo = $prod['titulo'] ?? $prod['title'] ?? 'Sem título';
        $ano = $prod['ano'] ?? $prod['year'] ?? date('Y');
        $revista = $prod['revista'] ?? $prod['journal'] ?? null;
        $qualis = $prod['qualis'] ?? null;
        $ppg = $prod['ppg'] ?? null;
        $doi = $prod['doi'] ?? null;
        $issn = $prod['issn'] ?? null;
        $autores = json_encode($prod['autores'] ?? $prod['authors'] ?? [$autor_principal]);
        $tags = json_encode($prod['tags'] ?? []);
        $link_openalex = $prod['link_openalex'] ?? $prod['openalex_url'] ?? null;
        $citacoes = $prod['citacoes'] ?? $prod['cited_by_count'] ?? 0;
        
        try {
            $stmt->execute([
                $pesquisador_id,
                $tipo,
                $titulo,
                $ano,
                $revista,
                $qualis,
                $ppg,
                $doi,
                $issn,
                $autores,
                $tags,
                $link_openalex,
                $citacoes
            ]);
            
            $producoes_migradas++;
            
            if ($producoes_migradas % 10 == 0) {
                echo "  ✓ {$producoes_migradas} produções migradas...\r";
            }
        } catch (PDOException $e) {
            echo "  ✗ Erro ao inserir produção: " . substr($titulo, 0, 50) . "...\n";
        }
    }
    
    echo "\n✅ Migradas: {$producoes_migradas} produções\n\n";
} catch (Exception $e) {
    echo "❌ Erro na migração de produções: " . $e->getMessage() . "\n\n";
}

// =====================================================
// FASE 3: MIGRAR PROJETOS
// =====================================================
echo "═══════════════════════════════════════════════════\n";
echo "FASE 3: Migrando Projetos de Pesquisa\n";
echo "═══════════════════════════════════════════════════\n";

$projetos_migrados = 0;

try {
    $result = $es->search([
        'index' => $index_projetos,
        'size' => 1000,
        'body' => ['query' => ['match_all' => (object)[]]]
    ]);
    
    $stmt = $mysql->prepare("
        INSERT INTO projetos (titulo, coordenador, ppg, status, ano_inicio, ano_fim, descricao, membros, financiamento)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($result['hits']['hits'] as $hit) {
        $proj = $hit['_source'];
        
        $titulo = $proj['titulo'] ?? $proj['title'] ?? 'Projeto sem título';
        $coordenador = $proj['coordenador'] ?? $proj['coordinator'] ?? null;
        $ppg = $proj['ppg'] ?? null;
        $status = $proj['status'] ?? 'Ativo';
        $ano_inicio = $proj['ano_inicio'] ?? $proj['start_year'] ?? null;
        $ano_fim = $proj['ano_fim'] ?? $proj['end_year'] ?? null;
        $descricao = $proj['descricao'] ?? $proj['description'] ?? null;
        $membros = json_encode($proj['membros'] ?? $proj['members'] ?? []);
        $financiamento = $proj['financiamento'] ?? $proj['funding'] ?? null;
        
        try {
            $stmt->execute([
                $titulo,
                $coordenador,
                $ppg,
                $status,
                $ano_inicio,
                $ano_fim,
                $descricao,
                $membros,
                $financiamento
            ]);
            
            $projetos_migrados++;
            echo "  ✓ {$titulo}\n";
        } catch (PDOException $e) {
            echo "  ✗ Erro ao inserir projeto: " . substr($titulo, 0, 50) . "...\n";
        }
    }
    
    echo "\n✅ Migrados: {$projetos_migrados} projetos\n\n";
} catch (Exception $e) {
    echo "⚠️  Nenhum projeto encontrado ou erro: " . $e->getMessage() . "\n\n";
}

// =====================================================
// ESTATÍSTICAS FINAIS
// =====================================================
echo "═══════════════════════════════════════════════════\n";
echo "📊 RESUMO DA MIGRAÇÃO\n";
echo "═══════════════════════════════════════════════════\n";

try {
    $count_pesq = $mysql->query("SELECT COUNT(*) FROM pesquisadores")->fetchColumn();
    $count_prod = $mysql->query("SELECT COUNT(*) FROM producoes")->fetchColumn();
    $count_proj = $mysql->query("SELECT COUNT(*) FROM projetos")->fetchColumn();
    
    echo "📁 Elasticsearch → MySQL:\n";
    echo "   Pesquisadores: {$stats['cv']} → {$count_pesq}\n";
    echo "   Produções: {$stats['producoes']} → {$count_prod}\n";
    echo "   Projetos: {$stats['projetos']} → {$count_proj}\n";
    
    echo "\n✅ Migração concluída com sucesso!\n";
    echo "   Dados salvos no MySQL: {$env['MYSQL_HOST']}/{$env['MYSQL_DB']}\n\n";
} catch (PDOException $e) {
    echo "⚠️  Erro ao obter estatísticas finais: " . $e->getMessage() . "\n\n";
}

echo "═══════════════════════════════════════════════════\n\n";
