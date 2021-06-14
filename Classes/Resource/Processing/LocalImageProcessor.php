<?php

declare(strict_types=1);

namespace HDNET\ImageProcessing\Resource\Processing;

use TYPO3\CMS\Core\Resource\Processing\ImageCropScaleMaskTask;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;

/**
 * Class LocalImageProcessor.
 */
class LocalImageProcessor extends \TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor
{
    protected static bool $checkCircle = false;

    /**
     * Processes an image described in a task, but optionally uses a given local image.
     *
     * @throws \InvalidArgumentException
     */
    public function processTaskWithLocalFile(TaskInterface $task, ?string $localFile): void
    {
        $helper = $this->getHelperByTaskName($task->getName());
        try {
            if (null === $localFile) {
                $result = $helper->process($task);
            } else {
                $result = $helper->processWithLocalFile($task, $localFile);
            }
            if (null === $result) {
                if (self::$checkCircle) {
                    $task->setExecuted(true);
                    $task->getTargetFile()->setUsesOriginalFile();
                } else {
                    self::$checkCircle = true;
                    // Crop the image always!!!
                    $properties = $task->getSourceFile()->getProperties();
                    $configuration = $task->getConfiguration();
                    $configuration['crop'] = '0,0,'.$properties['width'].','.$properties['height'];
                    $task = new ImageCropScaleMaskTask($task->getTargetFile(), $configuration);
                    $this->processTaskWithLocalFile($task, $localFile);
                }
                self::$checkCircle = false;
            } elseif (!empty($result['filePath']) && file_exists($result['filePath'])) {
                $task->setExecuted(true);
                $imageDimensions = $this->getGraphicalFunctionsObject()->getImageDimensions($result['filePath']);
                $task->getTargetFile()->setName($task->getTargetFileName());
                $task->getTargetFile()->updateProperties(
                    ['width' => $imageDimensions[0], 'height' => $imageDimensions[1], 'size' => filesize($result['filePath']), 'checksum' => $task->getConfigurationChecksum()]
                );
                $task->getTargetFile()->updateWithLocalFile($result['filePath']);
            } else {
                // Seems we have no valid processing result
                $task->setExecuted(false);
            }
        } catch (\Exception $e) {
            $task->setExecuted(false);
        }
    }
}
