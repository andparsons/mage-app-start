<?php
namespace Magento\Analytics\Model;

/**
 * The interface represents the type of classes that handling of a new data collection for MBI.
 */
interface ExportDataHandlerInterface
{
    /**
     * Execute collecting new data for MBI.
     *
     * @return bool
     */
    public function prepareExportData();
}
