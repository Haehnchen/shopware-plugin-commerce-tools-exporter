<?php

namespace CommerceToolsExporter;

use CommerceToolsExporter\Commands\CheckCommand;
use CommerceToolsExporter\Commands\ExportCommand;
use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Console\Application;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;

class CommerceToolsExporter extends Plugin implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Console_Add_Command' => 'onAddConsoleCommand',
        ];
    }

    public function onAddConsoleCommand(\Enlight_Event_EventArgs $args)
    {
        return new ArrayCollection([
            new ExportCommand(),
        ]);
    }
    
    public function install(InstallContext $context)
    {
        
    }

    public function registerCommands(Application $application)
    {
        // @TODO: wait for accepted pull request of symfony tag
        $application->add(new ExportCommand());
        $application->add(new CheckCommand());
    }
}