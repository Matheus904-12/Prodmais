<?php
/**
 * Plugin: Filtro de Títulos
 * Descrição: Exemplifica o uso de FILTERS para modificar dados em tempo de execução.
 */

// Este é um exemplo de como o sistema poderia usar filtros no futuro
HookManager::addFilter('format_production_title', function($title) {
    return "💡 " . $title;
});
