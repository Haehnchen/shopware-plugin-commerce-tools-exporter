<?php

namespace CommerceToolsExporter\Commands;

use CommerceToolsExporter\Exporter\ExportContext;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryExportCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ct:exporter:categories')
            ->setDescription('CommerceTools category exporter')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()
            ->get('commerce_tools_exporter.exporter.category.category_exporter')
            ->export(new ExportContext(new ConsoleLogger($output)));
    }
}