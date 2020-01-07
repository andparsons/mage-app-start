<?php

namespace Magento\Company\Test\Unit\Observer;

/**
 * Class CustomerAccountEditedTest.
 */
class CustomerAccountEditedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResourceMock;

    /**
     * @var \Magento\Company\Observer\CustomerAccountEdited
     */
    private $customerAccountEdited;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->customerRepositoryMock = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->customerResourceMock = $this->createPartialMock(
            \Magento\Company\Model\ResourceModel\Customer::class,
            ['saveAdvancedCustomAttributes']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerAccountEdited = $objectManager->getObject(
            \Magento\Company\Observer\CustomerAccountEdited::class,
            [
                'customerRepository' => $this->customerRepositoryMock,
                'request' => $this->requestMock,
                'customerResource' => $this->customerResourceMock,
            ]
        );
    }

    /**
     * Test method for execute
     */
    public function testExecute()
    {
        $email = 'email@sample.com';
        $customerData = ['extension_attributes' => ['company_attributes' => ['job_title' => 'Manager']]];
        /**
         * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $observer
         */
        $observer = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEmail']);
        $observer->expects($this->once())->method('getEmail')->willReturn($email);
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->once())->method('get')->willReturn($customer);
        $this->requestMock->expects($this->once())->method('getParam')->willReturn($customerData);

        $customerExtensionAttributes = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );
        $customerAttributes = $this->createMock(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        );
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtensionAttributes);
        $customerAttributes->expects($this->atLeastOnce())->method('setCustomerId')->willReturnSelf();
        $customerExtensionAttributes->expects($this->atLeastOnce())->method('getCompanyAttributes')
            ->willReturn($customerAttributes);
        $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
        $companyAttributes->setCustomerId($customer->getId())
            ->setJobTitle($customerData['extension_attributes']['company_attributes']['job_title']);
        $this->customerResourceMock->expects($this->once())
            ->method('saveAdvancedCustomAttributes')
            ->with($customerAttributes)
            ->willReturnSelf();
        $this->customerAccountEdited->execute($observer);
    }
}
