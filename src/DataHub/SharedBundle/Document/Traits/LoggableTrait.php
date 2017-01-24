<?php

namespace DataHub\SharedBundle\Document\Traits;

use Symfony\Bridge\Monolog\Logger;

/**
 * Monolog logger trait
 */
trait LoggableTrait
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Logger setter
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->logger;
    }

}
