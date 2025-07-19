<?php

namespace App\Helpers;

class TableHelper
{
    /**
     * Generate complete table with headers and rows
     */
    public static function create($headers, $rows = [], $options = [])
    {
        $tableOptions = [
            'class' => $options['class'] ?? 'table-striped',
            'responsive' => $options['responsive'] ?? true,
            'id' => $options['id'] ?? '',
            'striped' => $options['striped'] ?? true,
            'hover' => $options['hover'] ?? false,
            'bordered' => $options['bordered'] ?? false,
            'small' => $options['small'] ?? false,
            'dark' => $options['dark'] ?? false
        ];

        $html = '';
        
        // Responsive wrapper
        if ($tableOptions['responsive']) {
            $html .= '<div class="table-responsive">';
        }
        
        // Table opening tag
        $html .= self::openTable($tableOptions);
        
        // Headers
        if (!empty($headers)) {
            $html .= self::createHeader($headers, $options['headerOptions'] ?? []);
        }
        
        // Body with rows
        if (!empty($rows)) {
            $html .= self::createBody($rows, $options['bodyOptions'] ?? []);
        }
        
        // Footer if provided
        if (isset($options['footer'])) {
            $html .= self::createFooter($options['footer'], $options['footerOptions'] ?? []);
        }
        
        // Table closing tag
        $html .= self::closeTable();
        
        // Close responsive wrapper
        if ($tableOptions['responsive']) {
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Generate table opening tag
     */
    public static function openTable($options = [])
    {
        $classes = ['table'];
        
        if ($options['striped'] ?? true) $classes[] = 'table-striped';
        if ($options['hover'] ?? false) $classes[] = 'table-hover';
        if ($options['bordered'] ?? false) $classes[] = 'table-bordered';
        if ($options['small'] ?? false) $classes[] = 'table-sm';
        if ($options['dark'] ?? false) $classes[] = 'table-dark';
        
        // Add custom classes
        if (isset($options['class'])) {
            if (is_array($options['class'])) {
                $classes = array_merge($classes, $options['class']);
            } else {
                $classes[] = $options['class'];
            }
        }
        
        $attributes = [
            'class' => implode(' ', $classes)
        ];
        
        if (isset($options['id'])) $attributes['id'] = $options['id'];
        
        // Add custom attributes
        foreach ($options as $key => $value) {
            if (strpos($key, 'data-') === 0 || in_array($key, ['role', 'aria-label'])) {
                $attributes[$key] = $value;
            }
        }
        
        $attrs = self::buildAttributes($attributes);
        return "<table{$attrs}>";
    }
    
    /**
     * Generate table closing tag
     */
    public static function closeTable()
    {
        return '</table>';
    }
    
    /**
     * Generate table header
     */
    public static function createHeader($headers, $options = [])
    {
        $theadClass = $options['class'] ?? '';
        $dark = $options['dark'] ?? false;
        $light = $options['light'] ?? false;
        
        if ($dark) $theadClass .= ' table-dark';
        if ($light) $theadClass .= ' table-light';
        
        $theadAttrs = $theadClass ? " class=\"{$theadClass}\"" : '';
        
        $html = "<thead{$theadAttrs}>";
        $html .= self::createRow($headers, 'th', $options['rowOptions'] ?? []);
        $html .= '</thead>';
        
        return $html;
    }
    
    /**
     * Generate table body
     */
    public static function createBody($rows, $options = [])
    {
        $tbodyClass = $options['class'] ?? '';
        $tbodyAttrs = $tbodyClass ? " class=\"{$tbodyClass}\"" : '';
        
        $html = "<tbody{$tbodyAttrs}>";
        
        foreach ($rows as $rowIndex => $row) {
            $rowOptions = $options['rowOptions'] ?? [];
            
            // Add row-specific options if provided
            if (isset($options['rowCallback']) && is_callable($options['rowCallback'])) {
                $rowOptions = $options['rowCallback']($row, $rowIndex, $rowOptions);
            }
            
            $html .= self::createRow($row, 'td', $rowOptions);
        }
        
        $html .= '</tbody>';
        
        return $html;
    }
    
    /**
     * Generate table footer
     */
    public static function createFooter($footerData, $options = [])
    {
        $tfootClass = $options['class'] ?? '';
        $tfootAttrs = $tfootClass ? " class=\"{$tfootClass}\"" : '';
        
        $html = "<tfoot{$tfootAttrs}>";
        
        if (is_array($footerData[0] ?? null)) {
            // Multiple footer rows
            foreach ($footerData as $row) {
                $html .= self::createRow($row, 'td', $options['rowOptions'] ?? []);
            }
        } else {
            // Single footer row
            $html .= self::createRow($footerData, 'td', $options['rowOptions'] ?? []);
        }
        
        $html .= '</tfoot>';
        
        return $html;
    }
    
    /**
     * Generate table row
     */
    public static function createRow($cells, $cellType = 'td', $options = [])
    {
        $rowClass = $options['class'] ?? '';
        $rowAttrs = $rowClass ? " class=\"{$rowClass}\"" : '';
        
        // Add custom row attributes
        $customAttrs = '';
        foreach ($options as $key => $value) {
            if (strpos($key, 'data-') === 0 || in_array($key, ['id', 'onclick', 'role'])) {
                $customAttrs .= " {$key}=\"" . htmlspecialchars($value) . "\"";
            }
        }
        
        $html = "<tr{$rowAttrs}{$customAttrs}>";
        
        foreach ($cells as $cellIndex => $cell) {
            $cellOptions = $options['cellOptions'] ?? [];
            
            // Add cell-specific options if provided
            if (isset($options['cellCallback']) && is_callable($options['cellCallback'])) {
                $cellOptions = $options['cellCallback']($cell, $cellIndex, $cellOptions);
            }
            
            $html .= self::createCell($cell, $cellType, $cellOptions);
        }
        
        $html .= '</tr>';
        
        return $html;
    }
    
    /**
     * Generate table cell (td or th)
     */
    public static function createCell($content, $type = 'td', $options = [])
    {
        $cellClass = $options['class'] ?? '';
        $cellAttrs = $cellClass ? " class=\"{$cellClass}\"" : '';
        
        // Add cell attributes
        $customAttrs = '';
        foreach ($options as $key => $value) {
            if (in_array($key, ['colspan', 'rowspan', 'scope', 'id'])) {
                $customAttrs .= " {$key}=\"{$value}\"";
            } elseif (strpos($key, 'data-') === 0) {
                $customAttrs .= " {$key}=\"" . htmlspecialchars($value) . "\"";
            }
        }
        
        return "<{$type}{$cellAttrs}{$customAttrs}>{$content}</{$type}>";
    }
    
    /**
     * Generate action buttons column
     */
    public static function actionColumn($buttons, $options = [])
    {
        $size = $options['size'] ?? 'sm';
        $grouped = $options['grouped'] ?? true;
        
        if ($grouped) {
            $html = "<div class=\"btn-group btn-group-{$size}\">";
        } else {
            $html = '<div class="d-flex gap-1">';
        }
        
        foreach ($buttons as $button) {
            $class = 'btn ' . ($button['class'] ?? 'btn-outline-primary');
            $onclick = isset($button['onclick']) ? " onclick=\"{$button['onclick']}\"" : '';
            $title = isset($button['title']) ? " title=\"" . htmlspecialchars($button['title']) . "\"" : '';
            $disabled = isset($button['disabled']) && $button['disabled'] ? ' disabled' : '';
            $type = $button['type'] ?? 'button';
            
            $html .= "<button type=\"{$type}\" class=\"{$class}\"{$onclick}{$title}{$disabled}>";
            $html .= $button['icon'] ?? $button['text'] ?? '';
            $html .= '</button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate status badge for table cells
     */
    public static function statusBadge($status, $mapping = [])
    {
        $defaultMapping = [
            'active' => ['text' => 'Aktif', 'class' => 'bg-success'],
            'inactive' => ['text' => 'Tidak Aktif', 'class' => 'bg-danger'],
            'kosong' => ['text' => 'Kosong', 'class' => 'bg-success'],
            'terisi' => ['text' => 'Terisi', 'class' => 'bg-info'],
            'lunas' => ['text' => 'Lunas', 'class' => 'bg-success'],
            'cicil' => ['text' => 'Cicil', 'class' => 'bg-warning text-dark'],
            'belum_bayar' => ['text' => 'Belum Bayar', 'class' => 'bg-danger'],
            'terlambat' => ['text' => 'Terlambat', 'class' => 'bg-danger'],
            'mendekati' => ['text' => 'Mendekati', 'class' => 'bg-warning text-dark'],
        ];
        
        $mapping = array_merge($defaultMapping, $mapping);
        $config = $mapping[strtolower($status)] ?? ['text' => $status, 'class' => 'bg-secondary'];
        
        return "<span class=\"badge {$config['class']}\">{$config['text']}</span>";
    }
    
    /**
     * Generate currency column
     */
    public static function currencyColumn($amount, $options = [])
    {
        $class = $options['class'] ?? '';
        $prefix = $options['prefix'] ?? 'Rp ';
        $decimals = $options['decimals'] ?? 0;
        $decPoint = $options['decimal_point'] ?? ',';
        $thousandsSep = $options['thousands_separator'] ?? '.';
        
        $formatted = $prefix . number_format($amount, $decimals, $decPoint, $thousandsSep);
        
        if ($class) {
            return "<span class=\"{$class}\">{$formatted}</span>";
        }
        
        return $formatted;
    }
    
    /**
     * Generate building badge column
     */
    public static function buildingBadge($buildingNumber, $options = [])
    {
        $class = $options['class'] ?? 'bg-primary';
        return "<span class=\"badge {$class}\">Gedung {$buildingNumber}</span>";
    }
    
    /**
     * Generate date column
     */
    public static function dateColumn($date, $format = 'd/m/Y', $options = [])
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return $options['empty_text'] ?? '-';
        }
        
        $formatted = date($format, strtotime($date));
        $class = $options['class'] ?? '';
        
        if ($class) {
            return "<span class=\"{$class}\">{$formatted}</span>";
        }
        
        return $formatted;
    }
    
    /**
     * Generate empty state for tables
     */
    public static function emptyState($message, $icon = 'inbox', $colspan = 1)
    {
        $html = '<tr>';
        $html .= "<td colspan=\"{$colspan}\" class=\"text-center py-5\">";
        $html .= "<div>";
        $html .= "<i class=\"bi bi-{$icon} text-muted\" style=\"font-size: 4rem;\"></i>";
        $html .= "<h5 class=\"text-muted mt-3\">{$message}</h5>";
        $html .= "</div>";
        $html .= '</td>';
        $html .= '</tr>';
        
        return $html;
    }
    
    /**
     * Generate sortable header
     */
    public static function sortableHeader($text, $column, $currentSort = '', $currentDir = 'asc', $options = [])
    {
        $url = $options['url'] ?? '';
        $class = $options['class'] ?? '';
        
        $icon = '';
        $nextDir = 'asc';
        
        if ($currentSort === $column) {
            $class .= ' sorted';
            if ($currentDir === 'asc') {
                $icon = ' <i class="bi bi-arrow-up"></i>';
                $nextDir = 'desc';
            } else {
                $icon = ' <i class="bi bi-arrow-down"></i>';
                $nextDir = 'asc';
            }
        } else {
            $icon = ' <i class="bi bi-arrow-up-down text-muted"></i>';
        }
        
        $href = $url . (strpos($url, '?') ? '&' : '?') . "sort={$column}&dir={$nextDir}";
        $classAttr = $class ? " class=\"{$class}\"" : '';
        
        return "<a href=\"{$href}\"{$classAttr} style=\"text-decoration: none; color: inherit;\">{$text}{$icon}</a>";
    }
    
    /**
     * Generate pagination for tables
     */
    public static function pagination($currentPage, $totalPages, $url, $options = [])
    {
        if ($totalPages <= 1) return '';
        
        $range = $options['range'] ?? 2;
        $showFirstLast = $options['show_first_last'] ?? true;
        $class = $options['class'] ?? '';
        
        $html = "<nav aria-label=\"Table pagination\">";
        $html .= "<ul class=\"pagination {$class}\">";
        
        // First page
        if ($showFirstLast && $currentPage > 1) {
            $href = $url . (strpos($url, '?') ? '&' : '?') . 'page=1';
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$href}\">First</a></li>";
        }
        
        // Previous page
        if ($currentPage > 1) {
            $href = $url . (strpos($url, '?') ? '&' : '?') . 'page=' . ($currentPage - 1);
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$href}\">&laquo;</a></li>";
        }
        
        // Page numbers
        $start = max(1, $currentPage - $range);
        $end = min($totalPages, $currentPage + $range);
        
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = $i === $currentPage ? ' active' : '';
            $href = $url . (strpos($url, '?') ? '&' : '?') . "page={$i}";
            $html .= "<li class=\"page-item{$activeClass}\"><a class=\"page-link\" href=\"{$href}\">{$i}</a></li>";
        }
        
        // Next page
        if ($currentPage < $totalPages) {
            $href = $url . (strpos($url, '?') ? '&' : '?') . 'page=' . ($currentPage + 1);
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$href}\">&raquo;</a></li>";
        }
        
        // Last page
        if ($showFirstLast && $currentPage < $totalPages) {
            $href = $url . (strpos($url, '?') ? '&' : '?') . "page={$totalPages}";
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$href}\">Last</a></li>";
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Generate table from associative array data
     */
    public static function fromArray($data, $columns = [], $options = [])
    {
        if (empty($data)) {
            $emptyMessage = $options['empty_message'] ?? 'Tidak ada data';
            $emptyIcon = $options['empty_icon'] ?? 'inbox';
            $colspan = count($columns) ?: 1;
            
            $html = self::openTable($options);
            if (!empty($columns)) {
                $headers = array_values($columns);
                $html .= self::createHeader($headers);
            }
            $html .= '<tbody>';
            $html .= self::emptyState($emptyMessage, $emptyIcon, $colspan);
            $html .= '</tbody>';
            $html .= self::closeTable();
            
            return $options['responsive'] !== false ? '<div class="table-responsive">' . $html . '</div>' : $html;
        }
        
        // Extract headers from columns or first data row
        if (empty($columns)) {
            $columns = array_keys($data[0]);
            $headers = $columns;
        } else {
            $headers = array_values($columns);
        }
        
        // Extract rows
        $rows = [];
        foreach ($data as $item) {
            $row = [];
            foreach (array_keys($columns) as $key) {
                $value = $item[$key] ?? '';
                
                // Apply column formatters if provided
                if (isset($options['formatters'][$key]) && is_callable($options['formatters'][$key])) {
                    $value = $options['formatters'][$key]($value, $item);
                }
                
                $row[] = $value;
            }
            $rows[] = $row;
        }
        
        return self::create($headers, $rows, $options);
    }
    
    /**
     * Generate advanced table with search, sort, and pagination
     */
    public static function advanced($data, $columns, $options = [])
    {
        $tableId = $options['id'] ?? 'advanced-table-' . uniqid();
        $searchable = $options['searchable'] ?? true;
        $sortable = $options['sortable'] ?? true;
        $paginated = $options['paginated'] ?? true;
        
        $html = '';
        
        // Search box
        if ($searchable) {
            $html .= '<div class="row mb-3">';
            $html .= '<div class="col-md-6">';
            $html .= '<div class="input-group">';
            $html .= '<span class="input-group-text"><i class="bi bi-search"></i></span>';
            $html .= "<input type=\"text\" class=\"form-control\" id=\"{$tableId}-search\" placeholder=\"Cari data...\">";
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        // Table
        $tableOptions = array_merge($options, [
            'id' => $tableId,
            'class' => ($options['class'] ?? '') . ' advanced-table'
        ]);
        
        $html .= self::fromArray($data, $columns, $tableOptions);
        
        // Pagination placeholder
        if ($paginated) {
            $html .= "<div id=\"{$tableId}-pagination\" class=\"d-flex justify-content-center mt-3\"></div>";
        }
        
        // JavaScript for advanced features
        if ($searchable || $sortable || $paginated) {
            $html .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Advanced table functionality for {$tableId}
                    // TODO: Implement search, sort, and pagination JavaScript
                });
            </script>";
        }
        
        return $html;
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
}