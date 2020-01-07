<?php
namespace Magento\CompanyCredit\Test\Unit\Model\Email;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\CompanyCredit\Api\CreditLimitRepositoryInterface;
use Magento\CompanyCredit\Model\Sales\OrderLocator;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Unit tests for CompanyCreditDataFactory.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyCreditDataFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProcessor;

    /**
     * @var CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var CreditLimitRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitRepository;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceFormatter;

    /**
     * @var CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelper;

    /**
     * @var OrderLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderLocator;

    /**
     * @var \Magento\CompanyCredit\Model\Email\CompanyCreditDataFactory
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dataProcessor = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyRepository = $this->getMockBuilder(CompanyRepositoryInterface::class)
            ->getMock();
        $this->creditLimitRepository = $this->getMockBuilder(CreditLimitRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->priceFormatter = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMockForAbstractClass();
        $this->customerViewHelper = $this->getMockBuilder(CustomerNameGenerationInterface::class)
            ->getMockForAbstractClass();
        $this->orderLocator = $this->getMockBuilder(OrderLocator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $serializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);
        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->model = $objectManager->getObject(
            \Magento\CompanyCredit\Model\Email\CompanyCreditDataFactory::class,
            [
                'dataProcessor' => $this->dataProcessor,
                'companyRepository' => $this->companyRepository,
                'creditLimitRepository' => $this->creditLimitRepository,
                'priceFormatter' => $this->priceFormatter,
                'customerViewHelper' => $this->customerViewHelper,
                'orderLocator' => $this->orderLocator,
                'serializer' => $serializer
            ]
        );
    }

    /**
     * Test getCompanyCreditDataObject method.
     *
     * @param array $data
     * @return void
     * @dataProvider getCompanyCreditDataObjectDataProvider
     */
    public function testGetCompanyCreditDataObject(array $data)
    {
        $companyCreditId = 1;
        $companyId = 1;
        $history = $this->getMockForAbstractClass(
            \Magento\CompanyCredit\Model\HistoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            [
                'getCompanyCreditId',
                'getComment',
                'getCreditLimit',
                'getCurrencyCredit',
                'getBalance',
                'getAmount',
                'getCurrencyOperation'
            ]
        );
        $customer = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $creditLimit = $this->createMock(
            \Magento\CompanyCredit\Api\Data\CreditLimitInterface::class
        );
        $company = $this->createMock(
            \Magento\Company\Api\Data\CompanyInterface::class
        );
        $history->expects($this->once())->method('getCompanyCreditId')->willReturn($companyCreditId);
        $this->creditLimitRepository->expects($this->once())
            ->method('get')
            ->with($companyCreditId)
            ->willReturn($creditLimit);
        $creditLimit->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->companyRepository->expects($this->once())
            ->method('get')
            ->with($companyId)
            ->willReturn($company);
        $this->dataProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($history, \Magento\CompanyCredit\Model\HistoryInterface::class)
            ->willReturn([]);
        $comment = json_encode(['system' => ['order' => 7]]);
        $history->expects($this->any())->method('getComment')->willReturn($comment);
        $history->expects($this->once())->method('getCreditLimit')->willReturn(500);
        $history->expects($this->exactly(4))->method('getCurrencyCredit')->willReturn('USD');
        $history->expects($this->once())->method('getBalance')->willReturn(200);
        $history->expects($this->once())->method('getCurrencyOperation')->willReturn('EUR');
        $history->expects($this->once())->method('getAmount')->willReturn(100);
        $history->expects($this->once())->method('getRate')->willReturn(1);
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->orderLocator->expects($this->once())->method('getOrderByIncrementId')->willReturn($order);
        $order->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->priceFormatter->expects($this->exactly(3))
            ->method('format')
            ->withConsecutive(
                [500, false, PriceCurrencyInterface::DEFAULT_PRECISION, 1, 'USD'],
                [200, false, PriceCurrencyInterface::DEFAULT_PRECISION, 1, 'USD'],
                [100, false, PriceCurrencyInterface::DEFAULT_PRECISION, 1, 'USD']
            )
            ->willReturnOnConsecutiveCalls(
                ['$500'],
                ['$200'],
                ['$100']
            );
        $creditLimit->expects($this->once())->method('getExceedLimit')->willReturn(1);
        $company->expects($this->once())->method('getCompanyName')->willReturn('Test Company');
        $this->customerViewHelper->expects($this->once())
            ->method('getCustomerName')->with($customer)->willReturn('Firstname Lastname');

        $companyCreditDataObject = new \Magento\Framework\DataObject();
        $companyCreditDataObject->setData($data);
        $this->assertEquals(
            $companyCreditDataObject,
            $this->model->getCompanyCreditDataObject($history, $customer)
        );
    }

    /**
     * Data provider for getCompanyCreditDataObject method.
     *
     * @return array
     */
    public function getCompanyCreditDataObjectDataProvider()
    {
        $data = [
            'availableCredit' => ['$500'],
            'outStandingBalance' => ['$200'],
            'exceedLimit' => 'allowed',
            'operationAmount' => ['$100'],
            'orderId' => 7,
            'companyName' => 'Test Company',
            'customerName' => 'Firstname Lastname'
        ];

        return [[$data]];
    }
}
