<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

/**
 * Unit test for customer group validator.
 */
class CustomerGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupRepository;

    /**
     * @var \Magento\Company\Model\SaveValidator\CustomerGroup
     */
    private $customerGroup;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerGroupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerGroup = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\CustomerGroup::class,
            [
                'company' => $this->company,
                'customerGroupRepository' => $this->customerGroupRepository,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $customerGroupId = 1;
        $this->company->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $customerGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerGroupRepository->expects($this->once())
            ->method('getById')->with($customerGroupId)->willReturn($customerGroup);
        $this->customerGroup->execute();
    }

    /**
     * Test for execute method with non-existing customer group.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerGroupId = 1
     */
    public function testExecuteWithNonExistingCustomerGroup()
    {
        $customerGroupId = 1;
        $this->company->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->customerGroupRepository->expects($this->once())->method('getById')->with($customerGroupId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->customerGroup->execute();
    }
}
