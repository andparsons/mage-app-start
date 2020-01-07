<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\ResourceModel;

/**
 * Test for Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem class.
 */
class NegotiableQuoteItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resource->expects($this->once())
            ->method('getTableName')
            ->willReturn(\Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem::NEGOTIABLE_QUOTE_ITEM_TABLE);
        $resource->expects($this->once())->method('getConnection')->with('default')->willReturn($this->adapter);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem::class,
            [
                '_resources' => $resource,
            ]
        );
    }

    /**
     * Test saveList method.
     *
     * @return void
     */
    public function testSaveList()
    {
        $item = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMockForAbstractClass();
        $item->expects($this->once())->method('getData')->willReturn(['price' => 10]);
        $this->adapter->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                'negotiable_quote_item',
                [['price' => 10]],
                [
                    \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::ORIGINAL_PRICE,
                    \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::ORIGINAL_TAX_AMOUNT,
                    \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::ORIGINAL_DISCOUNT_AMOUNT,
                ]
            )
            ->willReturn(1);
        $this->model->saveList([$item]);
    }
}
