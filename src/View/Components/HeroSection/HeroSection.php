<?php
namespace App\View\Components\HeroSection;

use App\View\Component;

class HeroSection extends Component {
    public function render() {
        $title = $this->getProp('title', 'Prodmais');
        $subtitle = $this->getProp('subtitle', '');
        $badge = $this->getProp('badge', '');
        $badgeIcon = $this->getProp('badge_icon', 'star');
        $variant = $this->getProp('variant', 'primary');
        $showSearch = $this->getProp('show_search', false);
        $elasticsearch_available = $this->getProp('elasticsearch_available', true);

        // Radial accent colors por variante — aplicados sobre o fundo dark navy
        $accentMap = [
            'primary'  => ['rgba(26,86,219,.22)',   'rgba(59,130,246,.15)'],
            'lavender' => ['rgba(99,102,241,.22)',   'rgba(139,92,246,.15)'],
            'success'  => ['rgba(5,150,105,.22)',    'rgba(16,185,129,.15)'],
            'info'     => ['rgba(14,165,233,.22)',   'rgba(6,182,212,.15)'],
        ];
        $accents = $accentMap[$variant] ?? $accentMap['primary'];

        // Cor do badge border/text varia por variante
        $badgeColorMap = [
            'primary'  => ['rgba(26,86,219,.2)',  'rgba(26,86,219,.35)',  '#93c5fd'],
            'lavender' => ['rgba(99,102,241,.2)', 'rgba(99,102,241,.35)', '#c4b5fd'],
            'success'  => ['rgba(5,150,105,.2)',  'rgba(5,150,105,.35)',  '#6ee7b7'],
            'info'     => ['rgba(14,165,233,.2)', 'rgba(14,165,233,.35)', '#7dd3fc'],
        ];
        $bc = $badgeColorMap[$variant] ?? $badgeColorMap['primary'];
        ?>
        <section class="hero-section-dark" style="
            background: #070d1f;
            background-image:
                radial-gradient(ellipse 55% 50% at 20% 65%, <?php echo $accents[0]; ?>, transparent),
                radial-gradient(ellipse 45% 45% at 82% 18%, <?php echo $accents[1]; ?>, transparent);
            position: relative;
            overflow: hidden;
            padding: 5rem 0 3.5rem;
        ">
            <!-- Dot grid overlay -->
            <div style="
                position: absolute;
                inset: 0;
                background-image: radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
                background-size: 28px 28px;
                pointer-events: none;
            " aria-hidden="true"></div>

            <div class="container" style="position: relative; z-index: 1;">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-10 fade-in-up">

                        <?php if ($badge): ?>
                        <div style="margin-bottom: 1.75rem;">
                            <span style="
                                display: inline-flex;
                                align-items: center;
                                gap: .5rem;
                                background: <?php echo $bc[0]; ?>;
                                border: 1px solid <?php echo $bc[1]; ?>;
                                border-radius: 100px;
                                padding: .375rem 1.1rem;
                                font-size: .75rem;
                                font-weight: 700;
                                letter-spacing: .08em;
                                text-transform: uppercase;
                                color: <?php echo $bc[2]; ?>;
                            ">
                                <i class="fas fa-<?php echo htmlspecialchars($badgeIcon); ?>" aria-hidden="true"></i>
                                <?php echo $badge; ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <h1 style="
                            font-size: 2.75rem;
                            font-weight: 900;
                            color: #f8fafc;
                            letter-spacing: -.03em;
                            line-height: 1.12;
                            margin-bottom: 1rem;
                        "><?php echo $title; ?></h1>

                        <?php if ($subtitle): ?>
                        <p style="
                            font-size: 1.05rem;
                            color: rgba(241,245,249,.72);
                            max-width: 720px;
                            margin: 0 auto;
                            line-height: 1.65;
                            font-weight: 400;
                        "><?php echo $subtitle; ?></p>
                        <?php endif; ?>

                        <?php if ($showSearch): ?>
                            <?php if (!$elasticsearch_available): ?>
                            <div class="alert alert-warning glass-panel mb-4 mt-3" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Atenção:</strong> Elasticsearch não está disponível. Sistema funcionando em modo limitado.
                            </div>
                            <?php endif; ?>

                            <form action="/presearch.php" method="POST" class="search-elegant mb-4 mt-4">
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
