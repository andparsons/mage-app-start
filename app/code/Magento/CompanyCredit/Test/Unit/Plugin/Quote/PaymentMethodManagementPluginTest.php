<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Quote;

/**
 * Class PaymentMethodManagementPluginTest.
 */
class PaymentMethodManagementPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $methodSpecificationFactory;

    /**
     * @var \Magento\CompanyCredit\Plugin\Quote\PaymentMethodManagementPlugin
     */
    private $paymentMethodManagementPlugin;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->quoteRepository = $this->createMock(
            \Magento\Quote\Api\CartRepositoryInterface::class
        );
        $this->methodSpecificationFactory = $this->createMock(
            \Magento\Payment\Model\Checks\SpecificationFactory::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentMethodManagementPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Quote\PaymentMethodManagementPlugin::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'methodSpecificationFactory' => $this->methodSpecificationFactory,
            ]
        );
    }

    /**
     * Test aroundGetList method.
     */
    public function testAroundGetList()
    {
        $cartId = 1;
        $quote = $this->createMock(
            \Magento\Quote\Model\Quote::class
        );

        $this->quoteRepository->expects(static::once())->method('get')->willReturn($quote);
        $specification = $this->createMock(
            \Magento\Payment\Model\Checks\Composite::class
        );
        $method = $this->createMock(
            \Magento\Payment\Model\MethodInterface::class
        );
        $specification->expects(static::at(0))->method('isApplicable')->willReturn(false);
        $specification->expects(static::at(1))->method('isApplicable')->willReturn(true);
        $this->methodSpecificationFactory->expects(static::once())->method('create')->willReturn($specification);
        $subject = $this->getMockBuilder(\Magento\Quote\Api\PaymentMethodManagementInterface::class)
            ->getMockForAbstractClass();
        $proceed = function () use ($method) {
            return [$method, $method];
        };
        $this->assertEquals(
            count($this->paymentMethodManagementPlugin->aroundGetList($subject, $proceed, $cartId)),
            1
        );
    }
}
