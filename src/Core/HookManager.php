<?php
/**
 * HookManager - Sistema de Hooks (Action/Filters) estilo WordPress
 * Permite extensibilidade sem modificar o código core
 */
class HookManager {
    private static $actions = [];
    private static $filters = [];

    /**
     * Adicionar uma ação (Action)
     */
    public static function addAction($hookName, $callback, $priority = 10) {
        if (!isset(self::$actions[$hookName])) {
            self::$actions[$hookName] = [];
        }
        self::$actions[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
        
        // Ordenar por prioridade
        usort(self::$actions[$hookName], function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Executar ações de um hook
     */
    public static function doAction($hookName, ...$args) {
        if (isset(self::$actions[$hookName])) {
            foreach (self::$actions[$hookName] as $action) {
                call_user_func_array($action['callback'], $args);
            }
        }
    }

    /**
     * Adicionar um filtro (Filter)
     */
    public static function addFilter($hookName, $callback, $priority = 10) {
        if (!isset(self::$filters[$hookName])) {
            self::$filters[$hookName] = [];
        }
        self::$filters[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
        
        usort(self::$filters[$hookName], function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Aplicar filtros a um valor
     */
    public static function applyFilters($hookName, $value, ...$args) {
        if (isset(self::$filters[$hookName])) {
            foreach (self::$filters[$hookName] as $filter) {
                $value = call_user_func_array($filter['callback'], array_merge([$value], $args));
            }
        }
        return $value;
    }
}
