<?php

namespace CommerceToolsExporter\Config;

class Config
{
    /**
     * @var string
     */
    private $project;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $productTypeId;

    public function __construct($project, $clientId, $clientSecret, $productTypeId)
    {
        $this->project = $project;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->productTypeId = $productTypeId;
    }

    /**
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function getProductTypeId()
    {
        return $this->productTypeId;
    }
}