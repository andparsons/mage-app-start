<?php

namespace Magento\CompanyPayment\Test\Unit\Plugin\Quote;

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
     * @var \Magento\CompanyPayment\Plugin\Quote\PaymentMethodManagementPlugin
     */
    private $paymentMethodManagementPlugin;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->quoteRepository->method('get')->willReturn($quote);
        $this->methodSpecificationFactory =
            $this->createPartialMock(\Magento\Payment\Model\Checks\SpecificationFactory::class, ['create']);
        $specification = $this->createMock(\Magento\Payment\Model\Checks\Composite::class);
        $specification->expects($this->at(0))->method('isApplicable')->willReturn(false);
        $specification->expects($this->at(1))->method('isApplicable')->willReturn(true);
        $this->methodSpecificationFactory->method('create')->willReturn($specification);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentMethodManagementPlugin = $objectManager->getObject(
            \Magento\CompanyPayment\Plugin\Quote\PaymentMethodManagementPlugin::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'methodSpecificationFactory' => $this->methodSpecificationFactory,
            ]
        );
    }

    /**
     * Test aroundGetList.
     */
    public function testAroundGetList()
    {
        $paymentMethodManagement = $this->createMock(\Magento\Quote\Api\PaymentMethodManagementInterface::class);
        $paymentMethod = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $additionalPaymentMethod = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $closure = function () use ($paymentMethod, $additionalPaymentMethod) {
            return [
                $paymentMethod,
                $additionalPaymentMethod,
            ];
        };

        $result = $this->paymentMethodManagementPlugin->aroundGetList($paymentMethodManagement, $closure, 1);
        $expectedResult = ['1' => $additionalPaymentMethod];

        $this->assertEquals($expectedResult, $result);
    }
}
