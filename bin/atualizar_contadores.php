<?php
/**
 * PRODMAIS UMC - Script para atualizar contadores de produções e projetos
 * 
 * Este script atualiza os documentos de pesquisadores que foram importados
 * antes da implementação dos campos total_producoes e total_projetos
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  PRODMAIS UMC - Atualizar Contadores de Produções e Projetos ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$client = getElasticsearchClient();

if (!$client) {
    echo "❌ Erro: Não foi possível conectar ao Elasticsearch\n\n";
    exit(1);
}

echo "🔍 Buscando todos os pesquisadores...\n";

try {
    // Buscar todos os pesquisadores
    $params = [
        'index' => 'prodmais_umc_cv',
        'body' => [
            'size' => 100,
            'query' => [
                'match_all' => new stdClass()
            ]
        ]
    ];
    
    $response = $client->search($params);
    $pesquisadores = $response['hits']['hits'];
    $total = count($pesquisadores);
    
    echo "✅ Encontrados {$total} pesquisadores\n\n";
    
    if ($total === 0) {
        echo "ℹ️  Nenhum pesquisador encontrado.\n\n";
        exit(0);
    }
    
    $atualizados = 0;
    
    foreach ($pesquisadores as $hit) {
        $pesquisador = $hit['_source'];
        $lattesID = $pesquisador['lattesID'];
        $nome = $pesquisador['nome_completo'];
        
        echo "📊 Processando: {$nome}\n";
        echo "   Lattes ID: {$lattesID}\n";
        
        // Contar produções
        $count_producoes = $client->count([
            'index' => 'prodmais_umc',
            'body' => [
                'query' => [
                    'term' => ['lattesID' => $lattesID]
                ]
            ]
        ]);
        
        $total_producoes = $count_producoes['count'] ?? 0;
        
        // Contar projetos
        $count_projetos = $client->count([
            'index' => 'prodmais_umc_projetos',
            'body' => [
                'query' => [
                    'term' => ['lattesID' => $lattesID]
                ]
            ]
        ]);
        
        $total_projetos = $count_projetos['count'] ?? 0;
        
        echo "   📄 Produções: {$total_producoes}\n";
        echo "   🔬 Projetos: {$total_projetos}\n";
        
        // Verificar se precisa atualizar
        $precisa_atualizar = false;
        
        if (!isset($pesquisador['total_producoes']) || $pesquisador['total_producoes'] != $total_producoes) {
            $precisa_atualizar = true;
        }
        
        if (!isset($pesquisador['total_projetos']) || $pesquisador['total_projetos'] != $total_projetos) {
            $precisa_atualizar = true;
        }
        
        if (!isset($pesquisador['foto_url'])) {
            $precisa_atualizar = true;
        }
        
        if ($precisa_atualizar) {
            // Atualizar documento
            $update_body = [
                'doc' => [
                    'total_producoes' => $total_producoes,
                    'total_projetos' => $total_projetos
                ]
            ];
            
            // Adicionar foto_url se não existir
            if (!isset($pesquisador['foto_url'])) {
                $update_body['doc']['foto_url'] = "http://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&id={$lattesID}";
            }
            
            $client->update([
                'index' => 'prodmais_umc_cv',
                'id' => $lattesID,
                'body' => $update_body
            ]);
            
            echo "   ✅ Documento atualizado!\n";
            $atualizados++;
        } else {
            echo "   ℹ️  Já está atualizado\n";
        }
        
        echo "\n";
    }
    
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║                    ATUALIZAÇÃO CONCLUÍDA!                     ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "📊 Resumo:\n";
    echo "   Total de pesquisadores: {$total}\n";
    echo "   Atualizados: {$atualizados}\n";
    echo "   Já estavam corretos: " . ($total - $atualizados) . "\n";
    echo "\n";
    echo "🎉 Todos os contadores foram atualizados com sucesso!\n";
    echo "   Acesse http://localhost:8000/pesquisadores.php para visualizar\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n\n";
    exit(1);
}
