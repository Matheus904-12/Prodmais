<?php
namespace App\View\Components\StatCard;

use App\View\Component;

class StatCard extends Component {
    public function render() {
        $value = $this->getProp('value', 0);
        $label = $this->getProp('label', '');
        $icon = $this->getProp('icon', 'file-alt');
        $colorRange = $this->getProp('colors', ['#6366f1', '#8b5cf6']);
        $link = $this->getProp('link', '#');
        $linkText = $this->getProp('link_text', 'Ver todos');
        $delay = $this->getProp('delay', '0s');
        
        $gradient = "linear-gradient(135deg, {$colorRange[0]}, {$colorRange[1]})";
        $shadowColor = $colorRange[0] . '4D'; // 30% opacity in hex
        
        ?>
        <div class="stat-card-wrapper fade-in-up" style="animation-delay: <?php echo $delay; ?>;">
            <div class="stat-card" style="background: <?php echo $gradient; ?>; box-shadow: 0 4px 12px <?php echo $shadowColor; ?>;">
                <div class="stat-card-decor"></div>
                <div class="stat-card-content">
                    <div class="stat-card-icon">
                        <i class="fas fa-<?php echo $icon; ?>"></i>
                    </div>
                    <div class="stat-card-value">
                        <?php echo is_numeric($value) ? number_format($value) : $value; ?>
                    </div>
                    <div class="stat-card-label"><?php echo $label; ?></div>
                    
                    <?php if ($link && $link !== '#'): ?>
                    <a href="<?php echo $link; ?>" class="stat-card-link">
                        <?php echo $linkText; ?> <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
