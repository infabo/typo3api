<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

interface DefaultTabInterface extends TcaConfigurationInterface
{
    /**
     * There are instances where fields should be separated from the main fields.
     *
     * @return string
     */
    public function getDefaultTab(): string;
}
