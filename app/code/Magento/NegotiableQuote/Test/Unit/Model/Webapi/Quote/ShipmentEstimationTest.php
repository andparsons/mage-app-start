<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi\Quote;

/**
 * Class ShipmentEstimationTest
 */
class ShipmentEstimationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\ShipmentEstimationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var int
     */
    private $cartId = 1;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\Quote\ShipmentEstimation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentEstimation;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->originalInterface = $this->createMock(\Magento\Quote\Api\ShipmentEstimationInterface::class);
        $this->validator = $this->createMock(\Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->shipmentEstimation = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\Quote\ShipmentEstimation::class,
            [
                'originalInterface' => $this->originalInterface,
                'validator' => $this->validator
            ]
        );
    }

    /**
     * Test estimateByExtendedAddress
     */
    public function testEstimateByExtendedAddress()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        /**
         * @var \Magento\Quote\Api\Data\AddressInterface $address
         */
        $address = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        /**
         * @var \Magento\Quote\Api\Data\ShippingMethodInterface $shippingMethod
         */
        $shippingMethod = $this->createMock(\Magento\Quote\Api\Data\ShippingMethodInterface::class);
        $this->originalInterface->expects($this->any())->method('estimateByExtendedAddress')
            ->willReturn([$shippingMethod]);

        $this->assertEquals(
            [$shippingMethod],
            $this->shipmentEstimation->estimateByExtendedAddress($this->cartId, $address)
        );
    }
}
