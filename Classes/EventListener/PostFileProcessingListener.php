<?php

namespace HDNET\ImageProcessing\EventListener;

use TYPO3\CMS\Core\Resource\Event\AfterFileProcessingEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFileProcessingEvent;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;


class PostFileProcessingListener
{

    static protected bool $inExtraProcessing = false;

    public function __invoke(AfterFileProcessingEvent $event)
    {
        if (self::$inExtraProcessing) {
            return;
        }

        if ($event->getTaskType() !== 'Image.CropScaleMask') {
            return;
        }

        if ($event->getFile()->getExtension() === 'svg') {
            return;
        }

        if ($event->getFile()->getSha1() !== $event->getProcessedFile()->getSha1()) {
            return;
        }

        if (!is_callable([$event->getFile(), 'process'])) {
            return;
        }

        self::$inExtraProcessing = true;
        $configuration = $event->getConfiguration();

        $width = $event->getFile()->getProperty('width');
        $height = $event->getFile()->getProperty('height');
        $configuration['crop'] = '0,0,' . $width . ',' . $height;

        $processFile = $event->getFile()->process($event->getTaskType(), $configuration);

        $event->setProcessedFile($processFile);
        self::$inExtraProcessing = false;
    }

}
