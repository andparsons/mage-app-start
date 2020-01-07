<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi\Quote;

/**
 * Class ShippingMethodManagementTest
 */
class ShippingMethodManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var int
     */
    private $addressId = 1;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\Quote\ShippingMethodManagement
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMethodManagement;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->originalInterface = $this->createMock(\Magento\Quote\Api\ShippingMethodManagementInterface::class);
        $this->validator = $this->createMock(\Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->shippingMethodManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\Quote\ShippingMethodManagement::class,
            [
                'originalInterface' => $this->originalInterface,
                'validator' => $this->validator
            ]
        );
    }

    /**
     * Test estimateByAddressId
     */
    public function testEstimateByAddressId()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        /**
         * @var \Magento\Quote\Api\Data\ShippingMethodInterface $shippingMethod
         */
        $shippingMethod = $this->createMock(\Magento\Quote\Api\Data\ShippingMethodInterface::class);
        $this->originalInterface->expects($this->any())->method('estimateByAddressId')->willReturn([$shippingMethod]);

        $this->assertEquals(
            [$shippingMethod],
            $this->shippingMethodManagement->estimateByAddressId($this->cartId, $this->addressId)
        );
    }
}
