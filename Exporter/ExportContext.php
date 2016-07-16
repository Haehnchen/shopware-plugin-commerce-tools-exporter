<?php

namespace CommerceToolsExporter\Exporter;

use Psr\Log\LoggerInterface;

class ExportContext
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getShopId()
    {
        // @TODO
        return 1;
    }
}