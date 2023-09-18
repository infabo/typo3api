<?php

declare(strict_types=1);

namespace Typo3Api\Builder\Context;

class TableBuilderContext implements TcaBuilderContext, \Stringable
{
    public function __construct(private readonly string $tableName, private readonly string $typeName)
    {
    }
    public function getTableName(): string
    {
        return $this->tableName;
    }
    public function getTypeName(): string
    {
        return $this->typeName;
    }
    public function __toString(): string
    {
        return $this->tableName;
    }
}
