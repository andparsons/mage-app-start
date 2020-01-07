<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Test for CompanyRepository.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\CompanyRepository
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyFactory;

    /**
     * @var \Magento\Company\Model\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyResource;

    /**
     * @var \Magento\Company\Model\Company\GetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyListGetter;

    /**
     * @var \Magento\Company\Model\Company\Delete|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyDeleter;

    /**
     * @var \Magento\Company\Model\Company\Save|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companySaver;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyFactory = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->companyResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyListGetter = $this->getMockBuilder(\Magento\Company\Model\Company\GetList::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyDeleter = $this->getMockBuilder(\Magento\Company\Model\Company\Delete::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companySaver = $this->getMockBuilder(\Magento\Company\Model\Company\Save::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyRepository = $objectManagerHelper->getObject(
            \Magento\Company\Model\CompanyRepository::class,
            [
                'companyFactory' => $this->companyFactory,
                'companyDeleter' => $this->companyDeleter,
                'companyListGetter' => $this->companyListGetter,
                'companySaver' => $this->companySaver
            ]
        );
    }

    /**
     * Test get method.
     *
     * @return void
     */
    public function testGet()
    {
        $this->companyFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->company);
        $this->company->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $this->company->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->assertEquals($this->company, $this->companyRepository->get(1));
    }

    /**
     * Test get method throws NoSuchEntityException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetWithException()
    {
        $this->companyFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->company);
        $this->company->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $this->company->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $this->assertEquals($this->company, $this->companyRepository->get(1));
    }

    /**
     * Test getList.
     *
     * @param int $count
     * @param int $expectedResult
     * @return void
     * @dataProvider getListDataProvider
     */
    public function testGetList($count, $expectedResult)
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\Search\SearchCriteria::class);

        $this->companyListGetter->expects($this->once())->method('getList')->will($this->returnValue($count));
        $result = $this->companyRepository->getList($searchCriteria);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider fot testGetList.
     *
     * @return array
     */
    public function getListDataProvider()
    {
        return [
            [0, 0],
            [1, 1]
        ];
    }

    /**
     * Test for method save.
     *
     * @return void
     */
    public function testSave()
    {
        $this->assertEquals($this->company, $this->companyRepository->save($this->company));
    }

    /**
     * Test save method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save company
     */
    public function testSaveWithException()
    {
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__('Could not save company'));
        $this->companySaver->expects($this->once())->method('save')->willThrowException($exception);
        $this->companyRepository->save($this->company);
    }

    /**
     * Test for method delete.
     *
     * @return void
     */
    public function testDelete()
    {
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $companyAttributes->expects($this->any())->method('getCompanyId')
            ->willReturn(1);

        $this->company->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->assertTrue($this->companyRepository->delete($this->company));
    }

    /**
     * Test delete method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Cannot delete company with id 1
     */
    public function testDeleteWithException()
    {
        $exception = new \Exception;
        $this->company->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->setUpCustomerDelete();
        $this->companyDeleter->expects($this->once())->method('delete')->willThrowException($exception);
        $this->companyRepository->delete($this->company);
    }

    /**
     * Test for method deleteById.
     *
     * @return void
     */
    public function testDeleteById()
    {
        $this->companyFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->company);
        $this->company->expects($this->atLeastOnce())->method('load')->will($this->returnSelf());
        $this->company->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->setUpCustomerDelete();
        $this->assertTrue($this->companyRepository->deleteById(1));
    }

    /**
     * Processes attached customers upon company deletion.
     *
     * @return void
     */
    private function setUpCustomerDelete()
    {
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $companyAttributes->expects($this->any())->method('getCompanyId')
            ->willReturn(1);
        $customerExtension = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCompanyAttributes', 'getCompanyAttributes'])
            ->getMockForAbstractClass();
        $customerExtension->expects($this->any())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtension);
    }
}
