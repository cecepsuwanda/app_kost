<?php

namespace App\Helpers;

class BootstrapHelper
{
    /**
     * Generate alert component
     */
    public static function alert($message, $type = 'info', $options = [])
    {
        $dismissible = $options['dismissible'] ?? false;
        $class = "alert alert-{$type}";
        $id = $options['id'] ?? '';
        
        if ($dismissible) {
            $class .= ' alert-dismissible fade show';
        }
        
        $attributes = [
            'class' => $class,
            'role' => 'alert'
        ];
        
        if ($id) $attributes['id'] = $id;
        
        $attrs = self::buildAttributes($attributes);
        
        $html = "<div{$attrs}>";
        $html .= $message;
        
        if ($dismissible) {
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate badge component
     */
    public static function badge($text, $type = 'primary', $options = [])
    {
        $pill = $options['pill'] ?? false;
        $class = "badge bg-{$type}";
        
        if ($pill) {
            $class .= ' rounded-pill';
        }
        
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        
        $attributes = ['class' => $class];
        
        // Add custom attributes
        foreach ($options as $key => $value) {
            if (strpos($key, 'data-') === 0 || in_array($key, ['id', 'title'])) {
                $attributes[$key] = $value;
            }
        }
        
        $attrs = self::buildAttributes($attributes);
        return "<span{$attrs}>{$text}</span>";
    }
    
    /**
     * Generate button component
     */
    public static function button($text, $options = [])
    {
        $type = $options['type'] ?? 'button';
        $variant = $options['variant'] ?? 'primary';
        $size = $options['size'] ?? '';
        $outline = $options['outline'] ?? false;
        $disabled = $options['disabled'] ?? false;
        $block = $options['block'] ?? false;
        
        $class = 'btn';
        
        if ($outline) {
            $class .= " btn-outline-{$variant}";
        } else {
            $class .= " btn-{$variant}";
        }
        
        if ($size) {
            $class .= " btn-{$size}";
        }
        
        if ($block) {
            $class .= ' w-100';
        }
        
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        
        $attributes = [
            'type' => $type,
            'class' => $class
        ];
        
        if ($disabled) $attributes['disabled'] = '';
        if (isset($options['id'])) $attributes['id'] = $options['id'];
        if (isset($options['onclick'])) $attributes['onclick'] = $options['onclick'];
        if (isset($options['data-bs-toggle'])) $attributes['data-bs-toggle'] = $options['data-bs-toggle'];
        if (isset($options['data-bs-target'])) $attributes['data-bs-target'] = $options['data-bs-target'];
        
        // Add custom attributes
        foreach ($options as $key => $value) {
            if (strpos($key, 'data-') === 0 && !isset($attributes[$key])) {
                $attributes[$key] = $value;
            }
        }
        
        $attrs = self::buildAttributes($attributes);
        return "<button{$attrs}>{$text}</button>";
    }
    
    /**
     * Generate link button
     */
    public static function linkButton($text, $href, $options = [])
    {
        $variant = $options['variant'] ?? 'primary';
        $size = $options['size'] ?? '';
        $outline = $options['outline'] ?? false;
        $block = $options['block'] ?? false;
        
        $class = 'btn';
        
        if ($outline) {
            $class .= " btn-outline-{$variant}";
        } else {
            $class .= " btn-{$variant}";
        }
        
        if ($size) {
            $class .= " btn-{$size}";
        }
        
        if ($block) {
            $class .= ' w-100';
        }
        
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        
        $attributes = [
            'href' => $href,
            'class' => $class
        ];
        
        if (isset($options['id'])) $attributes['id'] = $options['id'];
        if (isset($options['target'])) $attributes['target'] = $options['target'];
        if (isset($options['role'])) $attributes['role'] = $options['role'];
        
        $attrs = self::buildAttributes($attributes);
        return "<a{$attrs}>{$text}</a>";
    }
    
    /**
     * Generate button group
     */
    public static function buttonGroup($buttons, $options = [])
    {
        $size = $options['size'] ?? '';
        $vertical = $options['vertical'] ?? false;
        $toolbar = $options['toolbar'] ?? false;
        
        $class = $vertical ? 'btn-group-vertical' : 'btn-group';
        
        if ($toolbar) {
            $class = 'btn-toolbar';
        }
        
        if ($size && !$toolbar) {
            $class .= " btn-group-{$size}";
        }
        
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        
        $attributes = [
            'class' => $class,
            'role' => 'group'
        ];
        
        if (isset($options['aria-label'])) {
            $attributes['aria-label'] = $options['aria-label'];
        }
        
        $attrs = self::buildAttributes($attributes);
        
        $html = "<div{$attrs}>";
        
        foreach ($buttons as $button) {
            if (is_string($button)) {
                $html .= $button;
            } elseif (is_array($button)) {
                $text = $button['text'] ?? '';
                $buttonOptions = $button;
                unset($buttonOptions['text']);
                $html .= self::button($text, $buttonOptions);
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate card component
     */
    public static function card($content, $options = [])
    {
        $title = $options['title'] ?? '';
        $header = $options['header'] ?? '';
        $footer = $options['footer'] ?? '';
        $headerClass = $options['header_class'] ?? '';
        $bodyClass = $options['body_class'] ?? '';
        $footerClass = $options['footer_class'] ?? '';
        $cardClass = $options['card_class'] ?? '';
        $border = $options['border'] ?? '';
        $textColor = $options['text_color'] ?? '';
        $bgColor = $options['bg_color'] ?? '';
        
        $class = 'card';
        
        if ($border) $class .= " border-{$border}";
        if ($textColor) $class .= " text-{$textColor}";
        if ($bgColor) $class .= " bg-{$bgColor}";
        if ($cardClass) $class .= " {$cardClass}";
        
        $html = "<div class=\"{$class}\">";
        
        // Header
        if ($header || $title) {
            $headerClassFull = "card-header {$headerClass}";
            $html .= "<div class=\"{$headerClassFull}\">";
            
            if ($title) {
                $html .= "<h5 class=\"card-title mb-0\">{$title}</h5>";
            } else {
                $html .= $header;
            }
            
            $html .= '</div>';
        }
        
        // Body
        $bodyClassFull = "card-body {$bodyClass}";
        $html .= "<div class=\"{$bodyClassFull}\">";
        $html .= $content;
        $html .= '</div>';
        
        // Footer
        if ($footer) {
            $footerClassFull = "card-footer {$footerClass}";
            $html .= "<div class=\"{$footerClassFull}\">";
            $html .= $footer;
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate modal component
     */
    public static function modal($id, $title, $body, $options = [])
    {
        $size = $options['size'] ?? '';
        $footer = $options['footer'] ?? '';
        $backdrop = $options['backdrop'] ?? 'true';
        $keyboard = $options['keyboard'] ?? 'true';
        $fade = $options['fade'] ?? true;
        $centered = $options['centered'] ?? false;
        $scrollable = $options['scrollable'] ?? false;
        $fullscreen = $options['fullscreen'] ?? false;
        
        $modalClass = 'modal';
        if ($fade) $modalClass .= ' fade';
        
        $dialogClass = 'modal-dialog';
        if ($size) $dialogClass .= " modal-{$size}";
        if ($centered) $dialogClass .= ' modal-dialog-centered';
        if ($scrollable) $dialogClass .= ' modal-dialog-scrollable';
        if ($fullscreen) {
            if ($fullscreen === true) {
                $dialogClass .= ' modal-fullscreen';
            } else {
                $dialogClass .= " modal-fullscreen-{$fullscreen}";
            }
        }
        
        $modalAttrs = [
            'class' => $modalClass,
            'id' => $id,
            'tabindex' => '-1',
            'data-bs-backdrop' => $backdrop,
            'data-bs-keyboard' => $keyboard
        ];
        
        $attrs = self::buildAttributes($modalAttrs);
        
        $html = "<div{$attrs}>";
        $html .= "<div class=\"{$dialogClass}\">";
        $html .= '<div class="modal-content">';
        
        // Header
        $html .= '<div class="modal-header">';
        $html .= "<h5 class=\"modal-title\">{$title}</h5>";
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
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
     * Generate modal with form
     */
    public static function modalForm($id, $title, $formContent, $options = [])
    {
        $action = $options['action'] ?? '';
        $method = $options['method'] ?? 'POST';
        $submitText = $options['submit_text'] ?? 'Simpan';
        $cancelText = $options['cancel_text'] ?? 'Batal';
        $submitClass = $options['submit_class'] ?? 'btn-primary';
        
        // Form wrapper
        $form = \App\Helpers\FormHelper::open($action, ['method' => $method]);
        $form .= $formContent;
        $form .= \App\Helpers\FormHelper::close();
        
        // Footer buttons
        $footer = self::button($cancelText, [
            'variant' => 'secondary',
            'data-bs-dismiss' => 'modal'
        ]);
        $footer .= ' ';
        $footer .= self::button($submitText, [
            'type' => 'submit',
            'variant' => str_replace('btn-', '', $submitClass)
        ]);
        
        $modalOptions = array_merge($options, ['footer' => $footer]);
        
        return self::modal($id, $title, $form, $modalOptions);
    }
    
    /**
     * Generate breadcrumb component
     */
    public static function breadcrumb($items, $options = [])
    {
        $class = 'breadcrumb';
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        
        $html = "<nav aria-label=\"breadcrumb\">";
        $html .= "<ol class=\"{$class}\">";
        
        $lastIndex = count($items) - 1;
        
        foreach ($items as $index => $item) {
            $isActive = $index === $lastIndex;
            $itemClass = 'breadcrumb-item';
            
            if ($isActive) {
                $itemClass .= ' active';
            }
            
            $html .= "<li class=\"{$itemClass}\"";
            
            if ($isActive) {
                $html .= ' aria-current="page"';
            }
            
            $html .= '>';
            
            if (!$isActive && isset($item['url'])) {
                $html .= "<a href=\"{$item['url']}\">{$item['text']}</a>";
            } else {
                $html .= $item['text'] ?? $item;
            }
            
            $html .= '</li>';
        }
        
        $html .= '</ol></nav>';
        
        return $html;
    }
    
    /**
     * Generate pagination component
     */
    public static function pagination($currentPage, $totalPages, $baseUrl, $options = [])
    {
        if ($totalPages <= 1) return '';
        
        $size = $options['size'] ?? '';
        $maxLinks = $options['max_links'] ?? 5;
        $showFirstLast = $options['show_first_last'] ?? true;
        $prevText = $options['prev_text'] ?? '&laquo;';
        $nextText = $options['next_text'] ?? '&raquo;';
        $firstText = $options['first_text'] ?? 'First';
        $lastText = $options['last_text'] ?? 'Last';
        
        $class = 'pagination';
        if ($size) $class .= " pagination-{$size}";
        
        $html = "<nav aria-label=\"Page navigation\">";
        $html .= "<ul class=\"{$class}\">";
        
        // First page
        if ($showFirstLast && $currentPage > 1) {
            $url = $baseUrl . (strpos($baseUrl, '?') ? '&' : '?') . 'page=1';
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$url}\">{$firstText}</a></li>";
        }
        
        // Previous page
        if ($currentPage > 1) {
            $url = $baseUrl . (strpos($baseUrl, '?') ? '&' : '?') . 'page=' . ($currentPage - 1);
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$url}\">{$prevText}</a></li>";
        }
        
        // Page numbers
        $start = max(1, $currentPage - floor($maxLinks / 2));
        $end = min($totalPages, $start + $maxLinks - 1);
        
        if ($end - $start + 1 < $maxLinks) {
            $start = max(1, $end - $maxLinks + 1);
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = $i === $currentPage ? ' active' : '';
            $url = $baseUrl . (strpos($baseUrl, '?') ? '&' : '?') . "page={$i}";
            $html .= "<li class=\"page-item{$activeClass}\"><a class=\"page-link\" href=\"{$url}\">{$i}</a></li>";
        }
        
        // Next page
        if ($currentPage < $totalPages) {
            $url = $baseUrl . (strpos($baseUrl, '?') ? '&' : '?') . 'page=' . ($currentPage + 1);
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$url}\">{$nextText}</a></li>";
        }
        
        // Last page
        if ($showFirstLast && $currentPage < $totalPages) {
            $url = $baseUrl . (strpos($baseUrl, '?') ? '&' : '?') . "page={$totalPages}";
            $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$url}\">{$lastText}</a></li>";
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
    
    /**
     * Generate progress bar component
     */
    public static function progressBar($value, $options = [])
    {
        $max = $options['max'] ?? 100;
        $variant = $options['variant'] ?? '';
        $striped = $options['striped'] ?? false;
        $animated = $options['animated'] ?? false;
        $label = $options['label'] ?? '';
        $height = $options['height'] ?? '';
        
        $percentage = ($value / $max) * 100;
        
        $progressClass = 'progress';
        $barClass = 'progress-bar';
        
        if ($variant) $barClass .= " bg-{$variant}";
        if ($striped) $barClass .= ' progress-bar-striped';
        if ($animated) $barClass .= ' progress-bar-animated';
        
        $progressStyle = $height ? "height: {$height};" : '';
        $barStyle = "width: {$percentage}%";
        
        $html = "<div class=\"{$progressClass}\"" . ($progressStyle ? " style=\"{$progressStyle}\"" : '') . ">";
        $html .= "<div class=\"{$barClass}\" role=\"progressbar\" style=\"{$barStyle}\" ";
        $html .= "aria-valuenow=\"{$value}\" aria-valuemin=\"0\" aria-valuemax=\"{$max}\">";
        
        if ($label) {
            $html .= $label;
        }
        
        $html .= '</div></div>';
        
        return $html;
    }
    
    /**
     * Generate spinner component
     */
    public static function spinner($options = [])
    {
        $type = $options['type'] ?? 'border'; // border or grow
        $size = $options['size'] ?? ''; // sm
        $variant = $options['variant'] ?? '';
        $text = $options['text'] ?? 'Loading...';
        $inline = $options['inline'] ?? false;
        
        $class = "spinner-{$type}";
        
        if ($size) $class .= " spinner-{$type}-{$size}";
        if ($variant) $class .= " text-{$variant}";
        
        $html = "<div class=\"{$class}\" role=\"status\">";
        
        if (!$inline) {
            $html .= "<span class=\"visually-hidden\">{$text}</span>";
        }
        
        $html .= '</div>';
        
        if ($inline && $text) {
            $html .= " {$text}";
        }
        
        return $html;
    }
    
    /**
     * Generate tooltip trigger
     */
    public static function tooltip($content, $text, $options = [])
    {
        $placement = $options['placement'] ?? 'top';
        $trigger = $options['trigger'] ?? 'hover';
        $html = $options['html'] ?? 'false';
        
        $attributes = [
            'data-bs-toggle' => 'tooltip',
            'data-bs-placement' => $placement,
            'data-bs-trigger' => $trigger,
            'data-bs-html' => $html,
            'title' => $content
        ];
        
        if (isset($options['class'])) {
            $attributes['class'] = $options['class'];
        }
        
        $attrs = self::buildAttributes($attributes);
        
        return "<span{$attrs}>{$text}</span>";
    }
    
    /**
     * Generate popover trigger
     */
    public static function popover($title, $content, $text, $options = [])
    {
        $placement = $options['placement'] ?? 'top';
        $trigger = $options['trigger'] ?? 'click';
        $html = $options['html'] ?? 'false';
        
        $attributes = [
            'data-bs-toggle' => 'popover',
            'data-bs-placement' => $placement,
            'data-bs-trigger' => $trigger,
            'data-bs-html' => $html,
            'data-bs-title' => $title,
            'data-bs-content' => $content
        ];
        
        if (isset($options['class'])) {
            $attributes['class'] = $options['class'];
        }
        
        $attrs = self::buildAttributes($attributes);
        
        return "<span{$attrs}>{$text}</span>";
    }
    
    /**
     * Generate dropdown component
     */
    public static function dropdown($buttonText, $items, $options = [])
    {
        $variant = $options['variant'] ?? 'primary';
        $size = $options['size'] ?? '';
        $split = $options['split'] ?? false;
        $direction = $options['direction'] ?? ''; // up, end, start
        $alignment = $options['alignment'] ?? ''; // end
        
        $wrapperClass = 'dropdown';
        if ($direction) $wrapperClass = "drop{$direction}";
        
        $buttonClass = "btn btn-{$variant} dropdown-toggle";
        if ($size) $buttonClass .= " btn-{$size}";
        
        $menuClass = 'dropdown-menu';
        if ($alignment) $menuClass .= " dropdown-menu-{$alignment}";
        
        $buttonId = 'dropdown' . uniqid();
        
        $html = "<div class=\"{$wrapperClass}\">";
        
        if ($split) {
            // Split button dropdown
            $html .= "<button type=\"button\" class=\"btn btn-{$variant}" . ($size ? " btn-{$size}" : '') . "\">{$buttonText}</button>";
            $html .= "<button type=\"button\" class=\"btn btn-{$variant} dropdown-toggle dropdown-toggle-split" . ($size ? " btn-{$size}" : '') . "\" ";
            $html .= "data-bs-toggle=\"dropdown\" aria-expanded=\"false\">";
            $html .= '<span class="visually-hidden">Toggle Dropdown</span>';
            $html .= '</button>';
        } else {
            $html .= "<button class=\"{$buttonClass}\" type=\"button\" id=\"{$buttonId}\" ";
            $html .= "data-bs-toggle=\"dropdown\" aria-expanded=\"false\">";
            $html .= $buttonText;
            $html .= '</button>';
        }
        
        $html .= "<ul class=\"{$menuClass}\" aria-labelledby=\"{$buttonId}\">";
        
        foreach ($items as $item) {
            if ($item === 'divider') {
                $html .= '<li><hr class="dropdown-divider"></li>';
            } elseif (isset($item['header'])) {
                $html .= "<li><h6 class=\"dropdown-header\">{$item['header']}</h6></li>";
            } else {
                $itemClass = 'dropdown-item';
                if (isset($item['disabled']) && $item['disabled']) {
                    $itemClass .= ' disabled';
                }
                
                if (isset($item['href'])) {
                    $html .= "<li><a class=\"{$itemClass}\" href=\"{$item['href']}\">{$item['text']}</a></li>";
                } else {
                    $onclick = isset($item['onclick']) ? " onclick=\"{$item['onclick']}\"" : '';
                    $html .= "<li><button class=\"{$itemClass}\" type=\"button\"{$onclick}>{$item['text']}</button></li>";
                }
            }
        }
        
        $html .= '</ul></div>';
        
        return $html;
    }
    
    /**
     * Generate collapse component
     */
    public static function collapse($trigger, $content, $options = [])
    {
        $id = $options['id'] ?? 'collapse' . uniqid();
        $show = $options['show'] ?? false;
        $triggerClass = $options['trigger_class'] ?? 'btn btn-primary';
        
        $collapseClass = 'collapse';
        if ($show) $collapseClass .= ' show';
        
        $html = "<button class=\"{$triggerClass}\" type=\"button\" data-bs-toggle=\"collapse\" ";
        $html .= "data-bs-target=\"#{$id}\" aria-expanded=\"" . ($show ? 'true' : 'false') . "\" ";
        $html .= "aria-controls=\"{$id}\">";
        $html .= $trigger;
        $html .= '</button>';
        
        $html .= "<div class=\"{$collapseClass}\" id=\"{$id}\">";
        $html .= "<div class=\"card card-body\">";
        $html .= $content;
        $html .= '</div></div>';
        
        return $html;
    }
    
    /**
     * Generate accordion component
     */
    public static function accordion($items, $options = [])
    {
        $id = $options['id'] ?? 'accordion' . uniqid();
        $flush = $options['flush'] ?? false;
        
        $accordionClass = 'accordion';
        if ($flush) $accordionClass .= ' accordion-flush';
        
        $html = "<div class=\"{$accordionClass}\" id=\"{$id}\">";
        
        foreach ($items as $index => $item) {
            $itemId = "{$id}-item-{$index}";
            $headingId = "{$id}-heading-{$index}";
            $collapseId = "{$id}-collapse-{$index}";
            
            $isOpen = $item['open'] ?? false;
            $collapseClass = 'accordion-collapse collapse';
            if ($isOpen) $collapseClass .= ' show';
            
            $html .= "<div class=\"accordion-item\">";
            
            // Header
            $html .= "<h2 class=\"accordion-header\" id=\"{$headingId}\">";
            $html .= "<button class=\"accordion-button" . ($isOpen ? '' : ' collapsed') . "\" ";
            $html .= "type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#{$collapseId}\" ";
            $html .= "aria-expanded=\"" . ($isOpen ? 'true' : 'false') . "\" aria-controls=\"{$collapseId}\">";
            $html .= $item['title'];
            $html .= '</button>';
            $html .= '</h2>';
            
            // Body
            $html .= "<div id=\"{$collapseId}\" class=\"{$collapseClass}\" ";
            $html .= "aria-labelledby=\"{$headingId}\" data-bs-parent=\"#{$id}\">";
            $html .= "<div class=\"accordion-body\">";
            $html .= $item['content'];
            $html .= '</div></div>';
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate toast component
     */
    public static function toast($title, $message, $options = [])
    {
        $id = $options['id'] ?? 'toast' . uniqid();
        $delay = $options['delay'] ?? 5000;
        $autohide = $options['autohide'] ?? 'true';
        $variant = $options['variant'] ?? '';
        $time = $options['time'] ?? date('H:i');
        
        $toastClass = 'toast';
        if ($variant) $toastClass .= " text-bg-{$variant}";
        
        $html = "<div class=\"{$toastClass}\" id=\"{$id}\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\" ";
        $html .= "data-bs-delay=\"{$delay}\" data-bs-autohide=\"{$autohide}\">";
        
        $html .= '<div class="toast-header">';
        $html .= "<strong class=\"me-auto\">{$title}</strong>";
        $html .= "<small>{$time}</small>";
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>';
        $html .= '</div>';
        
        $html .= "<div class=\"toast-body\">{$message}</div>";
        $html .= '</div>';
        
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