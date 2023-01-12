<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 02.07.17
 * Time: 22:13
 */

namespace Typo3Api\Builder;

use Typo3Api\Tca\TcaConfigurationInterface;

interface TcaBuilderInterface
{
    public function configure(TcaConfigurationInterface $configuration): TcaBuilderInterface;

    public function configureInTab(string $tab, TcaConfigurationInterface $configuration): TcaBuilderInterface;

    public function configureAtPosition(string $position, TcaConfigurationInterface $configuration): TcaBuilderInterface;

    public function inheritConfigurationFromType(string $type): TcaBuilderInterface;

    public function addOrMoveTabInFrontOfTab(string $tab, string $otherTab): TcaBuilderInterface;
}
