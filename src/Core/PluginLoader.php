<?php
/**
 * PluginLoader - Gerenciador de Plugins para o PRODMAIS
 * Escaneia e carrega plugins da pasta /plugins/
 */
class PluginLoader {
    private static $pluginsDir;

    /**
     * Inicializar o carregador de plugins
     */
    public static function loadPlugins($dir = null) {
        self::$pluginsDir = $dir ?? __DIR__ . '/../../plugins';
        
        if (!is_dir(self::$pluginsDir)) {
            mkdir(self::$pluginsDir, 0755, true);
            return;
        }

        $plugins = scandir(self::$pluginsDir);
        
        foreach ($plugins as $plugin) {
            if ($plugin === '.' || $plugin === '..') continue;
            
            $pluginPath = self::$pluginsDir . '/' . $plugin;
            $mainFile = $pluginPath . '/plugin.php';

            if (is_dir($pluginPath) && file_exists($mainFile)) {
                require_once $mainFile;
            }
        }
    }
}
