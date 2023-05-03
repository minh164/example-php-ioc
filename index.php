<?php

require 'app/bootstraps/bootstrap.php';
require 'app/helpers/dump.php';
require 'app/helpers/container.php';

try {
    $request = \App\Bootstraps\Container::getInstance()->bindAndResolve(
        \App\Services\Main\Request::class,
        '',
        true
    );
    $router = new \App\Services\Main\Router($request);

    $router->get('/abc', \App\Controllers\IndexController::class . '@index');
    $router->post('/create', \App\Controllers\IndexController::class . '@create');

} catch (Exception $e) {
    $content = "<span style='font-size: 20px; color: red;'>Error: {$e->getMessage()}</span><br><br>";
    $content .= "Status code: {$e->getCode()} <br><br>";
    $content .= "File: {$e->getFile()} in line <span style='font-weight: bold'>{$e->getLine()}</span><br><br>";
    $content .= $e->getTraceAsString();

    echo $content;
}

function test()
{
    class A {}
    class B1 {}
    class B2 {}
    class B {
        public $b1;
        public $b2;

        public function __construct(B1 $b1, B2 $b2)
        {
            $this->b1 = $b1;
            $this->b2 = $b2;
        }
    }

    class C {
        public $b;

        public function __construct(B $b)
        {
            $this->b = $b;
        }
    }

    class Controller {
        public $a;
        public $c;

        public function __construct(A $a, C $c)
        {
            $this->a = $a;
            $this->c = $c;
        }
    }

    // Option 1:
    //$app = \App\Bootstraps\Container::getInstance();
    //$controller = $container->bindAndResolve('Controller');

    // Option 2:
    //$app = \App\Bootstraps\Container::getInstance();
    //$app->bind('Controller');
    //$controller = $app->resolve('Controller');

    // Option 3:
    $controller = make('Controller');

    xxx($controller);
}

function testSingleton()
{
    class Singleton
    {
        public $name = 123;
    }

    \App\Bootstraps\Container::getInstance()->singleton(Singleton::class);
    $singleton = \App\Bootstraps\Container::getInstance()->resolve(Singleton::class);
    $singleton->name = 999;

    $singleton_2 = \App\Bootstraps\Container::getInstance()->resolve(Singleton::class);
    $singleton_2->name = 'hahaha';

    xx(\App\Bootstraps\Container::getInstance()->getServices()); // service Singleton has $name = hahaha
}

function testBindClosure()
{
    $clo = make("TestClosure", function () {
        return make(\App\Controllers\IndexController::class);
    }, true);
    $clo->test = 123;


    $clo_2 = \App\Bootstraps\Container::getInstance()->resolve('TestClosure');
    $clo_2->test = 987;

    xx($clo->test == $clo_2->test); // true
}


