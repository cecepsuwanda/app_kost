<?php

/**
 * Global helper functions for easier access in views
 * These functions provide shortcuts to commonly used helper methods
 */

if (!function_exists('html')) {
    /**
     * Get HtmlHelper instance or call method directly
     */
    function html(string $method = null, ...$args)
    {
        $className = 'App\\Helpers\\HtmlHelper';
        
        if ($method === null) {
            return $className;
        }
        
        if (method_exists($className, $method)) {
            return call_user_func_array([$className, $method], $args);
        }
        
        throw new BadMethodCallException("Method {$method} not found in HtmlHelper");
    }
}

if (!function_exists('view_helper')) {
    /**
     * Get ViewHelper instance or call method directly
     */
    function view_helper(string $method = null, ...$args)
    {
        $className = 'App\\Helpers\\ViewHelper';
        
        if ($method === null) {
            return $className;
        }
        
        if (method_exists($className, $method)) {
            return call_user_func_array([$className, $method], $args);
        }
        
        throw new BadMethodCallException("Method {$method} not found in ViewHelper");
    }
}

if (!function_exists('currency')) {
    /**
     * Format currency shortcut
     */
    function currency($amount): string
    {
        return \App\Helpers\HtmlHelper::currency($amount);
    }
}

if (!function_exists('badge')) {
    /**
     * Generate badge shortcut
     */
    function badge(string $text, string $type = 'primary', array $attributes = []): string
    {
        return \App\Helpers\HtmlHelper::badge($text, $type, $attributes);
    }
}

if (!function_exists('status_badge')) {
    /**
     * Generate status badge shortcut
     */
    function status_badge(string $status, array $mapping = []): string
    {
        return \App\Helpers\HtmlHelper::statusBadge($status, $mapping);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date shortcut
     */
    function format_date(string $date, string $format = 'd/m/Y'): string
    {
        return \App\Helpers\HtmlHelper::date($date, $format);
    }
}

if (!function_exists('room_status_badge')) {
    /**
     * Room status badge shortcut
     */
    function room_status_badge(string $status): string
    {
        return \App\Helpers\ViewHelper::roomStatusBadge($status);
    }
}

if (!function_exists('payment_status_badge')) {
    /**
     * Payment status badge shortcut
     */
    function payment_status_badge(string $status): string
    {
        return \App\Helpers\ViewHelper::paymentStatusBadge($status);
    }
}

if (!function_exists('helper_manager')) {
    /**
     * Get HelperManager instance
     */
    function helper_manager(): \App\Core\HelperManager
    {
        return \App\Core\HelperManager::getInstance();
    }
}

if (!function_exists('load_helper')) {
    /**
     * Load specific helper on demand
     */
    function load_helper(string $helperName): void
    {
        \App\Core\HelperManager::getInstance()->loadSpecificHelpers([$helperName]);
    }
}

if (!function_exists('is_helper_loaded')) {
    /**
     * Check if helper is loaded
     */
    function is_helper_loaded(string $helperName): bool
    {
        return \App\Core\HelperManager::getInstance()->isHelperLoaded($helperName);
    }
}