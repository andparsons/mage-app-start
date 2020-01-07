<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

/**
 * Class CheckoutQuoteValidatorTest
 * @package Magento\NegotiableQuote\Test\Unit\Model
 */
class CheckoutQuoteValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\CheckoutQuoteValidator
     */
    protected $checkoutQuoteValidator;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->quoteMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getAllVisibleItems']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->checkoutQuoteValidator = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\CheckoutQuoteValidator::class
        );
    }

    /**
     * Test of countInvalidQtyItems() method
     *
     * @param string $origin
     * @param int $expectErrorsCount
     * @dataProvider countInvalidQtyItemsDataProvider
     */
    public function testCountInvalidQtyItems($origin, $expectErrorsCount)
    {
        $quoteItemMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartItemInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getErrorInfos']
        );
        $errorInfos = [
            [
                'origin' => $origin,
                'code' => \Magento\CatalogInventory\Helper\Data::ERROR_QTY
            ]
        ];
        $quoteItemMock->expects($this->once())
            ->method('getErrorInfos')
            ->willReturn($errorInfos);
        $this->quoteMock
            ->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([$quoteItemMock]);

        $this->assertEquals($expectErrorsCount, $this->checkoutQuoteValidator->countInvalidQtyItems($this->quoteMock));
    }

    /**
     * Data Provider for testCountInvalidQtyItems()
     *
     * @return array
     */
    public function countInvalidQtyItemsDataProvider()
    {
        return [
            ['cataloginventory', 1],
            ['xxx', 0]
        ];
    }
}
