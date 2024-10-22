<?php

/**
 * Import model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ScheduledImportExport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class Import extends \Magento\ImportExport\Model\Import implements
    \Magento\ScheduledImportExport\Model\Scheduled\Operation\OperationInterface
{
    /**
     * Run import through cron
     *
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation $operation
     * @return bool
     */
    public function runSchedule(\Magento\ScheduledImportExport\Model\Scheduled\Operation $operation)
    {
        $sourceFile = $operation->getFileSource($this);

        if ($sourceFile) {
            $validationStrategy = $operation->getForceImport()
                ? ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS
                : ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR;

            $this->setData(self::FIELD_NAME_VALIDATION_STRATEGY, $validationStrategy);

            $this->createHistoryReport($sourceFile, $operation->getEntityType());

            $result = $this->validateSource(
                \Magento\ImportExport\Model\Import\Adapter::findAdapterFor(
                    $sourceFile,
                    $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR),
                    $this->getData(\Magento\ImportExport\Model\Import::FIELD_FIELD_SEPARATOR)
                )
            );

            if ($result
                || $operation->getForceImport()
                && !$this->getErrorAggregator()->hasFatalExceptions()
            ) {
                $result = $this->importSource();
            }

            if ($result) {
                $this->invalidateIndex();
            }

            return (bool)$result;
        }

        return false;
    }

    /**
     * Initialize import instance from scheduled operation
     *
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation $operation
     * @return $this
     */
    public function initialize(\Magento\ScheduledImportExport\Model\Scheduled\Operation $operation)
    {
        $fileInfo = $operation->getFileInfo();
        if (!is_array($fileInfo)) {
            $fileInfo = [];
        }
        $this->setData(
            array_merge(
                $fileInfo,
                [
                    'entity' => $operation->getEntityType(),
                    'behavior' => $operation->getBehavior(),
                    'operation_type' => $operation->getOperationType(),
                    'run_at' => $operation->getStartTime(),
                    'scheduled_operation_id' => $operation->getId(),
                ]
            )
        );
        return $this;
    }
}
