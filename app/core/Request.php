<?php

namespace App\Core;

class Request
{
    public static function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public static function all()
    {
        return array_merge($_GET, $_POST);
    }

    public static function only($keys)
    {
        $data = self::all();
        return array_intersect_key($data, array_flip((array) $keys));
    }

    public static function except($keys)
    {
        $data = self::all();
        return array_diff_key($data, array_flip((array) $keys));
    }

    public static function has($key)
    {
        return isset($_GET[$key]) || isset($_POST[$key]);
    }

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public static function isGet()
    {
        return self::method() === 'GET';
    }

    public static function isPost()
    {
        return self::method() === 'POST';
    }

    public static function isPut()
    {
        return self::method() === 'PUT';
    }

    public static function isDelete()
    {
        return self::method() === 'DELETE';
    }

    public static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public static function input($key, $default = null)
    {
        return self::post($key, self::get($key, $default));
    }

    public static function file($key)
    {
        return $_FILES[$key] ?? null;
    }

    public static function hasFile($key)
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public static function validate($rules)
    {
        $errors = [];
        $data = self::all();

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field][] = "Field $field is required";
            }
            
            if (strpos($rule, 'email') !== false && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field][] = "Field $field must be a valid email";
            }
            
            if (strpos($rule, 'numeric') !== false && !empty($value) && !is_numeric($value)) {
                $errors[$field][] = "Field $field must be numeric";
            }
        }

        return $errors;
    }
} 