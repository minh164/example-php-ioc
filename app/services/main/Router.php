<?php

namespace App\Services\Main;

use App\Constants\StatusCodes;

class Router
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $action
     *
     * @return mixed
     * @throws \Exception
     */
    protected function handleAction($action)
    {
        // check if action is Closure
        if ($action instanceof \Closure) {
            return $action();
        }

        if (!is_string($action)) {
            throw new \Exception('Action is invalid type', StatusCodes::BAD_REQUEST);
        }

        $actionParts = explode('@', trim($action));
        $controller = $actionParts[0];
        $method = $actionParts[1] ?? null;

        if (!class_exists($controller)) {
            throw new \Exception("Could not found $controller class", StatusCodes::BAD_REQUEST);
        }

        if (!method_exists($controller, $method)) {
            throw new \Exception("Could not found $method() method in $controller class", StatusCodes::BAD_REQUEST);
        }

        $dispatcher = make($controller);

        return makeMethod($dispatcher, $method);
    }

    /**
     * Check if route method equal request method
     *
     * @param string $method
     * @return bool
     */
    protected function isMethod(string $method)
    {
        return $this->request->getMethod() == $method;
    }

    /**
     * Check if route URI match with request URI
     *
     * @param string $uri
     * @return bool
     */
    protected function isMatchUri(string $uri)
    {
        $uri = trim($uri);
        $uri = trim($uri, '/');

        return $this->request->getUri() == $uri;
    }

    public function get(string $uri, $action)
    {
        if (!$this->isMethod(Request::GET_METHOD) || !$this->isMatchUri($uri)) {
            return false;
        }

        $this->handleAction($action);
        exit(); // return response and end process
    }

    public function post(string $uri, $action)
    {
        if (!$this->isMethod(Request::POST_METHOD) || !$this->isMatchUri($uri)) {
            return false;
        }

        $this->handleAction($action);
        exit(); // return response and end process
    }
}