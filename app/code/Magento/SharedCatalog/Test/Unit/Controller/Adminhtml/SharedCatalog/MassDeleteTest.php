<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog;

/**
 * Class MassDeleteTest
 * @package Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog
 */
class MassDeleteTest extends MassTest
{
    protected $actionName = 'Delete';

    /**
     * Test for method execute
     */
    public function testExecute()
    {
        if (empty($this->actionName)) {
            return;
        }
        $testData = [
            $this->createMock(\Magento\SharedCatalog\Model\SharedCatalog::class),
            $this->createMock(\Magento\SharedCatalog\Model\SharedCatalog::class)
        ];
        $this->sharedCatalogCollectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($testData));

        $this->sharedCatalogRepositoryMock->expects($this->any())
            ->method('deleteById')
            ->willReturnMap([[10, true], [11, true], [12, true]]);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('A total of %1 record(s) were deleted.', count($testData)));

        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->with('shared_catalog/*/index')
            ->willReturnSelf();

        $this->massAction->execute();
    }
}
