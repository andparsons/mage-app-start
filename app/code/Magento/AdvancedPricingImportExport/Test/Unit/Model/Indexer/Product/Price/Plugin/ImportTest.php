<?php

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Indexer\Product\Price\Plugin;

use Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import as Import;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Indexer\IndexerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexer;

    /**
     * @var Import |\PHPUnit_Framework_MockObject_MockObject
     */
    private $import;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing|\PHPUnit_Framework_MockObject_MockObject
     */
    private $advancedPricing;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerRegistry;

    protected function setUp()
    {
        $this->indexer = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false
        );
        $this->indexerRegistry = $this->createMock(
            \Magento\Framework\Indexer\IndexerRegistry::class
        );
        $this->import = new \Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import(
            $this->indexerRegistry
        );
        $this->advancedPricing = $this->createMock(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class
        );
        $this->indexerRegistry->expects($this->any())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID)
            ->willReturn($this->indexer);
    }

    public function testAfterSaveReindexIsOnSave()
    {
        $this->indexer->expects($this->once())
            ->method('isScheduled')
            ->willReturn(false);
        $this->indexer->expects($this->once())
            ->method('invalidate');
        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }

    public function testAfterSaveReindexIsOnSchedule()
    {
        $this->indexer->expects($this->once())
            ->method('isScheduled')
            ->willReturn(true);
        $this->indexer->expects($this->never())
            ->method('invalidate');
        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }

    public function testAfterDeleteReindexIsOnSave()
    {
        $this->indexer->expects($this->once())
            ->method('isScheduled')
            ->willReturn(false);
        $this->indexer->expects($this->once())
            ->method('invalidate');
        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }

    public function testAfterDeleteReindexIsOnSchedule()
    {
        $this->indexer->expects($this->once())
            ->method('isScheduled')
            ->willReturn(true);
        $this->indexer->expects($this->never())
            ->method('invalidate');
        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }
}
