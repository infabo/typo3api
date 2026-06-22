<?php

declare(strict_types=1);

namespace Typo3Api\EventListener;

use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;
use TYPO3\CMS\Core\Configuration\Tca\TcaEnrichment;

final class AfterTcaCompilationEventListener
{
    public function __construct(private TcaEnrichment $tcaEnrichment)
    {
    }

    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        $event->setTca($this->tcaEnrichment->enrich($event->getTca()));
    }
}
