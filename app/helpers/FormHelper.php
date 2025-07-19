<?php

namespace App\Helpers;

class FormHelper
{
    /**
     * Generate form opening tag
     */
    public static function open($action = '', $options = [])
    {
        $method = strtoupper($options['method'] ?? 'POST');
        $class = $options['class'] ?? '';
        $id = $options['id'] ?? '';
        $enctype = $options['enctype'] ?? '';
        
        $attributes = [
            'method' => $method,
            'action' => $action
        ];
        
        if ($class) $attributes['class'] = $class;
        if ($id) $attributes['id'] = $id;
        if ($enctype) $attributes['enctype'] = $enctype;
        
        $attrs = self::buildAttributes($attributes);
        return "<form{$attrs}>";
    }
    
    /**
     * Generate form closing tag
     */
    public static function close()
    {
        return '</form>';
    }
    
    /**
     * Generate text input field
     */
    public static function text($name, $value = '', $options = [])
    {
        return self::input('text', $name, $value, $options);
    }
    
    /**
     * Generate password input field
     */
    public static function password($name, $options = [])
    {
        return self::input('password', $name, '', $options);
    }
    
    /**
     * Generate email input field
     */
    public static function email($name, $value = '', $options = [])
    {
        return self::input('email', $name, $value, $options);
    }
    
    /**
     * Generate number input field
     */
    public static function number($name, $value = '', $options = [])
    {
        return self::input('number', $name, $value, $options);
    }
    
    /**
     * Generate date input field
     */
    public static function date($name, $value = '', $options = [])
    {
        return self::input('date', $name, $value, $options);
    }
    
    /**
     * Generate datetime-local input field
     */
    public static function datetime($name, $value = '', $options = [])
    {
        return self::input('datetime-local', $name, $value, $options);
    }
    
    /**
     * Generate month input field
     */
    public static function month($name, $value = '', $options = [])
    {
        return self::input('month', $name, $value, $options);
    }
    
    /**
     * Generate tel input field
     */
    public static function tel($name, $value = '', $options = [])
    {
        return self::input('tel', $name, $value, $options);
    }
    
    /**
     * Generate url input field
     */
    public static function url($name, $value = '', $options = [])
    {
        return self::input('url', $name, $value, $options);
    }
    
    /**
     * Generate hidden input field
     */
    public static function hidden($name, $value = '', $options = [])
    {
        return self::input('hidden', $name, $value, $options);
    }
    
    /**
     * Generate file input field
     */
    public static function file($name, $options = [])
    {
        return self::input('file', $name, '', $options);
    }
    
    /**
     * Generate generic input field
     */
    public static function input($type, $name, $value = '', $options = [])
    {
        $class = 'form-control ' . ($options['class'] ?? '');
        $placeholder = $options['placeholder'] ?? '';
        $required = isset($options['required']) && $options['required'];
        $readonly = isset($options['readonly']) && $options['readonly'];
        $disabled = isset($options['disabled']) && $options['disabled'];
        $autofocus = isset($options['autofocus']) && $options['autofocus'];
        $min = $options['min'] ?? '';
        $max = $options['max'] ?? '';
        $step = $options['step'] ?? '';
        $id = $options['id'] ?? $name;
        
        $attributes = [
            'type' => $type,
            'name' => $name,
            'id' => $id,
            'class' => trim($class),
            'value' => $value,
            'placeholder' => $placeholder
        ];
        
        if ($min !== '') $attributes['min'] = $min;
        if ($max !== '') $attributes['max'] = $max;
        if ($step !== '') $attributes['step'] = $step;
        if ($required) $attributes['required'] = '';
        if ($readonly) $attributes['readonly'] = '';
        if ($disabled) $attributes['disabled'] = '';
        if ($autofocus) $attributes['autofocus'] = '';
        
        // Add custom attributes
        foreach ($options as $key => $val) {
            if (!in_array($key, ['class', 'placeholder', 'required', 'readonly', 'disabled', 'autofocus', 'min', 'max', 'step', 'id']) && strpos($key, 'data-') === 0) {
                $attributes[$key] = $val;
            }
        }
        
        $attrs = self::buildAttributes($attributes);
        return "<input{$attrs}>";
    }
    
    /**
     * Generate textarea field
     */
    public static function textarea($name, $value = '', $options = [])
    {
        $class = 'form-control ' . ($options['class'] ?? '');
        $placeholder = $options['placeholder'] ?? '';
        $required = isset($options['required']) && $options['required'];
        $readonly = isset($options['readonly']) && $options['readonly'];
        $disabled = isset($options['disabled']) && $options['disabled'];
        $rows = $options['rows'] ?? 3;
        $cols = $options['cols'] ?? '';
        $id = $options['id'] ?? $name;
        
        $attributes = [
            'name' => $name,
            'id' => $id,
            'class' => trim($class),
            'placeholder' => $placeholder,
            'rows' => $rows
        ];
        
        if ($cols !== '') $attributes['cols'] = $cols;
        if ($required) $attributes['required'] = '';
        if ($readonly) $attributes['readonly'] = '';
        if ($disabled) $attributes['disabled'] = '';
        
        $attrs = self::buildAttributes($attributes);
        return "<textarea{$attrs}>" . htmlspecialchars($value) . "</textarea>";
    }
    
    /**
     * Generate select dropdown field
     */
    public static function select($name, $options = [], $selected = '', $attributes = [])
    {
        $class = 'form-select ' . ($attributes['class'] ?? '');
        $required = isset($attributes['required']) && $attributes['required'];
        $disabled = isset($attributes['disabled']) && $attributes['disabled'];
        $multiple = isset($attributes['multiple']) && $attributes['multiple'];
        $id = $attributes['id'] ?? $name;
        
        $selectAttributes = [
            'name' => $multiple ? $name . '[]' : $name,
            'id' => $id,
            'class' => trim($class)
        ];
        
        if ($required) $selectAttributes['required'] = '';
        if ($disabled) $selectAttributes['disabled'] = '';
        if ($multiple) $selectAttributes['multiple'] = '';
        
        // Add custom attributes
        foreach ($attributes as $key => $val) {
            if (!in_array($key, ['class', 'required', 'disabled', 'multiple', 'id']) && strpos($key, 'data-') === 0) {
                $selectAttributes[$key] = $val;
            }
        }
        
        $attrs = self::buildAttributes($selectAttributes);
        
        $html = "<select{$attrs}>";
        foreach ($options as $value => $text) {
            $selectedAttr = '';
            if ($multiple && is_array($selected)) {
                $selectedAttr = in_array($value, $selected) ? ' selected' : '';
            } else {
                $selectedAttr = ($value == $selected) ? ' selected' : '';
            }
            $html .= "<option value=\"" . htmlspecialchars($value) . "\"{$selectedAttr}>" . htmlspecialchars($text) . "</option>";
        }
        $html .= "</select>";
        
        return $html;
    }
    
    /**
     * Generate checkbox field
     */
    public static function checkbox($name, $value = '1', $checked = false, $options = [])
    {
        $class = 'form-check-input ' . ($options['class'] ?? '');
        $disabled = isset($options['disabled']) && $options['disabled'];
        $id = $options['id'] ?? $name;
        
        $attributes = [
            'type' => 'checkbox',
            'name' => $name,
            'id' => $id,
            'class' => trim($class),
            'value' => $value
        ];
        
        if ($checked) $attributes['checked'] = '';
        if ($disabled) $attributes['disabled'] = '';
        
        $attrs = self::buildAttributes($attributes);
        return "<input{$attrs}>";
    }
    
    /**
     * Generate radio button field
     */
    public static function radio($name, $value, $checked = false, $options = [])
    {
        $class = 'form-check-input ' . ($options['class'] ?? '');
        $disabled = isset($options['disabled']) && $options['disabled'];
        $id = $options['id'] ?? $name . '_' . $value;
        
        $attributes = [
            'type' => 'radio',
            'name' => $name,
            'id' => $id,
            'class' => trim($class),
            'value' => $value
        ];
        
        if ($checked) $attributes['checked'] = '';
        if ($disabled) $attributes['disabled'] = '';
        
        $attrs = self::buildAttributes($attributes);
        return "<input{$attrs}>";
    }
    
    /**
     * Generate label field
     */
    public static function label($for, $text, $options = [])
    {
        $class = 'form-label ' . ($options['class'] ?? '');
        $required = isset($options['required']) && $options['required'];
        
        $attributes = [
            'for' => $for,
            'class' => trim($class)
        ];
        
        $attrs = self::buildAttributes($attributes);
        $requiredMark = $required ? ' <span class="text-danger">*</span>' : '';
        return "<label{$attrs}>{$text}{$requiredMark}</label>";
    }
    
    /**
     * Generate button
     */
    public static function button($text, $options = [])
    {
        $type = $options['type'] ?? 'button';
        $class = 'btn ' . ($options['class'] ?? 'btn-primary');
        $disabled = isset($options['disabled']) && $options['disabled'];
        $onclick = $options['onclick'] ?? '';
        $id = $options['id'] ?? '';
        
        $attributes = [
            'type' => $type,
            'class' => $class
        ];
        
        if ($id) $attributes['id'] = $id;
        if ($disabled) $attributes['disabled'] = '';
        if ($onclick) $attributes['onclick'] = $onclick;
        
        // Add custom attributes
        foreach ($options as $key => $val) {
            if (!in_array($key, ['type', 'class', 'disabled', 'onclick', 'id']) && strpos($key, 'data-') === 0) {
                $attributes[$key] = $val;
            }
        }
        
        $attrs = self::buildAttributes($attributes);
        return "<button{$attrs}>{$text}</button>";
    }
    
    /**
     * Generate submit button
     */
    public static function submit($text = 'Submit', $options = [])
    {
        $options['type'] = 'submit';
        $options['class'] = $options['class'] ?? 'btn-primary';
        return self::button($text, $options);
    }
    
    /**
     * Generate form group with label and input
     */
    public static function group($label, $input, $options = [])
    {
        $colClass = $options['col'] ?? 'mb-3';
        $help = $options['help'] ?? '';
        $required = isset($options['required']) && $options['required'];
        
        $html = "<div class=\"{$colClass}\">";
        if ($label) {
            $labelOptions = ['required' => $required, 'class' => $options['label_class'] ?? ''];
            $html .= self::label($options['input_id'] ?? '', $label, $labelOptions);
        }
        $html .= $input;
        if ($help) {
            $html .= "<div class=\"form-text\">{$help}</div>";
        }
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Generate input group with prefix/suffix
     */
    public static function inputGroup($input, $options = [])
    {
        $prefix = $options['prefix'] ?? '';
        $suffix = $options['suffix'] ?? '';
        $class = $options['class'] ?? '';
        
        $html = "<div class=\"input-group {$class}\">";
        
        if ($prefix) {
            $html .= "<span class=\"input-group-text\">{$prefix}</span>";
        }
        
        $html .= $input;
        
        if ($suffix) {
            $html .= "<span class=\"input-group-text\">{$suffix}</span>";
        }
        
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Generate form check (checkbox/radio with label)
     */
    public static function check($input, $label, $options = [])
    {
        $class = $options['class'] ?? '';
        $inline = isset($options['inline']) && $options['inline'];
        $disabled = isset($options['disabled']) && $options['disabled'];
        
        $wrapperClass = 'form-check';
        if ($inline) $wrapperClass .= ' form-check-inline';
        if ($class) $wrapperClass .= ' ' . $class;
        
        $labelClass = 'form-check-label';
        if ($disabled) $labelClass .= ' disabled';
        
        $html = "<div class=\"{$wrapperClass}\">";
        $html .= $input;
        $html .= "<label class=\"{$labelClass}\" for=\"" . ($options['input_id'] ?? '') . "\">{$label}</label>";
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Generate floating label group
     */
    public static function floating($input, $label, $options = [])
    {
        $class = 'form-floating ' . ($options['class'] ?? '');
        
        $html = "<div class=\"{$class}\">";
        $html .= $input;
        $html .= "<label for=\"" . ($options['input_id'] ?? '') . "\">{$label}</label>";
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Generate currency input with Rp prefix
     */
    public static function currency($name, $value = '', $options = [])
    {
        $options['min'] = $options['min'] ?? '0';
        $options['step'] = $options['step'] ?? '1000';
        $input = self::number($name, $value, $options);
        
        return self::inputGroup($input, [
            'prefix' => 'Rp',
            'class' => $options['group_class'] ?? ''
        ]);
    }
    
    /**
     * Generate phone input with icon
     */
    public static function phone($name, $value = '', $options = [])
    {
        $input = self::tel($name, $value, $options);
        
        return self::inputGroup($input, [
            'prefix' => '<i class="bi bi-telephone"></i>',
            'class' => $options['group_class'] ?? ''
        ]);
    }
    
    /**
     * Generate search input with icon
     */
    public static function search($name, $value = '', $options = [])
    {
        $options['placeholder'] = $options['placeholder'] ?? 'Cari...';
        $input = self::text($name, $value, $options);
        
        return self::inputGroup($input, [
            'prefix' => '<i class="bi bi-search"></i>',
            'class' => $options['group_class'] ?? ''
        ]);
    }
    
    /**
     * Generate modal form wrapper
     */
    public static function modal($id, $title, $body, $options = [])
    {
        $size = $options['size'] ?? '';
        $action = $options['action'] ?? '';
        $method = $options['method'] ?? 'POST';
        
        $modalClass = $size ? "modal-dialog modal-{$size}" : 'modal-dialog';
        
        $html = "<div class=\"modal fade\" id=\"{$id}\" tabindex=\"-1\">";
        $html .= "<div class=\"{$modalClass}\">";
        $html .= '<div class="modal-content">';
        
        if ($action) {
            $html .= self::open($action, ['method' => $method]);
        }
        
        // Header
        $html .= '<div class="modal-header">';
        $html .= "<h5 class=\"modal-title\">{$title}</h5>";
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>';
        $html .= '</div>';
        
        // Body
        $html .= "<div class=\"modal-body\">{$body}</div>";
        
        // Footer
        $footerButtons = $options['footer_buttons'] ?? [
            'cancel' => 'Batal',
            'submit' => 'Simpan'
        ];
        
        if ($footerButtons) {
            $html .= '<div class="modal-footer">';
            if (isset($footerButtons['cancel'])) {
                $html .= "<button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">{$footerButtons['cancel']}</button>";
            }
            if (isset($footerButtons['submit'])) {
                $html .= "<button type=\"submit\" class=\"btn btn-primary\">{$footerButtons['submit']}</button>";
            }
            $html .= '</div>';
        }
        
        if ($action) {
            $html .= self::close();
        }
        
        $html .= '</div></div></div>';
        
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
    
    /**
     * Generate CSRF token field (for future implementation)
     */
    public static function csrf()
    {
        // TODO: Implement CSRF token generation
        // For now, return empty string
        return '';
    }
    
    /**
     * Generate method spoofing field for PUT/PATCH/DELETE
     */
    public static function method($method)
    {
        return self::hidden('_method', strtoupper($method));
    }
}