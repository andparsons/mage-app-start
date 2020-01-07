<?php

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionList\Order;

use Magento\RequisitionList\Model\RequisitionListItem\OrderItem\Converter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Converter.
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListManagement;

    /**
     * @var Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemConverter;

    /**
     * @var \Magento\RequisitionList\Model\ProductSkuLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productSkuLocator;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Order\Converter
     */
    private $converter;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requisitionListRepository = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListManagement = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\RequisitionListManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemConverter = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\OrderItem\Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productSkuLocator = $this->getMockBuilder(\Magento\RequisitionList\Model\ProductSkuLocator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->converter = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionList\Order\Converter::class,
            [
                'requisitionListRepository' => $this->requisitionListRepository,
                'requisitionListManagement' => $this->requisitionListManagement,
                'requisitionListItemConverter' => $this->requisitionListItemConverter,
                'productSkuLocator' => $this->productSkuLocator,
            ]
        );
    }

    /**
     * Test for convert() method.
     *
     * @return void
     */
    public function testConvert()
    {
        $productId = 2;
        $sku = 'sku';
        $requisitionList = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItem = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductId'])
            ->getMockForAbstractClass();
        $orderItem->expects($this->atLeastOnce())->method('getProductId')->willReturn($productId);
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->atLeastOnce())->method('getItems')->willReturn([$orderItem]);
        $this->productSkuLocator->expects($this->atLeastOnce())->method('getProductSkus')->with([$productId])
            ->willReturn([$productId => $sku]);
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemConverter->expects($this->atLeastOnce())->method('convert')
            ->willReturn($requisitionListItem);
        $this->requisitionListManagement->expects($this->atLeastOnce())->method('addItemToList')
            ->with($requisitionList, $requisitionListItem)->willReturn($requisitionList);
        $this->requisitionListRepository->expects($this->atLeastOnce())->method('save')->willReturn($requisitionList);

        $this->assertEquals([$requisitionListItem], $this->converter->addItems($order, $requisitionList));
    }
}
