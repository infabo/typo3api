<?php

declare(strict_types=1);

namespace Typo3Api\Tca;

use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Builder\Context\TcaBuilderContext;

/**
 * Enables workspace versioning (versioningWS) for a table.
 *
 * Sets the required "versioningWS" ctrl flag and adds all mandatory
 * versioning fields (t3ver_oid, t3ver_wsid, t3ver_state, t3ver_stage)
 * to the table's TCA and database schema.
 *
 * @see https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/VersioningWs.html
 */
class VersioningConfiguration implements TcaConfigurationInterface
{
    public function modifyCtrl(array &$ctrl, TcaBuilderContext $tcaBuilder): void
    {
        $ctrl['versioningWS'] = true;
        $ctrl['origUid'] = 't3_origuid';
    }

    public function getColumns(TcaBuilderContext $tcaBuilder): array
    {
        // No manual TCA columns needed: TYPO3 Core automatically registers the
        // t3ver_oid, t3ver_wsid, t3ver_state and t3ver_stage columns for every
        // table that has versioningWS enabled in ctrl.
        return [];
    }

    public function getPalettes(TcaBuilderContext $tcaBuilder): array
    {
        return [];
    }

    public function getShowItemString(TcaBuilderContext $tcaBuilder): string
    {
        // Versioning fields are purely technical and not shown in the form.
        return '';
    }

    public function getDbTableDefinitions(TableBuilderContext $tableBuilder): array
    {
        // No manual SQL needed: TYPO3's DefaultTcaSchema automatically adds t3ver_oid,
        // t3ver_wsid, t3ver_state, t3ver_stage and the t3ver_oid index for every table
        // that has versioningWS enabled in ctrl.
        return [
            $tableBuilder->getTableName() => [],
        ];
    }
}



