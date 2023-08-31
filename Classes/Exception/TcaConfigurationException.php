<?php

declare(strict_types=1);

namespace Typo3Api\Exception;

use Throwable;
use Typo3Api\Tca\TcaConfigurationInterface;

class TcaConfigurationException extends \RuntimeException
{
    public function __construct(private readonly TcaConfigurationInterface $configuration, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getConfiguration(): TcaConfigurationInterface
    {
        return $this->configuration;
    }
}
