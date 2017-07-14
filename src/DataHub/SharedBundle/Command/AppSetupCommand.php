<?php

namespace DataHub\SharedBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for setting up the application.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class AppSetupCommand extends ContainerAwareCommand
{
    const MEMORY_LIMIT = '512M';

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:setup')
            ->setDescription('Configures the application')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $output->writeln(sprintf('<comment>Setting up the application for the %s environment</comment>', $this->kernel->getEnvironment()));

        $memoryLimit = static::MEMORY_LIMIT;
        $output->writeln(sprintf('Setting memory limit to %s', $memoryLimit));
        ini_set('memory_limit', $memoryLimit);

        $this->runCommand('assets:install --symlink web');
        $this->runCommand('cache:clear');
        $this->runCommand('assetic:dump');
    }
}
