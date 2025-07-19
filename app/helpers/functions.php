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

if (!function_exists('form_helper')) {
    /**
     * Get FormHelper instance or call method directly
     */
    function form_helper(string $method = null, ...$args)
    {
        $className = 'App\\Helpers\\FormHelper';
        
        if ($method === null) {
            return $className;
        }
        
        if (method_exists($className, $method)) {
            return call_user_func_array([$className, $method], $args);
        }
        
        throw new BadMethodCallException("Method {$method} not found in FormHelper");
    }
}

if (!function_exists('form_open')) {
    /**
     * Open form tag shortcut
     */
    function form_open(string $action = '', array $options = []): string
    {
        return \App\Helpers\FormHelper::open($action, $options);
    }
}

if (!function_exists('form_close')) {
    /**
     * Close form tag shortcut
     */
    function form_close(): string
    {
        return \App\Helpers\FormHelper::close();
    }
}

if (!function_exists('form_text')) {
    /**
     * Text input shortcut
     */
    function form_text(string $name, string $value = '', array $options = []): string
    {
        return \App\Helpers\FormHelper::text($name, $value, $options);
    }
}

if (!function_exists('form_select')) {
    /**
     * Select dropdown shortcut
     */
    function form_select(string $name, array $options = [], string $selected = '', array $attributes = []): string
    {
        return \App\Helpers\FormHelper::select($name, $options, $selected, $attributes);
    }
}

if (!function_exists('form_checkbox')) {
    /**
     * Checkbox input shortcut
     */
    function form_checkbox(string $name, string $value = '1', bool $checked = false, array $options = []): string
    {
        return \App\Helpers\FormHelper::checkbox($name, $value, $checked, $options);
    }
}

if (!function_exists('form_submit')) {
    /**
     * Submit button shortcut
     */
    function form_submit(string $text = 'Submit', array $options = []): string
    {
        return \App\Helpers\FormHelper::submit($text, $options);
    }
}

if (!function_exists('form_group')) {
    /**
     * Form group shortcut
     */
    function form_group(string $label, string $input, array $options = []): string
    {
        return \App\Helpers\FormHelper::group($label, $input, $options);
    }
}