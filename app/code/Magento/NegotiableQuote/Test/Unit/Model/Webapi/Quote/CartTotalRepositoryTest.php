<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Webapi\Quote;

/**
 * Class CartTotalRepositoryTest
 */
class CartTotalRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\NegotiableQuote\Model\Webapi\Quote\CartTotalRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartTotalRepository;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->originalInterface = $this->createMock(\Magento\Quote\Api\CartTotalRepositoryInterface::class);
        $this->validator = $this->createMock(\Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cartTotalRepository = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Webapi\Quote\CartTotalRepository::class,
            [
                'originalInterface' => $this->originalInterface,
                'validator' => $this->validator
            ]
        );
    }

    /**
     * Test get
     */
    public function testGet()
    {
        $this->validator->expects($this->any())->method('validate')->willReturn(null);
        /**
         * @var \Magento\Quote\Api\Data\TotalsInterface $totals
         */
        $totals = $this->createMock(\Magento\Quote\Api\Data\TotalsInterface::class);
        $this->originalInterface->expects($this->any())->method('get')->willReturn($totals);

        $this->assertEquals($totals, $this->cartTotalRepository->get($this->cartId));
    }
}
