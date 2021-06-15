<?php

declare(strict_types=1);

namespace Typo3Api\Builder\Context;

class TableBuilderContext implements TcaBuilderContext
{
    /**
     * @var string
     */
    private string $tableName;

    /**
     * @var string
     */
    private string $typeName;

    public function __construct(string $tableName, string $typeName)
    {
        $this->tableName = $tableName;
        $this->typeName = $typeName;
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
