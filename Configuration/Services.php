<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire();

    $services->load('Typo3Api\\', '../Classes/*');

    $services->set(\Typo3Api\EventListener\SqlSchemaListener::class)
        ->tag('event.listener', [
            'identifier' => 'typo3api-builder/sql-schema',
            'event' => \TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent::class
        ]);

    $services->set(\Typo3Api\EventListener\RegisterWizard::class)
        ->tag('event.listener', [
            'identifier' => 'typo3api-builder/register-wizard',
            'event' => \TYPO3\CMS\Core\Configuration\Event\ModifyLoadedPageTsConfigEvent::class
        ]);
};
