<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Util;

use Typo3Api\Tca\Field\CountryField;
use Typo3Api\Tca\Field\InputField;
use Typo3Api\Tca\Field\TextareaField;
use Typo3Api\Tca\Linebreak;
use Typo3Api\Tca\NamedPalette;

class Address extends NamedPalette
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $prefix;

    public function __construct(string $name = 'Address', array $options = [])
    {
        $this->options = $options;
        $this->prefix = $options['prefix'] ?? strtolower(preg_replace('#\W+#', '_', $name));
        parent::__construct($name, $this->getFields());
    }

    protected function fieldOptions(string $field, array $defaults): array
    {
        return [
            $this->prefix . $field,
            isset($this->options[$field])
                ? $defaults + $this->options[$field]
                : $defaults
        ];
    }

    protected function getFields(): array
    {
        return [
            // schema.org streetAddress
            new TextareaField(...$this->fieldOptions('street', [
                'required' => $this->options['required'] ?? true,
                'label' => 'Street',
                'localize' => false,
                'placeholder' => "Street address, company name, Apartment, suit, unit, building, floor",
                'max' => 200,
                'rows' => 2,
            ])),
            new Linebreak(),
            // schema.org addressLocality
            new InputField(...$this->fieldOptions('city', [
                'required' => $this->options['required'] ?? true,
                'label' => 'City/Locality',
                'localize' => false,
                'placeholder' => 'eg. Berlin, Mountain View',
            ])),
            // schema.org addressRegion
            new InputField(...$this->fieldOptions('region', [
                'required' => false,
                'label' => 'State/Province/Region (can be abbreviation)',
                'localize' => false,
                'placeholder' => 'eg. CA, QLD or Bayern',
            ])),
            // schema.org postalCode
            new InputField(...$this->fieldOptions('postal_code', [
                'required' => $this->options['required'] ?? true,
                'label' => 'ZIP/Postal Code',
                'localize' => false,
                'placeholder' => '98052 or F-75002',
                'max' => 10,
            ])),
            new Linebreak(),
            // schema.org addressCountry
            new CountryField(...$this->fieldOptions('country', [
                'required' => $this->options['required'] ?? true,
                'label' => 'Country',
                'localize' => false,
            ]))
        ];
    }
}
