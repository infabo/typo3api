<?php

declare(strict_types=1);
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::PSR_12);
    $parameters->set(Option::SKIP, [
        DeclareStrictTypesFixer::class => [
            'ext_localconf.php',
            'ext_tables.php',
        ]
    ]);
};
