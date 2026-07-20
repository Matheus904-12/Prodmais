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
        
        $glowMap = [
            'primary'  => ['rgba(59,130,246,.16)', 'rgba(3,105,161,.12)'],
            'success'  => ['rgba(5,150,105,.16)',  'rgba(13,148,136,.12)'],
            'info'     => ['rgba(37,99,235,.16)',  'rgba(8,145,178,.12)'],
            'lavender' => ['rgba(99,102,241,.16)', 'rgba(139,92,246,.12)'],
        ];

        [$glowA, $glowB] = $glowMap[$variant] ?? $glowMap['primary'];

        ?>
        <section class="hero-section" style="background:#070d1f; background-image: radial-gradient(ellipse 55% 65% at 12% 20%, <?php echo $glowA; ?>, transparent), radial-gradient(ellipse 45% 55% at 88% 85%, <?php echo $glowB; ?>, transparent); padding: 4rem 0 3rem; position: relative; overflow: hidden;">
            <div class="hero-bg-decor">
                <div class="hero-dot-grid"></div>
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
