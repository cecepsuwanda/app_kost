<?php

namespace App\Core;

class Request
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Static methods (for backward compatibility)
    public function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public function all()
    {
        return array_merge($_GET, $_POST);
    }

    public function only($keys)
    {
        $data = $this->all();
        return array_intersect_key($data, array_flip((array) $keys));
    }

    public function except($keys)
    {
        $data = $this->all();
        return array_diff_key($data, array_flip((array) $keys));
    }

    public function has($key)
    {
        return isset($_GET[$key]) || isset($_POST[$key]);
    }

    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function isGet()
    {
        return $this->method() === 'GET';
    }

    public function isPost()
    {
        return $this->method() === 'POST';
    }

    public function isPut()
    {
        return $this->method() === 'PUT';
    }

    public function isDelete()
    {
        return $this->method() === 'DELETE';
    }

    public function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function input($key, $default = null)
    {
        return $this->post($key, $this->get($key, $default));
    }

    public function file($key)
    {
        return $_FILES[$key] ?? null;
    }

    public function hasFile($key)
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function validate($rules)
    {
        $errors = [];
        $data = $this->all();

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

    // Instance methods (new approach)
    public function getParam($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public function postParam($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public function allParams()
    {
        return array_merge($_GET, $_POST);
    }

    public function onlyParams($keys)
    {
        $data = $this->allParams();
        return array_intersect_key($data, array_flip((array) $keys));
    }

    public function exceptParams($keys)
    {
        $data = $this->allParams();
        return array_diff_key($data, array_flip((array) $keys));
    }

    public function hasParam($key)
    {
        return isset($_GET[$key]) || isset($_POST[$key]);
    }

    public function requestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function requestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function isGetRequest()
    {
        return $this->requestMethod() === 'GET';
    }

    public function isPostRequest()
    {
        return $this->requestMethod() === 'POST';
    }

    public function isPutRequest()
    {
        return $this->requestMethod() === 'PUT';
    }

    public function isDeleteRequest()
    {
        return $this->requestMethod() === 'DELETE';
    }

    public function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function inputParam($key, $default = null)
    {
        return $this->postParam($key, $this->getParam($key, $default));
    }

    public function fileParam($key)
    {
        return $_FILES[$key] ?? null;
    }

    public function hasFileParam($key)
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function validateParams($rules)
    {
        $errors = [];
        $data = $this->allParams();

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