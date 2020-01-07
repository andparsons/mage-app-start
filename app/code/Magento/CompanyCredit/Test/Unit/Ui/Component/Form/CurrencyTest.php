<?php

namespace Magento\CompanyCredit\Test\Unit\Ui\Component\Form;

/**
 * Class CurrencyTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeCurrency;

    /**
     * @var \Magento\CompanyCredit\Ui\Component\Form\Currency
     */
    private $currency;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->context = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class
        );
        $processor = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class
        );
        $this->context->expects(static::atLeastOnce())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(
            \Magento\Framework\View\Element\UiComponentFactory::class
        );
        $this->storeManager = $this->createMock(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->request = $this->createMock(
            \Magento\Framework\App\RequestInterface::class
        );
        $this->creditLimitManagement = $this->createPartialMock(
            \Magento\CompanyCredit\Api\CreditLimitManagementInterface::class,
            ['getCreditByCompanyId']
        );
        $this->localeCurrency = $this->createMock(
            \Magento\Framework\Locale\CurrencyInterface::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->currency = $objectManager->getObject(
            \Magento\CompanyCredit\Ui\Component\Form\Currency::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'storeManager' => $this->storeManager,
                'request' => $this->request,
                'creditLimitManagement' => $this->creditLimitManagement,
                'localeCurrency' => $this->localeCurrency,
            ]
        );
    }

    /**
     * Test prepare method.
     *
     * @param array $configData
     * @return void
     * @dataProvider prepareDataProvider
     */
    public function testPrepare(array $configData)
    {
        $baseCurrencyCode = 'USD';
        $companyId = 1;
        $creditLimitCurrencyCode = 'EUR';
        $creditLimitCurrencyLabel = 'Euro';
        $uiComponent = $this->createMock(
            \Magento\Framework\View\Element\UiComponentInterface::class
        );
        $context = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class
        );
        $context->method('getNamespace')->willReturn('namespace');
        $uiComponent->method('getContext')->willReturn($context);
        $uiComponent->method('getData')->willReturn($configData['config']);
        $this->uiComponentFactory->method('create')->willReturn($uiComponent);
        $website = $this->createMock(
            \Magento\Store\Model\Website::class
        );
        $website->expects(static::once())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $this->storeManager->expects(static::once())->method('getWebsite')->willReturn($website);
        $this->request->expects(static::once())->method('getParam')->with('id')->willReturn($companyId);
        $creditLimit = $this->createMock(
            \Magento\CompanyCredit\Api\Data\CreditLimitInterface::class
        );
        $creditLimit->method('getCurrencyCode')->willReturn($creditLimitCurrencyCode);
        $this->creditLimitManagement->expects(static::any())->method('getCreditByCompanyId')
            ->with($companyId)
            ->willReturn($creditLimit);
        $currency = $this->createMock(
            \Magento\Framework\Currency::class
        );
        $currency->method('getName')->willReturn($creditLimitCurrencyLabel);
        $this->localeCurrency->method('getCurrency')
            ->with($creditLimitCurrencyCode)
            ->willReturn($currency);
        $this->currency->setData($configData);
        $this->currency->prepare();
        $this->assertEquals(
            $this->currency->getData('config/options/0/value'),
            $creditLimitCurrencyCode
        );
    }

    /**
     * DataProvider for prepare method.
     *
     * @return array
     */
    public function prepareDataProvider()
    {
        return [
            [
                [
                    'config' => [
                        'formElement' => 'element'
                    ]
                ]

            ],
            [
                [
                    'config' => [
                        'formElement' => 'element',
                        'options' => [0 => ['value' => 'EUR']]
                    ]
                ]
            ]
        ];
    }
}
