<?php

declare(strict_types=1);

namespace Typo3Api\Exception;

use Throwable;
use Typo3Api\Tca\Field\AbstractField;

class TcaFieldException extends TcaConfigurationException
{
    public function __construct(AbstractField $field, $message = "", $code = 0, Throwable $previous = null)
    {
        $name = $field->getName();
        $message = "Error in field '$name': $message";
        parent::__construct($field, $message, $code, $previous);
    }

    public function getField(): AbstractField
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection
         * @phpstan-ignore-next-line */
        return $this->getConfiguration();
    }
}
