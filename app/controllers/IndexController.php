<?php

namespace App\Controllers;

use App\Bootstraps\Container;
use App\Models\Sources;
use App\Services\Main\Request;

class IndexController extends BaseController
{
    protected $source;

    public $test;

    public function __construct(Sources $source)
    {
        $this->source = $source;
    }

    public function index(Request $request)
    {
        echo "Hello, you are access it on URI: {$request->getUri()}";
    }

    public function create(Request $request)
    {
        xx($request->getJson());
    }
}