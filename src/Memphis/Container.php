<?php

namespace Memphis;

use Memphis\Exception\BindResolving as BindResolvingException;
use Memphis\Exception\ClassNotExists as ClassNotExistsException;
use Memphis\Exception\ClassConstructorResolving as ClassConstructorResolvingException;
use ReflectionClass;
use ReflectionParameter;

class Container
{
    protected $binds;

    public function __construct()
    {
        $this->binds = [];
    }

    public function bind($interface, $concrete)
    {
        $this->binds[$interface] = $concrete;
    }

    public function get($className)
    {
        if ( ! $this->hasBind($className)) {
            return $this->resolveClass($className);
        }

        return $this->resolveBind($this->binds[$className]);
    }

    protected function hasBind($bind)
    {
        return isset($this->binds[$bind]);
    }

    protected function resolveClass($class)
    {
        if ( ! $this->isClass($class)) {
            throw new ClassNotExistsException("Class '{$class}' does not exists.");
        }

        return $this->constructClass($class);
    }

    protected function isClass($bind)
    {
        return is_string($bind) && class_exists($bind);
    }

    protected function constructClass($class)
    {
        $ref = new ReflectionClass($class);
        if ($ref->isInternal()) {
            return $ref->newInstance();
        }

        $constructor = $ref->getConstructor();
        $constructorParameters = $constructor->getParameters();

        $instanceParameters = [];
        foreach ($constructorParameters as $parameter) {
            $instanceParameters[] = $this->resolveParameter($parameter, $class);
        }

        return $ref->newInstanceArgs($instanceParameters);
    }

    protected function resolveParameter(ReflectionParameter $parameter, $class)
    {
        $parameterClass = $parameter->getClass();
        if ($parameterClass === null) {
            throw new ClassConstructorResolvingException("Class '{$class}' constructor could not be resolved.");
        }

        return $this->get($parameterClass->getName());
    }

    protected function resolveBind($bind)
    {
        if ($this->isClass($bind)) {
            return $this->resolveClass($bind);

        } elseif (is_callable($bind)) {
            return call_user_func($bind);

        } elseif (is_object($bind)) {
            return $bind;
        }

        throw new BindResolvingException('Bind could not be resolved.');
    }
}
