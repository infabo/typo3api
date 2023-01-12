<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

interface DefaultTabInterface extends TcaConfigurationInterface
{
    /**
     * There are instances where fields should be separated from the main fields.
     */
    public function getDefaultTab(): string;
}
