<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Discount\StateChanges;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class ApplierTest
 */
class ApplierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $applier;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->appState = $this->createMock(\Magento\Framework\App\State::class);
        $this->quote = $this->createMock(
            \Magento\Quote\Api\Data\CartInterface::class
        );
        $this->applierMock = $this->createMock(
            \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->applier = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class,
            [
                'appState' => $this->appState
            ]
        );
    }

    /**
     * Test setItemsHasChanges
     *
     * @param float|null $negotiatedPriceValue
     * @dataProvider dataProviderSetHasItemChanges
     */
    public function testSetHasItemChanges($negotiatedPriceValue)
    {
        $negotiableQuote = $this->createMock(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class
        );
        $negotiableQuote->expects($this->any())->method('getNotifications')->willReturn(0);
        $negotiableQuote->expects($this->any())->method('setNotifications')->willReturnSelf();
        $negotiableQuote->expects($this->any())->method('getNegotiatedPriceValue')->willReturn($negotiatedPriceValue);
        $quoteExtension = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $quoteExtension->expects($this->any())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $this->quote->expects($this->any())->method('getExtensionAttributes')->willReturn($quoteExtension);
        $this->appState->expects($this->any())->method('getAreaCode')
            ->willReturn(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class,
            $this->applier->setHasItemChanges($this->quote)
        );
    }

    /**
     * Test setIsDiscountChanged
     */
    public function testSetIsDiscountChanged()
    {
        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class,
            $this->applier->setIsDiscountChanged($this->quote)
        );
    }

    /**
     * Test setIsDiscountRemovedLimit
     */
    public function testSetIsDiscountRemovedLimit()
    {
        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class,
            $this->applier->setIsDiscountRemovedLimit($this->quote)
        );
    }

    /**
     * Test setIsDiscountRemoved
     */
    public function testSetIsDiscountRemoved()
    {
        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class,
            $this->applier->setIsDiscountRemoved($this->quote)
        );
    }

    /**
     * Test setIsTaxChanged
     */
    public function testSetIsTaxChanged()
    {
        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class,
            $this->applier->setIsTaxChanged($this->quote)
        );
    }

    /**
     * Test setIsAddressChanged
     */
    public function testSetIsAddressChanged()
    {
        $this->assertInstanceOf(
            \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier::class,
            $this->applier->setIsAddressChanged($this->quote)
        );
    }

    /**
     * Test removeMessage
     */
    public function testRemoveMessage()
    {
        /**
         * @var NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject $negotiableQuote
         */
        $negotiableQuote = $this->createMock(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class);
        $negotiableQuote->expects($this->any())->method('getNotifications')->willReturn(256);
        $this->appState->expects($this->any())->method('getAreaCode')
            ->willReturn(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $this->assertEquals(null, $this->applier->removeMessage($negotiableQuote, 1, true));
    }

    /**
     * DataProvider setHasItemChanges
     *
     * @return array
     */
    public function dataProviderSetHasItemChanges()
    {
        return [
            [1.00],
            [null]
        ];
    }
}
