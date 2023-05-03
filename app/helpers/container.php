<?php

function make(string $abstract, $processor = NULL, bool $singleton = false)
{
    $container = \App\Bootstraps\Container::getInstance();
    $container->bind($abstract, $processor, $singleton);

    return $container->resolve($abstract);
}

function makeMethod($concrete, string $methodName)
{
    $container = \App\Bootstraps\Container::getInstance();

    return $container->resolveMethod($concrete, $methodName);
}