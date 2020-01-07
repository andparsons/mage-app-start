<?php

namespace Magento\CompanyPayment\Test\Unit\Model\Source;

/**
 * Class PaymentMethodTest.
 */
class PaymentMethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Store\Api\StoreResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeResolver;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\CompanyPayment\Model\Source\PaymentMethod
     */
    private $paymentMethod;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->paymentMethodList = $this->createMock(
            \Magento\Payment\Api\PaymentMethodListInterface::class
        );
        $this->storeResolver = $this->createMock(
            \Magento\Store\Api\StoreResolverInterface::class
        );
        $this->appState = $this->createMock(
            \Magento\Framework\App\State::class
        );

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentMethod = $objectManager->getObject(
            \Magento\CompanyPayment\Model\Source\PaymentMethod::class,
            [
                'paymentMethodList' => $this->paymentMethodList,
                'storeResolver' => $this->storeResolver,
                'appState' => $this->appState,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * Test for method toOptionArray.
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $storeId = 1;
        $paymentMethodNames = ['paymentMethod3', 'paymentMethod1', 'paymentMethod1'];
        $paymentMethodCodes = ['PM1', 'PM2', 'PM3'];

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMockForAbstractClass();

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $paymentMethod1 = $this->createMock(
            \Magento\Payment\Api\Data\PaymentMethodInterface::class
        );
        $paymentMethod2 = $this->createMock(
            \Magento\Payment\Api\Data\PaymentMethodInterface::class
        );
        $paymentMethod3 = $this->createMock(
            \Magento\Payment\Api\Data\PaymentMethodInterface::class
        );
        $this->appState->expects($this->once())
            ->method('getAreaCode')->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);
        $paymentMethod1->expects($this->atLeastOnce())->method('getTitle')->willReturn($paymentMethodNames[0]);
        $paymentMethod2->expects($this->atLeastOnce())->method('getTitle')->willReturn($paymentMethodNames[1]);
        $paymentMethod3->expects($this->atLeastOnce())->method('getTitle')->willReturn($paymentMethodNames[2]);
        $paymentMethod1->expects($this->exactly(2))->method('getCode')->willReturn($paymentMethodCodes[0]);
        $paymentMethod2->expects($this->exactly(3))->method('getCode')->willReturn($paymentMethodCodes[1]);
        $paymentMethod3->expects($this->exactly(3))->method('getCode')->willReturn($paymentMethodCodes[2]);
        $paymentMethod1->expects($this->once())->method('getIsActive')->willReturn(false);
        $paymentMethod2->expects($this->once())->method('getIsActive')->willReturn(true);
        $paymentMethod3->expects($this->once())->method('getIsActive')->willReturn(true);
        $this->paymentMethodList->expects($this->once())->method('getList')
            ->with($storeId)->willReturn([$paymentMethod1, $paymentMethod2, $paymentMethod3]);
        $this->assertEquals(
            [
                ['value' => $paymentMethodCodes[2], 'label' => $paymentMethodNames[2] . ' ' . $paymentMethodCodes[2]],
                ['value' => $paymentMethodCodes[1], 'label' => $paymentMethodNames[1] . ' ' . $paymentMethodCodes[1]],
                ['value' => $paymentMethodCodes[0], 'label' => $paymentMethodNames[0] . ' (disabled)'],
            ],
            $this->paymentMethod->toOptionArray()
        );
    }
}
