<?php
namespace App\View;

use HookManager;

/**
 * Base Component Class
 * Handles rendering and assets registration
 */
abstract class Component {
    protected $props;
    protected $name;
    protected $basePath;

    public function __construct($props = []) {
        $this->props = $props;
        $this->name = (new \ReflectionClass($this))->getShortName();
        $this->basePath = dirname((new \ReflectionClass($this))->getFileName());
        
        $this->registerAssets();
    }

    /**
     * Register CSS and JS for the component
     */
    protected function registerAssets() {
        $cssFile = "{$this->basePath}/{$this->name}.css";
        $jsFile = "{$this->basePath}/{$this->name}.js";
        
        // Inline CSS into app_head
        if (file_exists($cssFile)) {
            HookManager::addAction('app_head', function() use ($cssFile) {
                echo "<!-- Component Styles: {$this->name} -->\n";
                echo "<style>\n" . file_get_contents($cssFile) . "\n</style>\n";
            }, 20); // Priority 20 to load after main themes
        }
        
        // Inline JS into app_footer
        if (file_exists($jsFile)) {
            HookManager::addAction('app_footer', function() use ($jsFile) {
                echo "<!-- Component Script: {$this->name} -->\n";
                echo "<script>\n" . file_get_contents($jsFile) . "\n</script>\n";
            });
        }
    }

    /**
     * Helper to get a prop with default value
     */
    protected function getProp($key, $default = null) {
        return $this->props[$key] ?? $default;
    }

    /**
     * Set a prop
     */
    public function setProp($key, $value) {
        $this->props[$key] = $value;
        return $this;
    }

    /**
     * Main render method
     */
    abstract public function render();

    /**
     * Static helper for quick rendering
     */
    public static function display($props = []) {
        $instance = new static($props);
        return $instance->render();
    }
}
