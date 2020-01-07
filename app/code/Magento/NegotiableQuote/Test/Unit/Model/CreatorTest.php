<?php
namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Customer\Api\CustomerNameGenerationInterface;

/**
 * Unit test for Creator.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\User\Model\ResourceModel\User|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userResource;

    /**
     * @var \Magento\User\Api\Data\UserInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userFactory;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integration;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerNameGeneration;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var \Magento\NegotiableQuote\Model\Creator
     */
    private $creator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->userResource = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userFactory = $this->getMockBuilder(\Magento\User\Api\Data\UserInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->integration = $this->getMockBuilder(\Magento\Integration\Api\IntegrationServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerNameGeneration = $this->getMockBuilder(CustomerNameGenerationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->provider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Provider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->creator = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Creator::class,
            [
                'userResource' => $this->userResource,
                'userFactory' => $this->userFactory,
                'integration' => $this->integration,
                'customerRepository' => $this->customerRepository,
                'customerNameGeneration' => $this->customerNameGeneration,
                'provider' => $this->provider
            ]
        );
    }

    /**
     * Test for retrieveCreatorName() for admin user type.
     *
     * @return void
     */
    public function testRetrieveCreatorNameUserTypeAdmin()
    {
        $type = \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN;
        $id = 1;
        $firstName = 'First';
        $lastName = 'Last';
        $user = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->atLeastOnce())->method('getFirstName')->willReturn($firstName);
        $user->expects($this->atLeastOnce())->method('getLastName')->willReturn($lastName);
        $this->userFactory->expects($this->atLeastOnce())->method('create')->willReturn($user);
        $this->userResource->expects($this->atLeastOnce())->method('load')->with($user, $id)->willReturn($user);
        $name = $firstName . ' ' . $lastName;

        $this->assertEquals($name, $this->creator->retrieveCreatorName($type, $id));
    }

    /**
     * Test for retrieveCreatorName() for admin user type with NoSuchEntityException.
     *
     * @return void
     */
    public function testRetrieveCreatorNameUserTypeAdminWithNoSuchEntityException()
    {
        $type = \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN;
        $id = 1;
        $quoteId = 1;
        $name = 'Peter Parker';
        $this->userFactory->expects($this->atLeastOnce())->method('create')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->provider->expects($this->once())->method('getSalesRepresentativeName')->with($quoteId)
            ->willReturn($name);

        $this->assertEquals($name, $this->creator->retrieveCreatorName($type, $id, $quoteId));
    }

    /**
     * Test for retrieveCreatorName() for integration user type.
     *
     * @return void
     */
    public function testRetrieveCreatorNameUserTypeIntegration()
    {
        $type = \Magento\Authorization\Model\UserContextInterface::USER_TYPE_INTEGRATION;
        $id = 1;
        $name = 'Name';
        $integration = $this->getMockBuilder(\Magento\Integration\Model\Integration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();
        $integration->expects($this->atLeastOnce())->method('getName')->willReturn($name);
        $this->integration->expects($this->atLeastOnce())->method('get')->with($id)->willReturn($integration);

        $this->assertEquals($name, $this->creator->retrieveCreatorName($type, $id));
    }

    /**
     * Test for retrieveCreatorName() for customer user type.
     *
     * @return void
     */
    public function testRetrieveCreatorNameUserTypeCustomer()
    {
        $type = \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER;
        $id = 1;
        $name = 'Name';
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepository->expects($this->once())->method('getById')->with($id)->willReturn($customerMock);
        $this->customerNameGeneration->expects($this->once())->method('getCustomerName')->with($customerMock)
            ->willReturn($name);

        $this->assertEquals($name, $this->creator->retrieveCreatorName($type, $id));
    }

    /**
     * Test for retrieveCreatorName() for customer user type with NoSuchEntityException.
     *
     * @return void
     */
    public function testRetrieveCreatorNameUserTypeCustomerWithNoSuchEntityException()
    {
        $type = \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER;
        $id = 1;
        $quoteId = 1;
        $name = 'Name';
        $this->customerRepository->expects($this->once())->method('getById')->with($id)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->provider->expects($this->once())->method('getCustomerName')->with($quoteId)
            ->willReturn($name);

        $this->assertEquals($name, $this->creator->retrieveCreatorName($type, $id, $quoteId));
    }

    /**
     * Test for retrieveCreatorName() for customer user type is not supported.
     *
     * @return void
     */
    public function testRetrieveCreatorNameUserTypeNotSupported()
    {
        $type = 'dummy type';
        $id = 1;
        $name = '';

        $this->assertEquals($name, $this->creator->retrieveCreatorName($type, $id));
    }
}
