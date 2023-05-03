<?php

namespace App\Services\Main;

class Request
{
    const GET_METHOD = 'GET';
    const POST_METHOD = 'POST';
    const PUT_METHOD = 'PUT';
    const PATCH_METHOD = 'PATCH';
    const DELETE_METHOD = 'DELETE';
    const OPTION_METHOD = 'OPTION';

    public function __construct()
    {
        
    }

    public function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * @return string|array
     */
    public function getUri(bool $toArray = false)
    {
        $rawUri = $_SERVER['REQUEST_URI'];

        // remove query string
        $queryString = "?" . $this->getQueryString();
        $uri = str_replace($queryString, '', $rawUri);

        // replace multiple forward slash to only one ("/")
        $uri = preg_replace("~/+~", '/', $uri);
        $uri = trim($uri);
        $uri = trim($uri, '/');

        return $toArray ? explode('/', $uri) : $uri;
    }

    public function getHeader()
    {

    }

    /**
     * Get parameters on path
     *
     * @return array
     */
    public function getQuery()
    {
        return $_GET;
    }

    /**
     * Get form submit data
     *
     * @return array
     */
    public function getFormData()
    {
        return $_POST;
    }

    /**
     * Get raw request body
     *
     * @return false|string
     */
    public function getRawBody()
    {
        return file_get_contents("php://input");
    }

    /**
     * Get json on request body
     *
     * @param bool $toArray
     * @return mixed
     */
    public function getJson(bool $toArray = true)
    {
        $body = $this->getRawBody();
        if (empty($body)) {
            return $body;
        }

        return json_decode($body, true);
    }

    /**
     * Get parameters string on path
     *
     * @return string
     */
    public function getQueryString()
    {
        return $_SERVER['QUERY_STRING'];
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}