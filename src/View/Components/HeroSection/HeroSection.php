<?php
namespace App\View\Components\HeroSection;

use App\View\Component;

class HeroSection extends Component {
    public function render() {
        $title = $this->getProp('title', 'Prodmais');
        $subtitle = $this->getProp('subtitle', '');
        $badge = $this->getProp('badge', '');
        $badgeIcon = $this->getProp('badge_icon', 'star');
        $variant = $this->getProp('variant', 'primary'); // primary, success, info
        $showSearch = $this->getProp('show_search', false);
        $elasticsearch_available = $this->getProp('elasticsearch_available', true);
        
        $gradientMap = [
            'primary' => 'linear-gradient(135deg, #1a56db 0%, #0369a1 50%, #0891b2 100%)',
            'success' => 'linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%)',
            'info' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%)'
        ];
        
        $gradient = $gradientMap[$variant] ?? $gradientMap['primary'];
        
        ?>
        <section class="hero-section" style="background: <?php echo $gradient; ?>;">
            <div class="hero-bg-decor">
                <div class="blob blob-1"></div>
                <div class="blob blob-2"></div>
            </div>
            
            <div class="container hero-content">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-10 fade-in-up">
                        <?php if ($badge): ?>
                        <div class="hero-badge-wrapper">
                            <span class="hero-badge">
                                <i class="fas fa-<?php echo $badgeIcon; ?>"></i>
                                <?php echo $badge; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <h1 class="hero-title"><?php echo $title; ?></h1>
                        
                        <?php if ($subtitle): ?>
                        <p class="hero-subtitle"><?php echo $subtitle; ?></p>
                        <?php endif; ?>
                        
                        <?php if ($showSearch): ?>
                            <?php if (!$elasticsearch_available): ?>
                            <div class="alert alert-warning glass-panel mb-4" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i> 
                                <strong>Atenção:</strong> Elasticsearch não está disponível. Sistema funcionando em modo limitado.
                            </div>
                            <?php endif; ?>
                            
                            <form action="/presearch.php" method="POST" class="search-elegant mb-4">
                                <input type="search" 
                                       name="search" 
                                       placeholder="Pesquise por produções científicas, pesquisadores ou projetos..." 
                                       required>
                                <button type="submit">
                                    <i class="fas fa-search me-2"></i> Buscar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}
