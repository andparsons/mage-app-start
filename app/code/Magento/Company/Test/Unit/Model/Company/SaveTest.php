<?php

namespace Magento\Company\Test\Unit\Model\Company;

/**
 * Unit test for Magento\Company\Model\Company\Save class.
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\SaveHandlerPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveHandlerPool;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyResource;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyFactory;

    /**
     * @var \Magento\Company\Model\SaveValidatorPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveValidatorPool;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userCollectionFactory;

    /**
     * @var \Magento\Company\Model\Company\Save
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->saveHandlerPool = $this->getMockBuilder(\Magento\Company\Model\SaveHandlerPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyFactory = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->saveValidatorPool = $this->getMockBuilder(\Magento\Company\Model\SaveValidatorPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userCollectionFactory = $this
            ->getMockBuilder(\Magento\User\Model\ResourceModel\User\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\Company\Save::class,
            [
                'saveHandlerPool' => $this->saveHandlerPool,
                'companyResource' => $this->companyResource,
                'companyFactory' => $this->companyFactory,
                'userCollectionFactory' => $this->userCollectionFactory,
                'saveValidatorPool' => $this->saveValidatorPool,
            ]
        );
    }

    /**
     * Test save method.
     *
     * @return void
     */
    public function testSave()
    {
        $companyId = 1;
        $regionId = 5;
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialCompany = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userCollection = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company->expects($this->atLeastOnce())->method('getRegionId')->willReturn($regionId);
        $company->expects($this->atLeastOnce())->method('setRegion')->with(null)->willReturnSelf();
        $company->expects($this->atLeastOnce())
            ->method('getSalesRepresentativeId')
            ->willReturn(null);
        $this->userCollectionFactory->expects($this->once())->method('create')->willReturn($userCollection);
        $userCollection->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $userCollection->expects($this->once())->method('getFirstItem')->willReturn($user);
        $user->expects($this->once())->method('getId')->willReturn(1);
        $company->expects($this->atLeastOnce())->method('setSalesRepresentativeId')->with(1)->willReturnSelf();
        $company->expects($this->atLeastOnce())->method('getId')->willReturn($companyId);
        $this->companyFactory->expects($this->once())->method('create')->willReturn($initialCompany);
        $this->companyResource->expects($this->once())
            ->method('load')
            ->with($initialCompany, $companyId)
            ->willReturn($initialCompany);
        $this->saveValidatorPool->expects($this->once())->method('execute')->with($company, $initialCompany);
        $this->companyResource->expects($this->once())->method('save')->with($company)->willReturnSelf();
        $this->saveHandlerPool->expects($this->once())->method('execute')->with($company, $initialCompany);

        $this->assertSame($company, $this->model->save($company));
    }

    /**
     * Test save method with no region id.
     *
     * @return void
     */
    public function testSaveNoRegionId()
    {
        $companyId = 1;
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialCompany = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userCollection = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company->expects($this->atLeastOnce())->method('getRegionId')->willReturn(null);
        $company->expects($this->atLeastOnce())->method('setRegionId')->with(null)->willReturnSelf();
        $company->expects($this->atLeastOnce())
            ->method('getSalesRepresentativeId')
            ->willReturn(null);
        $this->userCollectionFactory->expects($this->once())->method('create')->willReturn($userCollection);
        $userCollection->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $userCollection->expects($this->once())->method('getFirstItem')->willReturn($user);
        $user->expects($this->once())->method('getId')->willReturn(1);
        $company->expects($this->atLeastOnce())->method('setSalesRepresentativeId')->with(1)->willReturnSelf();
        $company->expects($this->atLeastOnce())->method('getId')->willReturn($companyId);
        $this->companyFactory->expects($this->once())->method('create')->willReturn($initialCompany);
        $this->companyResource->expects($this->once())
            ->method('load')
            ->with($initialCompany, $companyId)
            ->willReturn($initialCompany);
        $this->saveValidatorPool->expects($this->once())->method('execute')->with($company, $initialCompany);
        $this->companyResource->expects($this->once())->method('save')->with($company)->willReturnSelf();
        $this->saveHandlerPool->expects($this->once())->method('execute')->with($company, $initialCompany);

        $this->assertSame($company, $this->model->save($company));
    }

    /**
     * Test save method when CouldNotSaveException is thrown.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save company
     */
    public function testSaveCompanyWithCouldNotSaveException()
    {
        $companyId = 1;
        $regionId = 5;
        $exception = new \Exception();
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialCompany = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userCollection = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $company->expects($this->atLeastOnce())->method('getRegionId')->willReturn($regionId);
        $company->expects($this->atLeastOnce())->method('setRegion')->with(null)->willReturnSelf();
        $company->expects($this->atLeastOnce())
            ->method('getSalesRepresentativeId')
            ->willReturn(null);
        $this->userCollectionFactory->expects($this->once())->method('create')->willReturn($userCollection);
        $userCollection->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $userCollection->expects($this->once())->method('getFirstItem')->willReturn($user);
        $user->expects($this->once())->method('getId')->willReturn(1);
        $company->expects($this->atLeastOnce())->method('setSalesRepresentativeId')->with(1)->willReturnSelf();
        $company->expects($this->atLeastOnce())->method('getId')->willReturn($companyId);
        $this->companyFactory->expects($this->once())->method('create')->willReturn($initialCompany);
        $this->companyResource->expects($this->once())
            ->method('load')
            ->with($initialCompany, $companyId)
            ->willReturn($initialCompany);
        $this->saveValidatorPool->expects($this->once())->method('execute')->with($company, $initialCompany);
        $this->companyResource->expects($this->once())->method('save')->with($company)->willThrowException($exception);

        $this->model->save($company);
    }
}
