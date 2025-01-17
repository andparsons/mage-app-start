<?php
namespace Magento\Store\Model\Config\Importer\Processor;

use Magento\Framework\Exception\RuntimeException;

/**
 * The processor for store manipulations.
 */
interface ProcessorInterface
{
    /**
     * Runs current process.
     *
     * @param array $data The data to be processed
     * @return void
     * @throws RuntimeException If processor was unable to finish execution
     */
    public function run(array $data);
}
