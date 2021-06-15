<?php

declare(strict_types=1);


namespace Typo3Api\Builder;

interface TableBuilderInterface extends TcaBuilderInterface
{
    public function getTableName(): string;
}
