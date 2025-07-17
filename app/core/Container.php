<?php

namespace App\Core;

class Container
{
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];

    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable $concrete): void
    {
        $this->singletons[$abstract] = $concrete;
    }

    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function resolve(string $abstract)
    {
        // Check if we have a direct instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Check if it's a singleton and already instantiated
        if (isset($this->singletons[$abstract])) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $this->singletons[$abstract]();
            }
            return $this->instances[$abstract];
        }

        // Check if we have a binding
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]();
        }

        // Try to auto-resolve the class
        if (class_exists($abstract)) {
            return new $abstract();
        }

        throw new \RuntimeException("Unable to resolve {$abstract}");
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || 
               isset($this->instances[$abstract]) || 
               isset($this->singletons[$abstract]) ||
               class_exists($abstract);
    }

    public function make(string $abstract, array $parameters = [])
    {
        if (!empty($parameters)) {
            // For parameterized instantiation
            if (class_exists($abstract)) {
                $reflection = new \ReflectionClass($abstract);
                return $reflection->newInstanceArgs($parameters);
            }
        }

        return $this->resolve($abstract);
    }

    public function call(callable $callback, array $parameters = [])
    {
        return call_user_func_array($callback, $parameters);
    }

    // Utility methods
    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getInstances(): array
    {
        return $this->instances;
    }

    public function getSingletons(): array
    {
        return $this->singletons;
    }

    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->singletons = [];
    }
}