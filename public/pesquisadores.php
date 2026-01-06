<?php
/**
 * PRODMAIS UMC - Página de Pesquisadores
 * Lista todos os pesquisadores cadastrados no sistema
 */

require_once __DIR__ . '/../config/config_umc.php';
require_once __DIR__ . '/../src/UmcFunctions.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

$client = getElasticsearchClient();
$pesquisadores = [];
$total_pesquisadores = 0;

if ($client !== null) {
    try {
        $params = [
            'index' => 'prodmais_umc_cv',
            'body' => [
                'size' => 100,
                'sort' => [
                    ['nome_completo.keyword' => ['order' => 'asc']]
                ],
                'query' => [
                    'match_all' => new stdClass()
                ]
            ]
        ];
        $response = $client->search($params);
        $total_pesquisadores = $response['hits']['total']['value'];
        foreach ($response['hits']['hits'] as $hit) {
            $pesquisadores[] = $hit['_source'];
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar pesquisadores: " . $e->getMessage());
    }
}

if ($client === null || empty($pesquisadores)) {
    try {
        require_once __DIR__ . '/../src/DatabaseService.php';
        $dbService = new DatabaseService($config ?? []);
        $pesquisadores = $dbService->getPesquisadores();
        $total_pesquisadores = count($pesquisadores);
    } catch (Exception $e) {
        error_log("Erro ao buscar pesquisadores no banco relacional: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisadores - <?php echo $branch; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Elegante Profissional -->
    <link rel="stylesheet" href="/css/prodmais-elegant.css">
    <link rel="stylesheet" href="/css/umc-theme.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body>

<!-- Navbar Elegante -->
<nav class="navbar navbar-expand-lg navbar-elegant">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/03/Logo_umc1.png" 
                 alt="UMC Logo" 
                 height="45" 
                 class="me-2"
                 onerror="this.style.display='none'">
            <strong style="font-size: 1.5rem; background: linear-gradient(135deg, #1a56db, #0369a1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Prodmais</strong>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/index_umc.php"><i class="fas fa-home me-1"></i> Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant active" href="/pesquisadores.php"><i class="fas fa-users me-1"></i> Pesquisadores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/ppgs.php"><i class="fas fa-university me-1"></i> PPGs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/projetos.php"><i class="fas fa-project-diagram me-1"></i> Projetos</a>
                </li>
                <?php if ($mostrar_link_dashboard): ?>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/dashboard.php"><i class="fas fa-chart-line me-1"></i> Dashboard</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link-elegant" href="/login.php"><i class="fas fa-cog me-1"></i> Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section Ultra Elegante -->
<section style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%); padding: 4rem 0 3rem; position: relative; overflow: hidden;">
    <!-- Background decorativo -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1;">
        <div style="position: absolute; top: 20%; left: 10%; width: 300px; height: 300px; background: white; border-radius: 50%; filter: blur(80px);"></div>
        <div style="position: absolute; bottom: 20%; right: 10%; width: 400px; height: 400px; background: white; border-radius: 50%; filter: blur(100px);"></div>
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10 fade-in-up">
                <div style="margin-bottom: 1.5rem;">
                    <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.5rem 1.5rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; color: white; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-users" style="margin-right: 0.5rem;"></i>
                        <?php echo number_format($total_pesquisadores); ?> Pesquisadores Cadastrados
                    </span>
                </div>
                
                <h1 style="font-size: 3.5rem; font-weight: 900; margin-bottom: 1rem; color: white; line-height: 1.2; letter-spacing: -0.02em;">
                    <i class="fas fa-users me-3"></i>Pesquisadores
                </h1>
                <p style="font-size: 1.25rem; color: rgba(255,255,255,0.95); margin-bottom: 0; max-width: 700px; margin-left: auto; margin-right: auto; line-height: 1.6; font-weight: 400;">
                    Conheça os pesquisadores dos Programas de Pós-Graduação da UMC
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Pesquisadores Section -->
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">
        <?php if (empty($pesquisadores)): ?>
        <div class="alert alert-info glass-effect" role="alert">
            <i class="fas fa-info-circle me-2"></i> 
            Nenhum pesquisador encontrado. Importe currículos Lattes para visualizar.
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($pesquisadores as $index => $p): 
                $nome = $p['nome_completo'] ?? 'Nome não informado';
                $orcid = $p['orcid'] ?? '';
                $lattes = $p['id_lattes'] ?? '';
                $ultima_atualizacao = $p['data_atualizacao_cv'] ?? '';
            ?>
            <div class="col-lg-6 fade-in-up" style="animation-delay: <?php echo ($index * 0.05); ?>s;">
                <div style="background: white; border-radius: 12px; padding: 1.25rem; border: 1px solid var(--gray-200); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"
                     onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(99, 102, 241, 0.12)'; this.style.borderColor='rgb(99, 102, 241)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)'; this.style.borderColor='var(--gray-200)';">
                    
                    <!-- Decorative gradient bar -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7);"></div>
                    
                    <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: white; box-shadow: 0 4px 8px rgba(99, 102, 241, 0.25); flex-shrink: 0;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 700; color: var(--gray-900); line-height: 1.3;">
                                <?php echo htmlspecialchars($nome); ?>
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.375rem;">
                                <span style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <i class="fas fa-id-card"></i><?php echo !empty($lattes) ? 'Lattes' : 'Pesquisador'; ?>
                                </span>
                                <?php if (!empty($orcid)): ?>
                                <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <i class="fab fa-orcid"></i>ORCID
                                </span>
                                <?php endif; ?>
                                <span style="background: var(--gray-100); color: var(--gray-700); padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <i class="fas fa-university" style="color: #6366f1;"></i>UMC
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($ultima_atualizacao)): ?>
                    <div style="background: var(--gray-50); border-left: 2px solid #6366f1; padding: 0.5rem 0.75rem; border-radius: 6px; margin-bottom: 1rem;">
                        <p style="color: var(--gray-600); font-size: 0.813rem; margin: 0; display: flex; align-items: center; gap: 0.375rem;">
                            <i class="fas fa-clock" style="color: #6366f1; font-size: 0.75rem;"></i>
                            Atualizado em <?php echo date('d/m/Y', strtotime($ultima_atualizacao)); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="/result.php?pesquisador=<?php echo urlencode($nome); ?>" 
                           style="flex: 1; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 0.625rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem; transition: all 0.3s ease; border: none;"
                           onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(99, 102, 241, 0.3)';"
                           onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                            <i class="fas fa-search"></i>Ver Produções
                        </a>
                        <?php if (!empty($orcid)): ?>
                        <a href="https://orcid.org/<?php echo htmlspecialchars($orcid); ?>" target="_blank" 
                           style="background: #a6ce39; color: white; padding: 0.625rem 1rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s ease; box-shadow: 0 2px 6px rgba(166, 206, 57, 0.25);"
                           onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(166, 206, 57, 0.4)';"
                           onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 6px rgba(166, 206, 57, 0.25)';">
                            <i class="fab fa-orcid" style="font-size: 1.125rem;"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Footer Elegante -->
<footer class="footer-elegant">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5><?php echo $instituicao; ?></h5>
                <p style="color: var(--gray-400); line-height: 1.6;"><?php echo $branch_description; ?></p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Links Úteis</h5>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><a href="<?php echo $privacy_policy_url; ?>">Política de Privacidade</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="<?php echo $terms_of_use_url; ?>">Termos de Uso</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/sobre">Sobre</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Integrações</h5>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #10b981;"></i> Plataforma Lattes</li>
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #10b981;"></i> ORCID</li>
                    <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: #10b981;"></i> OpenAlex</li>
                </ul>
            </div>
        </div>
        <hr style="border-color: var(--gray-700); margin: 2rem 0;">
        <div class="text-center">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> <?php echo $instituicao; ?> - PIVIC 2025</p>
            <p style="font-size: 0.875rem; color: var(--gray-500);">
                Desenvolvido com excelência seguindo conformidade LGPD e padrões CAPES
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
