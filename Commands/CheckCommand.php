<?php

namespace CommerceToolsExporter\Commands;

use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ct:check')
            ->setDescription('CommerceTools Article Exporter')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            $this->getContainer()->get('commerce_tools_exporter.client.client_authenticator')->getAuthorizationHeader()
        );
    }
}