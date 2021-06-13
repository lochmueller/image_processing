<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor::class] = [
    'className' => \HDNET\ImageProcessing\Resource\Processing\LocalImageProcessor::class,
];

// Use for future versions
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.CropScaleMaskForce'] = \HDNET\ImageProcessing\Resource\Processing\LocalImageProcessor::class;
