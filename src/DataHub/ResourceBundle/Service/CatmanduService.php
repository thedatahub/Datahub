<?php

namespace DataHub\ResourceBundle\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * CatmanduService is a service for performing various
 * operations with the Catmandu CLI tool.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class CatmanduService
{
    /**
     * @var string
     */
    protected $cliPath;

    /**
     * Set cliPath.
     *
     * @param string $cliPath
     * @return CatmanduService
     */
    public function setCliPath($cliPath)
    {
        $this->cliPath = $cliPath;

        return $this;
    }

    /**
     * Convert data from the source format to the
     * target format.
     *
     * @param  string $sourceFormat Source format
     * @param  string $targetFormat Target format
     * @param  mixed  $data         Data to convert
     * @return mixed
     */
    public function convertData($sourceFormat, $targetFormat, $data)
    {
        // Construct command
        $cmd = sprintf('%s convert %s to %s', $this->cliPath, $sourceFormat, $targetFormat);

        $process = new Process($cmd);
        $process->setInput(str_replace('[]','{}', $data));
        $process->run();

        if (!$process->isSuccessful()) {
            // throw new ProcessFailedException($process);
            throw new \InvalidArgumentException($process->getErrorOutput());
        }

        $output = $process->getOutput();

        return $output;
    }
}
