<?php

namespace CommerceToolsExporter;

use CommerceToolsExporter\Commands\CheckCommand;
use CommerceToolsExporter\Commands\ExportCommand;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Console\Application;
use Shopware\Components\Plugin;

class CommerceToolsExporter extends Plugin implements SubscriberInterface
{
    public function registerCommands(Application $application)
    {
        // @TODO: wait for accepted pull request of symfony tag
        $application->add(new ExportCommand());
        $application->add(new CheckCommand());
    }
}