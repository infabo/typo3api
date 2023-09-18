<?php

declare(strict_types=1);
use Typo3Api\EventListener\SqlSchemaListener;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use Typo3Api\EventListener\RegisterWizard;
use TYPO3\CMS\Core\Configuration\Event\ModifyLoadedPageTsConfigEvent;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire();

    $services->load('Typo3Api\\', '../Classes/*');

    $services->set(SqlSchemaListener::class)
        ->tag('event.listener', [
            'identifier' => 'typo3api-builder/sql-schema',
            'event' => AlterTableDefinitionStatementsEvent::class
        ]);

    $services->set(RegisterWizard::class)
        ->tag('event.listener', [
            'identifier' => 'typo3api-builder/register-wizard',
            'event' => ModifyLoadedPageTsConfigEvent::class
        ]);
};
