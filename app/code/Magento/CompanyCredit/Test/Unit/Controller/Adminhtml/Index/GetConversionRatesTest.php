<?php

namespace Magento\CompanyCredit\Test\Unit\Controller\Adminhtml\Index;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\ResourceModel\Company\CollectionFactory as CompanyCollectionFactory;
use Magento\Company\Model\ResourceModel\Company\Collection as CompanyCollection;
use Magento\CompanyCredit\Api\CreditLimitManagementInterface;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Controller\Adminhtml\Index\GetConversionRates;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Unit test for GetConversionRates class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetConversionRatesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var CompanyCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCollectionFactory;

    /**
     * @var CreditLimitManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitManagement;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var GetConversionRates
     */
    private $getConversionRates;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyCollectionFactory = $this->getMockBuilder(CompanyCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->creditLimitManagement = $this->getMockBuilder(CreditLimitManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->getConversionRates = $objectManager->getObject(
            GetConversionRates::class,
            [
                'filter' => $this->filter,
                'companyCollectionFactory' => $this->companyCollectionFactory,
                'creditLimitManagement' => $this->creditLimitManagement,
                'priceCurrency' => $this->priceCurrency,
                'logger' => $this->logger,
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request,
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $companyId = 1;
        $creditCurrency = 'USD';
        $newCurrency = 'EUR';
        $rate = 1.25;

        $result = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(ResultFactory::TYPE_JSON)->willReturn($result);
        $this->request->expects($this->at(0))->method('getParam')->with('currency_to')->willReturn($newCurrency);

        $company = $this->getMockBuilder(CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($companyId);
        $companyCollection = $this->getMockBuilder(CompanyCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($companyCollection);
        $this->filter->expects($this->once())
            ->method('getCollection')
            ->with($companyCollection)
            ->willReturn($companyCollection);
        $companyCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$company]);

        $currency = $this->getMockBuilder(Currency::class)->disableOriginalConstructor()->getMock();
        $this->priceCurrency->expects($this->once())->method('getCurrency')->willReturn($currency);
        $creditLimit = $this->getMockBuilder(CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willReturn($creditLimit);
        $creditLimit->expects($this->exactly(2))->method('getCurrencyCode')->willReturn($creditCurrency);
        $currency->expects($this->once())->method('getCurrencyRates')
            ->with($creditCurrency, [$newCurrency])->willReturn($rate);
        $result->expects($this->once())->method('setData')->with(
            [
                'status' => 'success',
                'currency_rates' => [
                    $creditCurrency => $rate,
                ]
            ]
        )->willReturnSelf();
        $this->assertEquals($result, $this->getConversionRates->execute());
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $newCurrency = 'EUR';
        $exception = new \Exception();
        $result = $this->getMockBuilder(ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(ResultFactory::TYPE_JSON)->willReturn($result);
        $this->request->expects($this->at(0))->method('getParam')->with('currency_to')->willReturn($newCurrency);
        $this->priceCurrency->expects($this->once())->method('getCurrency')->willThrowException($exception);
        $result->expects($this->once())->method('setData')
            ->with(
                [
                    'status' => 'error',
                    'error' => __(
                        'Unable to retrieve currency rates at this moment. '
                        . 'Please try again later or contact store administrator.'
                    )
                ]
            )
            ->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->assertEquals($result, $this->getConversionRates->execute());
    }
}
