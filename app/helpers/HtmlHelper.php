<?php

namespace App\Helpers;

class HtmlHelper
{
    /**
     * Generate a badge element
     */
    public static function badge($text, $type = 'primary', $attributes = [])
    {
        $class = "badge bg-{$type}";
        if (isset($attributes['class'])) {
            $class .= ' ' . $attributes['class'];
            unset($attributes['class']);
        }
        
        $attrs = self::buildAttributes(array_merge(['class' => $class], $attributes));
        return "<span{$attrs}>{$text}</span>";
    }

    /**
     * Generate a form input
     */
    public static function input($type, $name, $options = [])
    {
        $value = $options['value'] ?? '';
        $placeholder = $options['placeholder'] ?? '';
        $class = 'form-control ' . ($options['class'] ?? '');
        $required = isset($options['required']) && $options['required'] ? 'required' : '';
        
        $attributes = [
            'type' => $type,
            'name' => $name,
            'class' => trim($class),
            'value' => $value,
            'placeholder' => $placeholder
        ];
        
        if ($required) $attributes['required'] = '';
        
        $attrs = self::buildAttributes($attributes);
        return "<input{$attrs}>";
    }

    /**
     * Generate a select dropdown
     */
    public static function select($name, $options = [], $selected = '', $attributes = [])
    {
        $class = 'form-select ' . ($attributes['class'] ?? '');
        $attrs = self::buildAttributes(array_merge(['name' => $name, 'class' => trim($class)], $attributes));
        
        $html = "<select{$attrs}>";
        foreach ($options as $value => $text) {
            $selectedAttr = ($value == $selected) ? ' selected' : '';
            $html .= "<option value=\"{$value}\"{$selectedAttr}>{$text}</option>";
        }
        $html .= "</select>";
        
        return $html;
    }

    /**
     * Generate a form group with label and input
     */
    public static function formGroup($label, $input, $options = [])
    {
        $colClass = $options['col'] ?? 'mb-3';
        $help = $options['help'] ?? '';
        
        $html = "<div class=\"{$colClass}\">";
        $html .= "<label class=\"form-label\">{$label}</label>";
        $html .= $input;
        if ($help) {
            $html .= "<div class=\"form-text\">{$help}</div>";
        }
        $html .= "</div>";
        
        return $html;
    }

    /**
     * Generate a table
     */
    public static function table($headers, $rows, $options = [])
    {
        $class = 'table ' . ($options['class'] ?? 'table-striped');
        $responsive = $options['responsive'] ?? true;
        
        $html = $responsive ? '<div class="table-responsive">' : '';
        $html .= "<table class=\"{$class}\">";
        
        // Headers
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= "<th>{$header}</th>";
        }
        $html .= '</tr></thead>';
        
        // Rows
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= "<td>{$cell}</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        
        $html .= '</table>';
        $html .= $responsive ? '</div>' : '';
        
        return $html;
    }

    /**
     * Generate a card
     */
    public static function card($title, $content, $options = [])
    {
        $headerClass = $options['headerClass'] ?? '';
        $bodyClass = $options['bodyClass'] ?? '';
        
        $html = '<div class="card">';
        if ($title) {
            $html .= "<div class=\"card-header {$headerClass}\">";
            $html .= "<h5 class=\"mb-0\">{$title}</h5>";
            $html .= '</div>';
        }
        $html .= "<div class=\"card-body {$bodyClass}\">";
        $html .= $content;
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate a modal
     */
    public static function modal($id, $title, $body, $footer = '', $options = [])
    {
        $size = $options['size'] ?? '';
        $modalClass = $size ? "modal-dialog modal-{$size}" : 'modal-dialog';
        
        $html = "<div class=\"modal fade\" id=\"{$id}\" tabindex=\"-1\">";
        $html .= "<div class=\"{$modalClass}\">";
        $html .= '<div class="modal-content">';
        
        // Header
        $html .= '<div class="modal-header">';
        $html .= "<h5 class=\"modal-title\">{$title}</h5>";
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
        $html .= '</div>';
        
        // Body
        $html .= "<div class=\"modal-body\">{$body}</div>";
        
        // Footer
        if ($footer) {
            $html .= "<div class=\"modal-footer\">{$footer}</div>";
        }
        
        $html .= '</div></div></div>';
        
        return $html;
    }

    /**
     * Generate status badge based on condition
     */
    public static function statusBadge($status, $mapping = [])
    {
        $defaultMapping = [
            'active' => ['text' => 'Aktif', 'type' => 'success'],
            'inactive' => ['text' => 'Tidak Aktif', 'type' => 'danger'],
            'kosong' => ['text' => 'Kosong', 'type' => 'success'],
            'terisi' => ['text' => 'Terisi', 'type' => 'info'],
            'lunas' => ['text' => 'Lunas', 'type' => 'success'],
            'terlambat' => ['text' => 'Terlambat', 'type' => 'danger'],
            'mendekati' => ['text' => 'Mendekati', 'type' => 'warning'],
        ];
        
        $mapping = array_merge($defaultMapping, $mapping);
        $config = $mapping[$status] ?? ['text' => $status, 'type' => 'secondary'];
        
        return self::badge($config['text'], $config['type']);
    }

    /**
     * Build HTML attributes string
     */
    private static function buildAttributes($attributes)
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            if ($value === '' && $key !== 'value') continue;
            if (is_bool($value)) {
                if ($value) $html .= " {$key}";
            } else {
                $html .= " {$key}=\"" . htmlspecialchars($value) . "\"";
            }
        }
        return $html;
    }

    /**
     * Format currency
     */
    public static function currency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format date
     */
    public static function date($date, $format = 'd/m/Y')
    {
        return date($format, strtotime($date));
    }

    /**
     * Generate action buttons group
     */
    public static function actionButtons($buttons, $size = 'sm')
    {
        $html = "<div class=\"btn-group btn-group-{$size}\">";
        foreach ($buttons as $button) {
            $class = $button['class'] ?? 'btn-outline-primary';
            $onclick = isset($button['onclick']) ? " onclick=\"{$button['onclick']}\"" : '';
            $title = isset($button['title']) ? " title=\"{$button['title']}\"" : '';
            $disabled = isset($button['disabled']) && $button['disabled'] ? ' disabled' : '';
            
            $html .= "<button type=\"button\" class=\"btn {$class}\"{$onclick}{$title}{$disabled}>";
            $html .= $button['icon'] ?? $button['text'];
            $html .= '</button>';
        }
        $html .= '</div>';
        
        return $html;
    }
}