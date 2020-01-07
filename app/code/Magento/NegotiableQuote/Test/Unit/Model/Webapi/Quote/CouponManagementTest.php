<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi\Quote;

/**
 * Class CouponManagementTest
 */
class CouponManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CouponManagementInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var string
     */
    private $couponCode = 'coupon_code';

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\Quote\CouponManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $couponManagement;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->originalInterface = $this->createMock(\Magento\Quote\Api\CouponManagementInterface::class);
        $this->validator = $this->createMock(\Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->couponManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\Quote\CouponManagement::class,
            [
                'originalInterface' => $this->originalInterface,
                'validator' => $this->validator
            ]
        );
    }

    /**
     * Test set
     */
    public function testSet()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        $this->originalInterface->expects($this->any())->method('set')->willReturn(true);

        $this->assertEquals(true, $this->couponManagement->set($this->cartId, $this->couponCode));
    }

    /**
     * Test remove
     */
    public function testRemove()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        $this->originalInterface->expects($this->any())->method('remove')->willReturn(true);

        $this->assertEquals(true, $this->couponManagement->remove($this->cartId));
    }
}
