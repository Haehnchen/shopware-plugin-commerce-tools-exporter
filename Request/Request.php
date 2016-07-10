<?php

namespace CommerceToolsExporter\Request;

class Request
{
    /**
     * @var array
     */
    private $body;

    /**
     * @var string
     */
    private $endpoint;
    
    /**
     * @var string
     */
    private $method;

    public function __construct($endpoint, $method = 'GET', array $body = [])
    {
        $this->body = $body;
        $this->endpoint = $endpoint;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}