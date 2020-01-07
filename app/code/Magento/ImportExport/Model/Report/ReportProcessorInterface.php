<?php

namespace Magento\ImportExport\Model\Report;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Error report generator interface
 */
interface ReportProcessorInterface
{
    /**
     * @param string $originalFileName
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param bool $writeOnlyErrorItems
     * @return string
     */
    public function createReport(
        $originalFileName,
        ProcessingErrorAggregatorInterface $errorAggregator,
        $writeOnlyErrorItems = false
    );
}
