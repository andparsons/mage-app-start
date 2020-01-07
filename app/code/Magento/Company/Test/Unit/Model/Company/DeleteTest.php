<?php

namespace Magento\Company\Test\Unit\Model\Company;

/**
 * Unit test for Magento\Company\Model\Company\Delete class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyResource;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResource;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    /**
     * @var \Magento\Company\Api\TeamRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $teamRepository;

    /**
     * @var \Magento\Company\Model\StructureRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureRepository;

    /**
     * @var \Magento\Company\Model\Company\Delete
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
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->structureManager = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->teamRepository = $this->getMockBuilder(\Magento\Company\Api\TeamRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureRepository = $this->getMockBuilder(\Magento\Company\Model\StructureRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\Company\Delete::class,
            [
                'companyResource' => $this->companyResource,
                'customerRepository' => $this->customerRepository,
                'customerResource' => $this->customerResource,
                'structureManager' => $this->structureManager,
                'teamRepository' => $this->teamRepository,
                'structureRepository' => $this->structureRepository,
            ]
        );
    }

    /**
     * Test delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $superUserId = 1;
        $allowedIds = ['users' => [2]];
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->atLeastOnce())->method('getSuperUserId')->willReturn($superUserId);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')
            ->with($superUserId)
            ->willReturn($allowedIds);
        $team = $this->getMockBuilder(\Magento\Company\Api\Data\StructureInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureManager->expects($this->once())
            ->method('getUserChildTeams')
            ->willReturn([$team]);

        $this->structureManager->expects($this->once())
            ->method('removeCustomerNode')
            ->with(2);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willReturn($customer);
        $customer->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())
            ->method('setCompanyId')
            ->with(0)
            ->willReturnSelf();
        $companyAttributes->expects($this->once())
            ->method('setStatus')
            ->with(\Magento\Company\Api\Data\CompanyCustomerInterface::STATUS_INACTIVE)
            ->willReturnSelf();

        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($customer)
            ->willReturn($customer);
        $this->companyResource->expects($this->once())
            ->method('delete')
            ->with($company)
            ->willReturnSelf();

        $this->model->delete($company);
    }
}
