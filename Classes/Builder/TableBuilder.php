<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 18.06.17
 * Time: 20:13
 */

namespace Typo3Api\Builder;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\Tca\BaseConfiguration;
use Typo3Api\Tca\CompoundTcaConfiguration;
use Typo3Api\Tca\DefaultTabInterface;
use Typo3Api\Tca\TcaConfigurationInterface;

class TableBuilder implements TcaBuilderInterface
{
    /**
     * @var TableBuilderContext
     */
    private TableBuilderContext $context;

    /**
     * This is a list of default tabs.
     * Default tabs are passed by the DefaultTabInterface Interface.
     * They are forced to be always at the end.
     *
     * @var array
     */
    private array $defaultTabs = [];

    /**
     * TableBuilder constructor.
     *
     * @param string $tableName
     * @param string $typeName
     *
     * @internal param string $name
     */
    public function __construct(string $tableName, string $typeName)
    {
        $this->context = new TableBuilderContext($tableName, $typeName);
        $this->configureTableIfNotPresent();
    }

    /**
     * @param string $name
     * @param string $typeName
     *
     * @return TableBuilder
     */
    public static function create(string $name, string $typeName = '1'): TableBuilder
    {
        /** @var TableBuilder $tableBuilder */
        $tableBuilder = GeneralUtility::makeInstance(static::class, $name, $typeName);
        if (!$tableBuilder->getTitle()) {
            $title = preg_replace('#tx_[^_]+_#su', '', $name);
            $title = str_replace('_', ' ', $title);
            $title = ucfirst($title);
            $tableBuilder->setTitle($title);
        }
        return $tableBuilder;
    }

    /**
     * @param TcaConfigurationInterface $configuration
     *
     * @return $this
     */
    public function configure(TcaConfigurationInterface $configuration): TcaBuilderInterface
    {
        if ($configuration instanceof DefaultTabInterface) {
            $tabName = $configuration->getDefaultTab();
            $this->defaultTabs[] = $tabName;
        } else {
            $tabName = 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general';
        }
        return $this->configureInTab($tabName, $configuration);
    }

    /**
     * @param string $tab
     * @param TcaConfigurationInterface $configuration
     *
     * @return $this
     */
    public function configureInTab(string $tab, TcaConfigurationInterface $configuration): TcaBuilderInterface
    {
        $tca = &$GLOBALS['TCA'][$this->getTableName()];

        $configuration->modifyCtrl($tca['ctrl'], $this->context);
        $this->addShowItemToTab($tca, $configuration, $tab);
        $this->addPalettesAndColumns($tca, $configuration);

        return $this;
    }

    /**
     * @param string $position
     * @param TcaConfigurationInterface $configuration
     *
     * @return $this
     */
    public function configureAtPosition(string $position, TcaConfigurationInterface $configuration): TcaBuilderInterface
    {
        $tca = &$GLOBALS['TCA'][$this->getTableName()];

        $configuration->modifyCtrl($tca['ctrl'], $this->context);
        $this->addShowItemAtPosition($tca, $configuration, $position);
        $this->addPalettesAndColumns($tca, $configuration);

        return $this;
    }

    private function addPalettesAndColumns(array &$tca, TcaConfigurationInterface $configuration): void
    {
        $this->addPalettes($tca, $configuration);
        if ($configuration instanceof CompoundTcaConfiguration) {
            foreach ($configuration as $item) {
                $this->addPalettesAndColumns($tca, $item);
            }
        } else {
            $this->addColumns($tca, $configuration);
        }
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function inheritConfigurationFromType(string $type): TcaBuilderInterface
    {
        $tca = &$GLOBALS['TCA'][$this->getTableName()];

        if (!isset($tca['types'][$type])) {
            $msg = "The Type $type isn't defined so it can't be inherited from it.";
            $msg .= " Possible types are: " . implode(', ', array_keys($tca['types']));
            throw new \RuntimeException($msg);
        }

        // TODO maybe not overwrite existing configuration?
        $tca['types'][$this->getTypeName()] = $tca['types'][$type];

        return $this;
    }

    /**
     * @param string $tab
     * @param string $otherTab
     *
     * @return $this
     */
    public function addOrMoveTabInFrontOfTab(string $tab, string $otherTab): TcaBuilderInterface
    {
        $type = &$GLOBALS['TCA'][$this->getTableName()]['types'][$this->getTypeName()];

        $search = '/--div--\s*;\s*' . preg_quote($tab, '/') . '.*?(?=,\s?--div--|$)/Us';
        $match = preg_match($search, $type['showitem'], $results);
        $newTab = $match ? $results[0] : '--div--; ' . $tab;

        if ($match) {
            $type['showitem'] = preg_replace($search, '', $type['showitem'], 1);
        }

        // search the other tab and add the new one in front of it
        $search = '/--div--\s*;\s*' . preg_quote($otherTab, '/') . '.*?(?=,\s?--div--|$)/Us';
        $type['showitem'] = preg_replace($search, $newTab . ', \0', $type['showitem'], 1, $matches);
        if ($matches === 0) {
            throw new \RuntimeException("The tab '$otherTab' seems to not exist.");
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->context->getTableName();
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->context->getTypeName();
    }

    public function getTitle(): string
    {
        if (!isset($GLOBALS['TCA'][$this->getTableName()]['ctrl']['title']) || !is_string($GLOBALS['TCA'][$this->getTableName()]['ctrl']['title'])) {
            return '';
        }

        return $GLOBALS['TCA'][$this->getTableName()]['ctrl']['title'];
    }

    public function setTitle(string $title): TableBuilder
    {
        $GLOBALS['TCA'][$this->getTableName()]['ctrl']['title'] = $title;
        return $this;
    }

    /**
     * @return bool
     */
    protected function configureTableIfNotPresent(): bool
    {
        if (isset($GLOBALS['TCA'][$this->getTableName()])) {
            return false;
        }

        $GLOBALS['TCA'][$this->getTableName()] = [
            'ctrl' => [],
            'interface' => [
                'showRecordFieldList' => '',
            ],
            'columns' => [],
            'types' => [],
            'palettes' => [],
        ];

        // add the basic fields every table should have
        $this->configure(new BaseConfiguration());

        return true;
    }

    /**
     * @param array $tca
     * @param TcaConfigurationInterface $configuration
     */
    protected function addPalettes(array &$tca, TcaConfigurationInterface $configuration): void
    {
        $palettes = $configuration->getPalettes($this->context);
        foreach ($palettes as $paletteName => $paletteDefinition) {
            if (isset($tca['palettes'][$paletteName])) {
                if ($paletteDefinition !== $tca['palettes'][$paletteName]) {
                    $msg = "The palette $paletteName is already defined in {$this->context} but isn't compatible.";
                    $msg .= " If you can rename the palette than that would be an easy fix for the problem.";
                    throw new \RuntimeException($msg);
                }

                continue;
            }

            $tca['palettes'][$paletteName] = $paletteDefinition;
        }
    }

    /**
     * @param array $tca
     * @param TcaConfigurationInterface $configuration
     */
    protected function addColumns(array &$tca, TcaConfigurationInterface $configuration): void
    {
        $columns = $configuration->getColumns($this->context);
        $existingColumns = $tca['columns'];
        $missingColumns = array_diff(array_keys($columns), array_keys($existingColumns));

        if (count($missingColumns) === count($columns)) {
            foreach ($columns as $columnName => $columnDefinition) {
                $tca['columns'][$columnName] = $columnDefinition;
            }

            $tca['ctrl']['EXT']['typo3api']['sql'] = array_merge_recursive(
                $tca['ctrl']['EXT']['typo3api']['sql'] ?? [],
                $configuration->getDbTableDefinitions($this->context)
            );
        } else {
            if (count($missingColumns) > 0) {
                $confClass = get_class($configuration);
                $definedColumns = implode(', ', array_keys($columns));
                $alreadyDefinedColumns = implode(', ',
                    array_intersect(array_keys($existingColumns), array_keys($columns)));
                $notDefinedColumns = implode(', ', $missingColumns);
                $msg = "The $confClass defined the database columns $definedColumns.\n";
                $msg .= "However, the columns $alreadyDefinedColumns are already defined.\n";
                $msg .= "But the columns $notDefinedColumns are not.\n";
                $msg .= "This means the definitions would need to be merged.\n";
                $msg .= "This is currently not implemented because of all the special cases like relations that would need to be handled.\n";
                $msg .= "Therefor partial configuration of a child type is currently not possible.";
                throw new \RuntimeException($msg);
            } else {
                // all columns are already defined so define overrides, just in case something changed.
                foreach ($columns as $columnName => $columnDefinition) {

                    // don't overwrite if both arrays are identical
                    $existingColumnDefinition = $tca['columns'][$columnName];
                    if ($existingColumnDefinition === $columnDefinition) {
                        continue;
                    }

                    // prevent accidental type changes
                    $existingColumnType = $existingColumnDefinition['config']['type'];
                    $newColumnType = $columnDefinition['config']['type'];
                    if ($newColumnType !== $existingColumnType) {
                        $tableName = $this->getTableName();
                        $typeName = $this->getTypeName();
                        $msg = "Column $columnName is already defined in table $tableName but as type $existingColumnType.";
                        $msg .= " Tried to change the type in type $typeName with $newColumnType.";
                        $msg .= " It is not possible to change the field type in different render types.";
                        $msg .= " Use another field name or use another table entirely.";
                        throw new \RuntimeException($msg);
                    }

                    $tca['types'][$this->getTypeName()]['columnsOverrides'][$columnName] = $columnDefinition;
                }
            }
        }
    }

    /**
     * @param array $tca
     * @param TcaConfigurationInterface $configuration
     * @param string $tab
     */
    protected function addShowItemToTab(array &$tca, TcaConfigurationInterface $configuration, string $tab): void
    {
        if (!isset($tca['types'][$this->getTypeName()])) {
            $tca['types'][$this->getTypeName()] = [];
        }
        $type = &$tca['types'][$this->getTypeName()];

        $showItemString = $configuration->getShowItemString($this->context);
        if ($showItemString === '') {
            return;
        }

        // search the correct tab and add the content into it
        $search = '/--div--\s*;\s*' . preg_quote($tab, '/') . '.*(?=,\s*--div--|$)/Us';
        $type['showitem'] = preg_replace($search, '\0,' . $showItemString, $type['showitem'] ?? '', 1, $matches);
        if ($matches > 0) {
            return;
        }

        // so the tab did not exist yet...

        $newTab = '--div--; ' . $tab . ', ' . $showItemString;

        // put the new tab right before the first "default tab"
        if (count($this->defaultTabs) > 0 && !in_array($tab, $this->defaultTabs, true)) {
            $search = '/--div--\s*;\s*' . preg_quote(reset($this->defaultTabs), '/') . '.*(?=,\s*--div--|$)/Us';
            $type['showitem'] = preg_replace($search, $newTab . ', \0', $type['showitem'], 1, $matches);
            if ($matches > 0) {
                return;
            }
        }

        // just put the new tab at the end
        if (isset($type['showitem']) && !empty($type['showitem'])) {
            $type['showitem'] .= ', ' . $newTab;
        } else {
            $type['showitem'] = $newTab;
        }
    }

    /**
     * @param array $tca
     * @param TcaConfigurationInterface $configuration
     * @param string $position
     */
    protected function addShowItemAtPosition(
        array &$tca,
        TcaConfigurationInterface $configuration,
        string $position
    ): void {
        $showItemString = $configuration->getShowItemString($this->context);
        if ($showItemString === '') {
            return;
        }

        ExtensionManagementUtility::addToAllTCAtypes(
            $this->getTableName(),
            $showItemString,
            $this->getTypeName(),
            $position
        );
    }
}
