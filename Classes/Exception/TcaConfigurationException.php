<?php

namespace Typo3Api\Exception;

use Throwable;
use Typo3Api\Tca\TcaConfigurationInterface;

class TcaConfigurationException extends \RuntimeException
{
    /**
     * @var TcaConfigurationInterface
     */
    private $configuration;

    public function __construct(TcaConfigurationInterface $configuration, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->configuration = $configuration;
    }

    /**
     * @return TcaConfigurationInterface
     */
    public function getConfiguration(): TcaConfigurationInterface
    {
        return $this->configuration;
    }
}
