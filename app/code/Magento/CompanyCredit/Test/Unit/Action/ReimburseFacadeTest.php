<?php
namespace Magento\CompanyCredit\Test\Unit\Action;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\CompanyCredit\Action\ReimburseFacade;
use Magento\CompanyCredit\Api\CreditBalanceManagementInterface;
use Magento\CompanyCredit\Api\CreditLimitManagementInterface;
use Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface;
use Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterfaceFactory;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for \Magento\CompanyCredit\Action\ReimburseFacade class.
 */
class ReimburseFacadeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReimburseFacade
     */
    private $reimburseFacade;

    /**
     * @var CreditLimitManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitManagement;

    /**
     * @var CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var CreditBalanceManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditBalanceManagement;

    /**
     * @var CreditBalanceOptionsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditBalanceOptionsFactory;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->creditLimitManagement = $this->getMockBuilder(CreditLimitManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository = $this->getMockBuilder(CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditBalanceManagement = $this->getMockBuilder(CreditBalanceManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditBalanceOptionsFactory = $this->getMockBuilder(CreditBalanceOptionsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->reimburseFacade = (new ObjectManager($this))->getObject(
            ReimburseFacade::class,
            [
                'creditLimitManagement' => $this->creditLimitManagement,
                'companyRepository' => $this->companyRepository,
                'creditBalanceManagement' => $this->creditBalanceManagement,
                'creditBalanceOptionsFactory' => $this->creditBalanceOptionsFactory
            ]
        );
    }

    /**
     * Test `execute` method.
     *
     * @param float $amount
     * @param string $reimburseMethod
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($amount, $reimburseMethod)
    {
        $companyId = 1;
        $creditId = 2;
        $currencyCode = 'USD';
        $comment = 'Comment';
        $purchaseOrder = '100001223';

        $company = $this->getMockBuilder(CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($companyId);
        $this->companyRepository->expects($this->once())
            ->method('get')
            ->with($companyId)
            ->willReturn($company);
        $credit = $this->getMockBuilder(CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $credit->expects($this->once())
            ->method('getId')
            ->willReturn($creditId);
        $this->creditLimitManagement->expects($this->exactly(2))
            ->method('getCreditByCompanyId')
            ->with($companyId)
            ->willReturn($credit);
        $credit->expects($this->once())->method('getCurrencyCode')->willReturn($currencyCode);

        $options = $this->getMockBuilder(CreditBalanceOptionsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $options->expects($this->atLeastOnce())
            ->method('setPurchaseOrder')
            ->with($purchaseOrder);
        $this->creditBalanceOptionsFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($options);

        $this->creditBalanceManagement->expects($this->once())->method($reimburseMethod)
            ->with(
                $creditId,
                abs($amount),
                $currencyCode,
                HistoryInterface::TYPE_REIMBURSED,
                $comment,
                $options
            );

        $this->assertEquals(
            $credit,
            $this->reimburseFacade->execute($companyId, $amount, $comment, $purchaseOrder)
        );
    }

    /**
     * Test `execute` method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Exception message.
     */
    public function testExecuteWithException()
    {
        $companyId = 1;

        $this->companyRepository->expects($this->once())
            ->method('get')
            ->with($companyId)
            ->willThrowException(new NoSuchEntityException(__('Exception message.')));

        $this->reimburseFacade->execute($companyId, 100, '', '');
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [10.0, 'increase'],
            [-10.0, 'decrease']
        ];
    }
}
