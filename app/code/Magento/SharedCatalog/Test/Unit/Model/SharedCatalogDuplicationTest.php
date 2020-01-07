<?php
namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for SharedCatalogDuplication class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SharedCatalogDuplicationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\ProductItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Price\DuplicatorTierPriceLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogInvalidation;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogDuplication
     */
    private $sharedCatalogDuplication;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productItemManagement = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\ProductItemManagementInterface::class
        );

        $this->sharedCatalogInvalidation = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogInvalidation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->sharedCatalogDuplication = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\SharedCatalogDuplication::class,
            [
                'productItemManagement' => $this->productItemManagement,
                'sharedCatalogInvalidation' => $this->sharedCatalogInvalidation
            ]
        );
    }

    /**
     * Unit test for assignProductsToDuplicate().
     *
     * @return void
     */
    public function testAssignProductsToDuplicate()
    {
        $sharedCatalogId = 3;
        $productsSku = ['SKU_1', 'SKU_2'];
        $sharedCatalog = $this->getMockForAbstractClass(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class);
        $this->sharedCatalogInvalidation->expects($this->atLeastOnce())->method('checkSharedCatalogExist')
            ->with($sharedCatalogId)->willReturn($sharedCatalog);
        $sharedCatalog->expects($this->atLeastOnce())->method('getCustomerGroupId')
            ->willReturn(1);
        $sharedCatalog->expects($this->atLeastOnce())->method('getType')
            ->willReturn(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC);
        $this->productItemManagement->expects($this->exactly(2))->method('addItems')
            ->withConsecutive([1, $productsSku], [0, $productsSku])->willReturnSelf();
        $this->sharedCatalogDuplication->assignProductsToDuplicate($sharedCatalogId, $productsSku);
    }

    /**
     * Unit test for assignProductsToDuplicate() with NoSuchEntityException.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testAssignProductsToDuplicateWithNoSuchEntityException()
    {
        $sharedCatalogId = 3;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->sharedCatalogInvalidation->expects($this->atLeastOnce())->method('checkSharedCatalogExist')
            ->with($sharedCatalogId)->willThrowException($exception);
        $this->sharedCatalogDuplication->assignProductsToDuplicate($sharedCatalogId, ['SKU']);
    }

    /**
     * Unit test for assignProductsToDuplicate() with LocalizedException.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testAssignProductsToDuplicateWithLocalizedException()
    {
        $exception = new \Magento\Framework\Exception\LocalizedException(__('exception message'));

        $sharedCatalogId = 3;
        $productsSku = ['SKU_1', 'SKU_2'];
        $sharedCatalog = $this->getMockForAbstractClass(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class);
        $this->sharedCatalogInvalidation->expects($this->atLeastOnce())->method('checkSharedCatalogExist')
            ->with($sharedCatalogId)->willReturn($sharedCatalog);
        $sharedCatalog->expects($this->atLeastOnce())->method('getCustomerGroupId')
            ->willReturn(1);
        $sharedCatalog->expects($this->atLeastOnce())->method('getType')
            ->willReturn(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC);
        $this->productItemManagement->expects($this->any())->method('addItems')
            ->with(1, $productsSku)->willThrowException($exception);
        $this->sharedCatalogDuplication->assignProductsToDuplicate($sharedCatalogId, $productsSku);
    }
}
