<?php

namespace HDNET\ImageProcessing\Resource\Processing;

use TYPO3\CMS\Core\Resource\Processing\ImageCropScaleMaskTask;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;

/**
 * Class LocalImageProcessor
 *
 */
class LocalImageProcessor extends \TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor
{
    static protected bool $checkCircle = false;

    /**
     * Processes an image described in a task, but optionally uses a given local image
     *
     * @param TaskInterface $task
     * @param string|null $localFile
     * @throws \InvalidArgumentException
     */
    public function processTaskWithLocalFile(TaskInterface $task, ?string $localFile): void
    {
        $helper = $this->getHelperByTaskName($task->getName());
        try {
            if ($localFile === null) {
                $result = $helper->process($task);
            } else {
                $result = $helper->processWithLocalFile($task, $localFile);
            }
            if ($result === null) {

                if (self::$checkCircle) {
                    $task->setExecuted(true);
                    $task->getTargetFile()->setUsesOriginalFile();
                } else {
                    self::$checkCircle = true;
                    // Crop the image always!!!
                    $properties = $task->getSourceFile()->getProperties();
                    $configuration = $task->getConfiguration();
                    $configuration['crop'] = '0,0,' . $properties['width'] . ',' . $properties['height'];
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
