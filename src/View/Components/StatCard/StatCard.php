<?php
namespace App\View\Components\StatCard;

use App\View\Component;

class StatCard extends Component {
    public function render() {
        $value     = $this->getProp('value', 0);
        $label     = $this->getProp('label', '');
        $icon      = $this->getProp('icon', 'file-alt');
        $colors    = $this->getProp('colors', ['#1a56db', '#1e429f']);
        $link      = $this->getProp('link', '#');
        $linkText  = $this->getProp('link_text', 'Ver todos');
        $delay     = $this->getProp('delay', '0s');

        $iconGradient  = "linear-gradient(135deg, {$colors[0]}, {$colors[1]})";
        $accentColor   = $colors[0];
        ?>
        <div class="stat-card-wrapper fade-in-up" style="animation-delay: <?php echo $delay; ?>; height: 100%;">
            <div class="stat-card" style="border-top: 4px solid <?php echo $accentColor; ?>;">
                <div class="stat-card-content">
                    <div class="stat-card-icon" style="background: <?php echo $iconGradient; ?>;">
                        <i class="fas fa-<?php echo $icon; ?>" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card-value">
                        <?php echo is_numeric($value) ? number_format($value) : $value; ?>
                    </div>
                    <div class="stat-card-label"><?php echo htmlspecialchars($label); ?></div>

                    <?php if ($link && $link !== '#'): ?>
                    <a href="<?php echo $link; ?>" class="stat-card-link" style="color: <?php echo $accentColor; ?>;">
                        <?php echo $linkText; ?> <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
