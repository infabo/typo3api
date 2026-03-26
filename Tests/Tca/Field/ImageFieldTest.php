<?php

namespace Typo3Api\Tca\Field;


use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\PreparationForTypo3;

class ImageFieldTest extends FileFieldTest
{
    use PreparationForTypo3; // tt_content is needed here

    #[\Override]
    protected function createFieldInstance(string $name, array $options = []): AbstractField
    {
        // require 'vendor/typo3/cms/typo3/sysext/core/Configuration/DefaultConfiguration.php';
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = 'gif,jpg,jpeg,tif,tiff,bmp,pcx,tga,png,pdf,ai,svg';
        return new ImageField($name, $options);
    }

    #[\Override]
    protected function assertBasicCtrlChange(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $ctrl = [];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals([
            'thumbnail' => $field->getName()
        ], $ctrl, "ctrl change");
    }

    #[\Override]
    protected function assertBasicColumns(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $this->assertEquals([
            $field->getName() => [
                'label' => $field->getOption('label'),
                'config' => [
                    // TODO: Important! Verify that the fieldname value in foreign table either matches the column name
                    // or is set properly in the following TCA, see https://docs.typo3.org/permalink/t3tca:confval-inline-foreign-match-fields
                    'type' => 'file',
                    'allowed' => 'gif,jpg,jpeg,tif,tiff,png',
                    'minitems' => 0,
                    'maxitems' => 100,
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
                        'collapseAll' => true,
                        'showPossibleLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true,
                        'enabledControls' => [
                            'localize' => true
                        ],
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::TEXT->value => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::IMAGE->value => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::AUDIO->value => [
                                'showitem' => '
                                --palette--;;audioOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::VIDEO->value => [
                                'showitem' => '
                                --palette--;;videoOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::APPLICATION->value => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ]
                    ],
                ]
            ]
        ], $field->getColumns($stubTable));
    }

    /**
     * @dataProvider validNameProvider
     * @param string $fieldName
     */
    public function testThumbnail(string $fieldName): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $altFieldName = $fieldName . '_2';

        $ctrl = [];
        $field = $this->createFieldInstance($fieldName, ['useAsThumbnail' => false]);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEmpty($ctrl, "No thumbnail modified");

        $ctrl = [];
        $field = $this->createFieldInstance($fieldName, ['useAsThumbnail' => true]);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals(['thumbnail' => $fieldName], $ctrl, "thumbnail added");

        $ctrl = [];
        $field = $this->createFieldInstance($fieldName);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals(['thumbnail' => $fieldName], $ctrl, "thumbnail added even if not specified");

        // $ctrl = []; // left out on purpose
        $field = $this->createFieldInstance($altFieldName);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals(['thumbnail' => $fieldName], $ctrl, "thumbnail not overwritten");

        // $ctrl = []; // left out on purpose
        $field = $this->createFieldInstance($altFieldName, ['useAsThumbnail' => 'force']);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals(['thumbnail' => $altFieldName], $ctrl, "thumbnail force overwritten");


    }
}
