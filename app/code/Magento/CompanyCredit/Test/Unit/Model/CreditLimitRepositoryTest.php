<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Unit tests for CreditLimitRepository model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditLimitRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitFactory;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitResource;

    /**
     * @var \Magento\CompanyCredit\Model\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorMock;

    /**
     * @var \Magento\CompanyCredit\Model\SaveHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveHandlerMock;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimit\SearchProvider
     */
    private $searchProvider;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitRepository
     */
    private $creditLimitRepository;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditLimitFactory = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimitFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->creditLimitResource =
            $this->getMockBuilder(\Magento\CompanyCredit\Model\ResourceModel\CreditLimit::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validatorMock = $this->getMockBuilder(\Magento\CompanyCredit\Model\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saveHandlerMock = $this->getMockBuilder(\Magento\CompanyCredit\Model\SaveHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchProvider = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimit\SearchProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditLimitRepository = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CreditLimitRepository::class,
            [
                'creditLimitFactory'  => $this->creditLimitFactory,
                'creditLimitResource' => $this->creditLimitResource,
                'validator'           => $this->validatorMock,
                'saveHandler'         => $this->saveHandlerMock,
                'searchProvider'      => $this->searchProvider,
            ]
        );
    }

    /**
     * Test for method save.
     *
     * @return void
     */
    public function testSave()
    {
        $creditLimitId = 1;
        $creditLimitMock = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'getId', 'getCompanyId', 'getCurrencyCode'])
            ->getMockForAbstractClass();
        $creditLimitData = [\Magento\CompanyCredit\Api\Data\CreditLimitInterface::CURRENCY_CODE => 'USD'];
        $creditLimitMock->expects($this->once())->method('getData')->willReturn($creditLimitData);
        $creditLimitMock->expects($this->any())->method('getId')->willReturn($creditLimitId);
        $originalCreditLimitMock = $this->prepareGetCreditLimitMocks($creditLimitId);
        $originalCreditLimitMock->expects($this->any())->method('getId')->willReturn(null);
        $this->validatorMock->expects($this->once())->method('validateCreditData')->with($creditLimitData);
        $originalCreditLimitMock->expects($this->once())->method('getCurrencyCode')->willReturn('EUR');
        $this->saveHandlerMock->expects($this->once())->method('execute')->with($creditLimitMock)->willReturnSelf();
        $this->creditLimitResource->expects($this->once())->method('delete')->with($originalCreditLimitMock)
            ->willReturnSelf();
        $this->assertEquals($creditLimitMock, $this->creditLimitRepository->save($creditLimitMock));
    }

    /**
     * Prepare mocks for credit limit repository get() method.
     *
     * @param int $creditLimitId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareGetCreditLimitMocks($creditLimitId)
    {
        $companyId = 2;
        $originalCreditLimitMock = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimit::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'getId', 'getCompanyId', 'getCurrencyCode'])
            ->getMockForAbstractClass();
        $this->creditLimitFactory->expects($this->once())->method('create')->willReturn($originalCreditLimitMock);
        $this->creditLimitResource->expects($this->once())
            ->method('load')->with($originalCreditLimitMock, $creditLimitId)->willReturnSelf();
        $this->validatorMock->expects($this->once())->method('checkCompanyCreditExist')
            ->with($originalCreditLimitMock);
        $originalCreditLimitMock->expects($this->any())->method('getCompanyId')->willReturn($companyId);

        return $originalCreditLimitMock;
    }

    /**
     * Test for method get.
     *
     * @return void
     */
    public function testGet()
    {
        $creditLimitId = 1;
        $creditLimit = $this->prepareGetCreditLimitMocks($creditLimitId);

        $this->assertEquals($creditLimit, $this->creditLimitRepository->get($creditLimitId));
    }

    /**
     * Test for method delete.
     *
     * @return void
     */
    public function testDelete()
    {
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditLimit->expects($this->once())->method('getId')->willReturn(1);
        $this->creditLimitResource->expects($this->once())->method('delete')->with($creditLimit)->willReturnSelf();
        $this->assertTrue($this->creditLimitRepository->delete($creditLimit));
    }

    /**
     * Test for method delete with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Cannot delete credit limit with id 1
     */
    public function testDeleteWithException()
    {
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditLimit->expects($this->exactly(2))->method('getId')->willReturn(1);
        $this->creditLimitResource->expects($this->once())->method('delete')->with($creditLimit)
            ->willThrowException(new \Exception('Exception message'));
        $this->creditLimitRepository->delete($creditLimit);
    }

    /**
     * Test for method getList.
     *
     * @return void
     */
    public function testGetList()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchProvider->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $this->assertEquals($searchResults, $this->creditLimitRepository->getList($searchCriteria));
    }
}
