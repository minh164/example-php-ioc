<?php

namespace App\Bootstraps;

use App\Constants\StatusCodes;

class Container
{
    const SERVICE_IS_RESOLVED = 'isResolved';
    const SERVICE_INSTANCE = 'instance';
    const SERVICE_SINGLETON = 'singleton';
    const SERVICE_PROCESSOR = 'processor';

    /**
     * Instance of class, only one in runtime
     */
    protected static $instance;

    /**
     * List services is register
     */
    protected $registers = [];

    private function __construct()
    {
    }

    /**
     * Get instance
     *
     * @return Container
     */
    public static function getInstance(): Container
    {
        // check if instance is not exist
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Get registered services in container
     *
     * @return array
     */
    public function getServices()
    {
        return $this->registers;
    }

    /**
     * Registry a service
     *
     * @param string $abstract
     * @param null $processor
     *
     * @return bool
     */
    public function bind(string $abstract, $processor = NULL, bool $singleton = false): bool
    {
        if (empty($abstract)) {
            throw new \Exception('Please type abstract!', StatusCodes::BAD_REQUEST);
        }

        if (empty($processor)) {
            $processor = $abstract;
        }

        $this->registers[$abstract] = [
            self::SERVICE_PROCESSOR => $processor,
            self::SERVICE_INSTANCE => null,
            self::SERVICE_IS_RESOLVED => false,
            self::SERVICE_SINGLETON => $singleton,
        ];

        return true;
    }

    /**
     * Bind with singleton
     *
     * @param string $abstract
     * @param null $processor
     *
     * @return bool
     * @throws \Exception
     */
    public function singleton(string $abstract, $processor = NULL): bool
    {
        return $this->bind($abstract, $processor, true);
    }

    /**
     * Resolve nested dependencies to object
     *
     * @param string $className
     *
     * @return mixed
     * @throws \Exception
     */
    private function resolveDependencies(string $className): mixed
    {
        if (!class_exists($className)) {
            throw new \Exception("$className does not exist!", StatusCodes::BAD_REQUEST);
        }

        $this->registers[123][] = $className;
        $reflection = new \ReflectionClass($className);

        // check if class have constructor
        $constructors = $reflection->getConstructor();
        if (!$constructors) {
            return new $className;
        }

        // check if constructor have params
        $dependencies = $constructors->getParameters();
        if (!count($dependencies)) {
            return new $className;
        }

        // resolve all dependencies
        $objects = [];
        foreach ($dependencies as $dependency) {
            // check if dependency does not have type-hint
            if (!$dependency->hasType()) {
                throw new \Exception("$" . "{$dependency->getName()} dependency of $className does not have type-hint!", StatusCodes::BAD_REQUEST);
            }

            $objects[] = $this->resolve($dependency->getType()->getName());
        }

        return new $className(...$objects);
    }

    /**
     * Resolve register
     *
     * @param string $abstract
     *
     * @return mixed|string|null
     * @throws \Exception
     */
    public function resolve(string $abstract): mixed
    {
        if (empty($abstract)) {
            throw new \Exception('Please type register service!', StatusCodes::BAD_REQUEST);
        }

        // return cached instance without re-resolve if service is singleton
        if ($this->isResolved($abstract) && $this->isSingleton($abstract)) {
            return $this->getSingletonInstance($abstract);
        }

        // check if service is existed
        $isExisted = $this->isExisted($abstract);

        $processor = $isExisted ? $this->registers[$abstract][self::SERVICE_PROCESSOR] : $abstract;

        $result = null;
        if ($processor instanceof \Closure) {
            // check if processor is Closure
            $result = $processor();
        } elseif (is_string($processor) && !class_exists($processor)) {
            // check if processor is a non exist class
            $result = (string) $processor;
        } elseif (is_string($processor) && class_exists($processor)) {
            // check if processor is a class
            $result = $this->resolveDependencies($processor);
        } elseif (is_object($processor)) {
            // check if processor is a object
            $result = $processor;
        }

        if ($isExisted) {
            $this->resolveSuccess($abstract, $result);
        }

        return $result;
    }

    /**
     * Update service when it is resolved successfully
     *
     * @param string $abstract
     * @param mixed $instance Resolved instance (string, array, object, ...)
     *
     * @throws \Exception
     */
    protected function resolveSuccess(string $abstract, mixed $instance): void
    {
        if (!$this->isExisted($abstract)) {
            throw new \Exception("Abstract: $abstract does not existed", StatusCodes::BAD_REQUEST);
        }

        if ($this->isSingleton($abstract)) {
            $this->registers[$abstract][self::SERVICE_INSTANCE] = $instance;
        }

        $this->registers[$abstract][self::SERVICE_IS_RESOLVED] = true;
    }

    /**
     * Register and resolve service
     *
     * @param string $abstract
     * @param null $processor
     * @param bool $singleton
     *
     * @return mixed
     * @throws \Exception
     */
    public function bindAndResolve(string $abstract, $processor = NULL, bool $singleton = false): mixed
    {
        $this->bind($abstract, $processor, $singleton);

        return $this->resolve($abstract);
    }

    /**
     * Resolve all parameters of method and Execute it
     *
     * @param string|object $concrete String or Object has method which be need to resolve (if it is string, resolve to a object)
     * @param string $methodName
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function resolveMethod(mixed $concrete, string $methodName): mixed
    {
        if (empty($concrete) || empty($methodName)) {
            throw new \Exception('Please type concrete and method name!', StatusCodes::BAD_REQUEST);
        }

        if (!is_string($concrete) && !is_object($concrete)) {
            throw new \Exception('Concrete parameter must be a string or object!', StatusCodes::BAD_REQUEST);
        }

        // if concrete is string, resolve it to a object
        if (is_string($concrete)) {
            $object = $this->bindAndResolve($concrete);
            if (!is_object($object)) {
                throw new \Exception("$concrete cannot resolve a object!", StatusCodes::BAD_REQUEST);
            }
        } else {
            $object = $concrete;
        }

        $className = $object::class;
        $reflection = new \ReflectionMethod($className, $methodName);

        $params = $reflection->getParameters();
        if (!count($params)) {
            return $object->$methodName();
        }

        // if method has params, resolve its params
        $resolvedParams = [];
        foreach ($params as $index => $param) {
            // check if param has type
            if (!$param->hasType()) {
                throw new \Exception("$" . "{$param->getName()} parameter of $methodName() method of $className does not have type-hint!", StatusCodes::BAD_REQUEST);
            }

            $resolvedParams[$index] = $this->resolve($param->getType()->getName());
            if (!is_object($resolvedParams[$index])) {
                throw new \Exception("$" . "{$param->getName()} parameter of $methodName() method of $className cannot resolve a object!", StatusCodes::BAD_REQUEST);
            }
        }

        return $object->$methodName(...$resolvedParams);
    }

    /**
     * Check if service is a singleton
     *
     * @param string $abstract
     *
     * @return false|mixed
     */
    public function isSingleton(string $abstract): bool
    {
        return $this->registers[$abstract][self::SERVICE_SINGLETON] ?? false;
    }

    /**
     * Check if service is resolved
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function isResolved(string $abstract): bool
    {
        return $this->registers[$abstract][self::SERVICE_IS_RESOLVED] ?? false;
    }

    /**
     * Check if service is existed
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function isExisted(string $abstract): bool
    {
        return !empty($this->registers[$abstract]);
    }

    /**
     * Return singleton instance
     *
     * @param string $abstract
     *
     * @return mixed
     */
    protected function getSingletonInstance(string $abstract): mixed
    {
        return $this->registers[$abstract][self::SERVICE_INSTANCE] ?? null;
    }
}