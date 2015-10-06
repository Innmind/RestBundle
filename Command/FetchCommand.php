<?php

namespace Innmind\RestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class FetchCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('innmind:rest:fetch')
            ->setDescription(
                'Fetch all the resource definitions from the given server'
            )
            ->addArgument('server', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');

        $capabilities = $this
            ->getContainer()
            ->get('innmind_rest.client.capabilities_factory')
            ->make($server);
        $loader = $this
            ->getContainer()
            ->get('innmind_rest.client.loader_factory')
            ->make($server);

        $output->writeln(sprintf(
            'Fetching exposed resources at <fg=cyan>%s</fg=cyan>',
            $server
        ));
        $capabilities->refresh();

        foreach ($capabilities->keys() as $name => $url) {
            $output->writeln(sprintf(
                'Fetching definition for the resource "<fg=cyan>%s</fg=cyan>" at "<fg=cyan>%s</fg=cyan>"',
                $name,
                $url
            ));
            $loader->refresh($url);
        }

        $output->writeln('<success>All definitions loaded</success>');
    }
}
