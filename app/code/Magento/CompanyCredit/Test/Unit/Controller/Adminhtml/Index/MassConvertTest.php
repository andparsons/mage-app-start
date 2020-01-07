<?php

namespace Magento\CompanyCredit\Test\Unit\Controller\Adminhtml\Index;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\ResourceModel\Company\CollectionFactory as CompanyCollectionFactory;
use Magento\Company\Model\ResourceModel\Company\Collection as CompanyCollection;
use Magento\CompanyCredit\Api\CreditLimitManagementInterface;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Api\CreditLimitRepositoryInterface;
use Magento\CompanyCredit\Controller\Adminhtml\Index\MassConvert;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Unit tests for MassConvert controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassConvertTest extends \PHPUnit\Framework\TestCase
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
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var CreditLimitRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitRepository;

    /**
     * @var MassConvert
     */
    private $massConvert;

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
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditLimitRepository = $this->getMockBuilder(CreditLimitRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->massConvert = $objectManager->getObject(
            MassConvert::class,
            [
                'filter' => $this->filter,
                'companyCollectionFactory' => $this->companyCollectionFactory,
                'creditLimitManagement' => $this->creditLimitManagement,
                'creditLimitRepository' => $this->creditLimitRepository,
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
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
        $oldCreditLimit = 10;
        $oldCurrency = 'USD';
        $newCurrency = 'EUR';
        $rate = 1.25;
        $rates = [$oldCurrency => $rate];

        $result = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)->willReturn($result);
        $this->request->expects($this->at(0))->method('getParam')->with('currency_rates')->willReturn($rates);
        $this->request->expects($this->at(1))->method('getParam')->with('currency_to')->willReturn($newCurrency);

        $company = $this->getMockBuilder(CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($companyId);
        $this->setUpFilterMock([$company]);

        $creditLimit = $this->getMockBuilder(CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrencyCode', 'getCreditLimit', 'setCurrencyRate'])
            ->getMockForAbstractClass();
        $creditLimit->expects($this->once())
            ->method('getCreditLimit')
            ->willReturn($oldCreditLimit);
        $creditLimit->expects($this->once())
            ->method('setCurrencyCode')
            ->with($newCurrency)
            ->willReturnSelf();
        $creditLimit->expects($this->once())
            ->method('setCurrencyRate')
            ->with($rate)
            ->willReturnSelf();
        $creditLimit->expects($this->once())
            ->method('setCreditLimit')
            ->with($rate * $oldCreditLimit)
            ->willReturnSelf();
        $this->creditLimitManagement->expects($this->exactly(1))
            ->method('getCreditByCompanyId')->with($companyId)
            ->willReturn($creditLimit);
        $creditLimit->expects($this->exactly(1))->method('getCurrencyCode')
            ->willReturnOnConsecutiveCalls($oldCurrency, $newCurrency);
        $this->creditLimitRepository->expects($this->once())->method('save')->with($creditLimit);
        $this->messageManager->expects($this->once())->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) were updated.', 1))->willReturnSelf();
        $this->assertEquals($result, $this->massConvert->execute());
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $companyId = 1;
        $oldCurrency = 'USD';
        $newCurrency = 'EUR';
        $rates = [$oldCurrency => 1.25];
        $exception = new \Exception();
        $result = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)->willReturn($result);
        $this->request->expects($this->at(0))->method('getParam')->with('currency_rates')->willReturn($rates);
        $this->request->expects($this->at(1))->method('getParam')->with('currency_to')->willReturn($newCurrency);

        $company = $this->getMockBuilder(CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($companyId);
        $this->setUpFilterMock([$company]);

        $this->creditLimitManagement->expects($this->once())
            ->method('getCreditByCompanyId')->with($companyId)->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addExceptionMessage')
            ->with(
                $exception,
                __('Unable to convert company credit. Please try again later or contact store administrator.')
            )->willReturnSelf();
        $this->assertEquals($result, $this->massConvert->execute());
    }

    /**
     * Test for method execute with localized exception.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $companyId = 1;
        $oldCurrency = 'USD';
        $newCurrency = 'EUR';
        $rates = [$oldCurrency => 1.25];
        $exceptionMessage = 'Exception Message';
        $result = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)->willReturn($result);
        $this->request->expects($this->at(0))->method('getParam')->with('currency_rates')->willReturn($rates);
        $this->request->expects($this->at(1))->method('getParam')->with('currency_to')->willReturn($newCurrency);

        $company = $this->getMockBuilder(CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($companyId);
        $this->setUpFilterMock([$company]);

        $this->creditLimitManagement->expects($this->once())->method('getCreditByCompanyId')->with($companyId)
            ->willThrowException(new LocalizedException(__($exceptionMessage)));
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')->with($exceptionMessage)->willReturnSelf();
        $this->assertEquals($result, $this->massConvert->execute());
    }

    /**
     * Set up massaction filter mock.
     *
     * @param array $companies
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpFilterMock(array $companies)
    {
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
            ->willReturn($companies);

        return $companyCollection;
    }
}
