<?php

namespace VKC\DataHub\SharedBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as BaseContainerAwareCommand;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base container aware command with some added functionality.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
abstract class ContainerAwareCommand extends BaseContainerAwareCommand
{
    protected $output;
    protected $container;
    protected $kernel;

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->container = $this->getContainer();
        $this->kernel = $this->container->get('kernel');
    }

    /**
     * Runs a command string using certain arguments and options.
     *
     * @param  string $string    [description]
     * @param  array  $arguments [description]
     * @param  array  $options   [description]
     * @return boolean           [description]
     */
    protected function runCommand($string, $arguments = array(), $options = array())
    {
        $this->output->writeln(sprintf('<info>Running command "%s"</info>', $string));

        // Split namespace and arguments
        $namespace = explode(' ', $string)[0];

        // Set input
        $command = $this->getApplication()->find($namespace);
        $input = new StringInput($string);
        $input->setInteractive(false);

        if (!empty($arguments)) {
            foreach ($arguments as $key => $value) {
                $input->setArgument($key, $value);
            }
        }

        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $input->setOption($key, $value);
            }
        }

        // Send all output to the console
        $returnCode = $command->run($input, $this->output);
        $this->output->writeln('');

        return $returnCode != 0;
    }
}
