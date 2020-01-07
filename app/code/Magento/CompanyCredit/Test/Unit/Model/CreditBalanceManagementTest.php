<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

use Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Magento\CompanyCredit\Model\ResourceModel\History\Collection as HistoryCollection;

/**
 * Unit tests for CreditBalanceManagement model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditBalanceManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitHistory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitHistory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitResource;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var HistoryCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyCollectionFactoryMock;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCurrencyMock;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalanceManagement
     */
    private $creditBalanceManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditLimitRepository = $this
            ->getMockBuilder(\Magento\CompanyCredit\Api\CreditLimitRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitHistory = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimitHistory::class)
            ->disableOriginalConstructor()->getMock();
        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->creditLimitResource = $this
            ->getMockBuilder(\Magento\CompanyCredit\Model\ResourceModel\CreditLimit::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyCollectionFactoryMock = $this->getMockBuilder(HistoryCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->websiteCurrencyMock = $this->getMockBuilder(\Magento\CompanyCredit\Model\WebsiteCurrency::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditBalanceManagement = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CreditBalanceManagement::class,
            [
                'creditLimitRepository' => $this->creditLimitRepository,
                'creditLimitHistory' => $this->creditLimitHistory,
                'priceCurrency' => $this->priceCurrency,
                'creditLimitResource' => $this->creditLimitResource,
                'customerRepository' => $this->customerRepository,
                'websiteCurrency' => $this->websiteCurrencyMock,
                'historyCollectionFactory' => $this->historyCollectionFactoryMock
            ]
        );
    }

    /**
     * Test for method decrease.
     *
     * @return void
     */
    public function testDecrease()
    {
        $data = [
            'balanceId' => 1,
            'status' =>  \Magento\CompanyCredit\Model\HistoryInterface::TYPE_PURCHASED,
            'amount' => 10,
            'currency' => 'USD',
            'comment' => 'Some comment',
            'orderNumber' => '00001',
            'purchaseOrder' => 'O123',
        ];
        $options = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditBalanceOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $data['options'] = $options;
        $options->expects($this->atLeastOnce())->method('getData')->with('order_increment')->willReturn('00001');
        $this->creditLimitResource->expects($this->once())
            ->method('changeBalance')->with($data['balanceId'], -$data['amount'], $data['currency']);
        $credit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditLimitRepository->expects($this->once())->method('get')
            ->with($data['balanceId'], true)->willReturn($credit);
        $this->creditLimitHistory->expects($this->once())->method('logCredit')->with(
            $credit,
            $data['status'],
            -$data['amount'],
            $data['currency'],
            $data['comment'],
            ['order' => $data['orderNumber']],
            $data['options']
        );
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connection->expects($this->once())->method('commit');
        $this->creditLimitResource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->websiteCurrencyMock->expects($this->once())->method('isCreditCurrencyEnabled')
            ->with($data['currency'])->willReturn(true);

        $this->creditBalanceManagement->decrease(
            $data['balanceId'],
            $data['amount'],
            $data['currency'],
            $data['status'],
            $data['comment'],
            $data['options']
        );
    }

    /**
     * Test for method increase.
     *
     * @return void
     */
    public function testIncrease()
    {
        $data = [
            'balanceId' => 1,
            'status' =>  \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REFUNDED,
            'amount' => 10,
            'currency' => 'USD',
            'comment' => 'Some comment',
            'orderNumber' => '00001',
            'purchaseOrder' => 'O123',
        ];
        $options = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditBalanceOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $data['options'] = $options;
        $options->expects($this->atLeastOnce())->method('getData')->with('order_increment')->willReturn('00001');
        $this->creditLimitResource->expects($this->once())
            ->method('changeBalance')->with($data['balanceId'], $data['amount'], $data['currency']);
        $credit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditLimitRepository->expects($this->once())->method('get')
            ->with($data['balanceId'], true)->willReturn($credit);
        $this->creditLimitHistory->expects($this->once())->method('logCredit')->with(
            $credit,
            $data['status'],
            $data['amount'],
            $data['currency'],
            $data['comment'],
            ['order' => $data['orderNumber']],
            $data['options']
        );
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connection->expects($this->once())->method('commit');
        $this->creditLimitResource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->websiteCurrencyMock->expects($this->once())->method('isCreditCurrencyEnabled')
            ->with($data['currency'])->willReturn(true);

        $this->creditBalanceManagement->increase(
            $data['balanceId'],
            $data['amount'],
            $data['currency'],
            $data['status'],
            $data['comment'],
            $data['options']
        );
    }

    /**
     * Test for method increase.
     *
     * @return void
     */
    public function testIncreaseWithAllocatedStatus()
    {
        $data = [
            'balanceId' => 1,
            'status' =>  \Magento\CompanyCredit\Model\HistoryInterface::TYPE_ALLOCATED,
            'amount' => 10,
            'currency' => 'USD',
            'comment' => 'Some comment',
            'orderNumber' => '00001',
            'purchaseOrder' => 'O123',
        ];
        $options = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditBalanceOptions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data['options'] = $options;
        $creditCurrencyCode = 'EUR';
        $creditCurrencyRate = 2;
        $creditLimitValue = 100;
        $creditLimitValueToSet = $creditLimitValue + $data['amount'] * 2;

        $this->prepareHistoryCollectionMock($data['balanceId'], 0);
        $creditMock = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrencyCode', 'getCreditLimit', 'setCreditLimit', 'setData'])
            ->getMockForAbstractClass();
        $this->creditLimitRepository->expects($this->once())->method('get')
            ->with($data['balanceId'])->willReturn($creditMock);
        $creditMock->expects($this->exactly(2))->method('getCurrencyCode')->willReturn($creditCurrencyCode);
        $currencyMock = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRate'])
            ->getMock();
        $this->priceCurrency->expects($this->once())->method('getCurrency')->with(null, $creditCurrencyCode)
            ->willReturn($currencyMock);
        $currencyMock->expects($this->once())->method('getRate')->willReturn($creditCurrencyRate);
        $creditMock->expects($this->once())->method('getCreditLimit')->willReturn($creditLimitValue);
        $creditMock->expects($this->once())->method('setCreditLimit')->with($creditLimitValueToSet);
        $creditMock->expects($this->once())->method('setData')->with('credit_comment', $data['comment']);
        $this->creditLimitRepository->expects($this->once())->method('save')->with($creditMock);
        $this->websiteCurrencyMock->expects($this->once())->method('isCreditCurrencyEnabled')
            ->with($data['currency'])->willReturn(true);

        $this->creditBalanceManagement->increase(
            $data['balanceId'],
            $data['amount'],
            $data['currency'],
            $data['status'],
            $data['comment'],
            $data['options']
        );
    }

    /**
     * Test for method increase with rollBack transaction.
     *
     * @return void
     * @expectedException \Exception
     */
    public function testIncreaseWithRollBack()
    {
        $data = [
            'balanceId' => 1,
            'status' =>  \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REFUNDED,
            'amount' => 10,
            'currency' => 'USD',
            'comment' => 'Some comment',
        ];
        $this->creditLimitResource->expects($this->once())
            ->method('changeBalance')->with($data['balanceId'], $data['amount'], $data['currency'])
            ->willThrowException(new \Exception());
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connection->expects($this->once())->method('rollBack');
        $this->creditLimitResource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->websiteCurrencyMock->expects($this->once())->method('isCreditCurrencyEnabled')
            ->with($data['currency'])->willReturn(true);

        $this->creditBalanceManagement->increase(
            $data['balanceId'],
            $data['amount'],
            $data['currency'],
            $data['status'],
            $data['comment']
        );
    }

    /**
     * Test for method decrease with rollBack transaction.
     *
     * @return void
     * @expectedException \Exception
     */
    public function testDecreaseWithRollBack()
    {
        $data = [
            'balanceId' => 1,
            'status' =>  \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REFUNDED,
            'amount' => 10,
            'currency' => 'USD',
            'comment' => 'Some comment',
        ];
        $this->creditLimitResource->expects($this->once())
            ->method('changeBalance')->with($data['balanceId'], $data['amount'], $data['currency'])
            ->willThrowException(new \Exception());
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connection->expects($this->once())->method('rollBack');
        $this->creditLimitResource->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->websiteCurrencyMock->expects($this->once())->method('isCreditCurrencyEnabled')
            ->with($data['currency'])->willReturn(true);

        $this->creditBalanceManagement->increase(
            $data['balanceId'],
            $data['amount'],
            $data['currency'],
            $data['status'],
            $data['comment']
        );
    }

    /**
     * Test for method increase with InputException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Cannot process the request. Please check the operation type and try again.
     */
    public function testIncreaseWithException()
    {
        $creditId = 1;
        $this->prepareHistoryCollectionMock($creditId, 1);

        $this->creditBalanceManagement->increase(
            $creditId,
            10,
            null,
            \Magento\CompanyCredit\Model\HistoryInterface::TYPE_ALLOCATED,
            null,
            null
        );
    }

    /**
     * Prepare history collection mock.
     *
     * @param int $creditId
     * @param int $collectionSize
     * @return void
     */
    private function prepareHistoryCollectionMock($creditId, $collectionSize)
    {
        $historyCollectionMock = $this->getMockBuilder(HistoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($historyCollectionMock);
        $historyCollectionMock->expects($this->once())->method('addFieldToFilter')
            ->with('company_credit_id', ['eq' => $creditId]);
        $historyCollectionMock->expects($this->once())->method('getSize')->willReturn($collectionSize);
    }

    /**
     * Test for method increase with InputException when credit balance value is incorrect.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid attribute value. Row ID: value = -1.
     */
    public function testIncreaseWithInputExceptionForInvalidBalanceValue()
    {
        $this->creditBalanceManagement->increase(
            1,
            -1,
            null,
            \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REFUNDED,
            null,
            null
        );
    }

    /**
     * Test for method increase with InputException when credit balance ID is incorrect.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage "balanceId" is required. Enter and try again.
     */
    public function testIncreaseWithInputExceptionForInvalidBalanceId()
    {
        $this->creditBalanceManagement->increase(
            null,
            10,
            null,
            \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REFUNDED,
            null,
            null
        );
    }

    /**
     * Test for method increase with InputException when credit balance currency is incorrect.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid attribute value. Row ID: currency = USD.
     */
    public function testIncreaseWithInputExceptionForInvalidBalanceCurrency()
    {
        $currency = 'USD';
        $this->websiteCurrencyMock->expects($this->once())->method('isCreditCurrencyEnabled')
            ->with($currency)->willReturn(false);

        $this->creditBalanceManagement->increase(
            1,
            10,
            $currency,
            \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REFUNDED,
            null,
            null
        );
    }
}
