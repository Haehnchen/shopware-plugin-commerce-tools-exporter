<?php

namespace CommerceToolsExporter\Config;

class ConfigFactory
{
    /**
     * @return Config
     */
    public static function create()
    {
        // @TODO: inject config?
        $config = Shopware()->Config();
        
        return new Config(
            $config->getByNamespace('CommerceToolsExporter', 'project'),
            $config->getByNamespace('CommerceToolsExporter', 'client_id'),
            $config->getByNamespace('CommerceToolsExporter', 'client_secret'),
            $config->getByNamespace('CommerceToolsExporter', 'product_type_id')
        );
    }
}