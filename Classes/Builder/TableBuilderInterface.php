<?php


namespace Typo3Api\Builder;

interface TableBuilderInterface extends TcaBuilderInterface
{
    public function getTableName(): string;
}
