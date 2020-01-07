<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Quote\Model\Quote;

/**
 * Unit test for \Magento\NegotiableQuote\Model\Plugin\Quote\Model\Quote\AddressPlugin.
 */
class AddressPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Quote\Model\Quote\AddressPlugin
     */
    private $addressPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->appState =
            $this->getMockBuilder(\Magento\Framework\App\State::class)
                ->disableOriginalConstructor()
                ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->addressPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Plugin\Quote\Model\Quote\AddressPlugin::class,
            [
                'appState' => $this->appState
            ]
        );
    }

    /**
     * Test for afterRequestShippingRates().
     *
     * @dataProvider afterRequestShippingRatesDataProvider
     *
     * @param string $code
     * @param float $price
     * @param bool $result
     * @param int $expectedResult
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $call
     * @param string $shippingMethod
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $deletedCall
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $setPriceCall
     * @return void
     */
    public function testAfterRequestShippingRates(
        string $code,
        float $price,
        bool $result,
        int $expectedResult,
        \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $call,
        string $shippingMethod,
        \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $deletedCall,
        \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $setPriceCall
    ) {
        /**
         * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject $address
         */
        $address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);

        $negotiableQuote = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class);
        $negotiableQuote->expects($call)->method('getShippingPrice')->willReturn($price);
        $negotiableQuote->expects($call)->method('getId')->willReturn(1);

        $quoteExtensionAttributes = $this
            ->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteExtensionAttributes->expects($call)->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $quote->expects($call)->method('getExtensionAttributes')->willReturn($quoteExtensionAttributes);
        $address->expects($call)->method('getQuote')->willReturn($quote);
        $rate = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\Rate::class)
            ->setMethods(['getCode', 'getPrice', 'setData', 'setPrice', 'isDeleted'])
            ->disableOriginalConstructor()
            ->getMock();

        $rate->expects($call)->method('getCode')->willReturn($code);
        $rate->expects($call)->method('getPrice')->willReturn($price);
        $rate->expects($call)->method('setData')->willReturnSelf();
        $rate->expects($setPriceCall)->method('setPrice')->with($price)->willReturnSelf();
        $rate->expects($deletedCall)->method('isDeleted')->with(true)->willReturnSelf();
        $address->expects($call)->method('getAllShippingRates')->willReturn([$rate]);
        $address->expects($call)->method('getShippingMethod')->willReturn($shippingMethod);
        $address->expects($call)->method('setShippingAmount')->willReturnSelf();
        $this->appState->expects($call)->method('getAreaCode')->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);

        $this->assertEquals($expectedResult, $this->addressPlugin->afterRequestShippingRates($address, $result));
    }

    /**
     * Data provider for testAfterRequestShippingRates().
     *
     * @return array
     */
    public function afterRequestShippingRatesDataProvider()
    {
        return [
            [
                'code'           => 'default',
                'price'          => 1.5,
                'result'         => true,
                'expectedResult' => true,
                'call'           => $this->atLeastOnce(),
                'shippingMethod' => 'default',
                'deletedCall'    => $this->never(),
                'setPriceCall'   => $this->atLeastOnce(),
            ],
            [
                'code'           => 'default',
                'price'          => 0,
                'result'         => true,
                'expectedResult' => true,
                'call'           => $this->atLeastOnce(),
                'shippingMethod' => 'default',
                'deletedCall'    => $this->never(),
                'setPriceCall'   => $this->never(),
            ],
            [
                'code'           => 'custom',
                'price'          => 1.5,
                'result'         => true,
                'expectedResult' => true,
                'call'           => $this->atLeastOnce(),
                'shippingMethod' => 'default',
                'deletedCall'    => $this->atLeastOnce(),
                'setPriceCall'   => $this->never(),
            ],
            [
                'code'           => 'default',
                'price'          => 0.00,
                'result'         => true,
                'expectedResult' => true,
                'call'           => $this->atLeastOnce(),
                'shippingMethod' => 'custom',
                'deletedCall'    => $this->atLeastOnce(),
                'setPriceCall'   => $this->never(),
            ],
            [
                'code'           => 'custom',
                'price'          => 0.00,
                'result'         => false,
                'expectedResult' => false,
                'call'           => $this->never(),
                'shippingMethod' => 'default',
                'deletedCall'    => $this->never(),
                'setPriceCall'   => $this->never(),
            ],
            [
                'code'           => 'custom',
                'price'          => 1.5,
                'result'         => true,
                'expectedResult' => true,
                'call'           => $this->atLeastOnce(),
                'shippingMethod' => 'custom',
                'deletedCall'    => $this->never(),
                'setPriceCall'   => $this->atLeastOnce(),
            ],
            [
                'code'           => 'default',
                'price'          => 1.5,
                'result'         => true,
                'expectedResult' => true,
                'call'           => $this->atLeastOnce(),
                'shippingMethod' => 'custom',
                'deletedCall'    => $this->atLeastOnce(),
                'setPriceCall'   => $this->never(),
            ],
        ];
    }
}
