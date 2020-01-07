<?php
namespace Magento\CompanyCredit\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Unit tests for Creator model.
 */
class CreatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\CompanyCredit\Model\Creator
     */
    private $creator;

    /**
     * @var \Magento\User\Model\ResourceModel\User|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userResourceMock;

    /**
     * @var \Magento\User\Api\Data\UserInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userFactoryMock;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $integrationMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerNameGenerationMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $providerMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->userResourceMock = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userFactoryMock = $this->getMockBuilder(\Magento\User\Api\Data\UserInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->integrationMock = $this->getMockBuilder(\Magento\Integration\Api\IntegrationServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerNameGenerationMock = $this->getMockBuilder(CustomerNameGenerationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->providerMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Provider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->creator = $this->objectManagerHelper->getObject(
            \Magento\CompanyCredit\Model\Creator::class,
            [
                'userResource' => $this->userResourceMock,
                'userFactory' => $this->userFactoryMock,
                'integration' => $this->integrationMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerNameGeneration' => $this->customerNameGenerationMock,
                'provider' => $this->providerMock
            ]
        );
    }

    /**
     * Test for retrieveCreatorName() method if user type is admin.
     *
     * @return void
     */
    public function testRetrieveCreatorNameIfUserTypeAdmin()
    {
        $userId = 1;
        $userFirstName = 'John';
        $userLastName = 'Doe';

        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userFactoryMock->expects($this->once())->method('create')->willReturn($userMock);
        $this->userResourceMock->expects($this->once())->method('load')->with($userMock, $userId);
        $userMock->expects($this->once())->method('getFirstName')->willReturn($userFirstName);
        $userMock->expects($this->once())->method('getLastName')->willReturn($userLastName);
        $result = $userFirstName . ' ' . $userLastName;

        $this->assertEquals(
            $result,
            $this->creator->retrieveCreatorName(UserContextInterface::USER_TYPE_ADMIN, $userId)
        );
    }

    /**
     * Test for retrieveCreatorName() method if user type is integration.
     *
     * @return void
     */
    public function testRetrieveCreatorNameIfUserTypeIntegration()
    {
        $userId = 1;
        $userName = 'John Doe';

        $integrationMock = $this->getMockBuilder(\Magento\Integration\Model\Integration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();
        $this->integrationMock->expects($this->once())->method('get')->with($userId)
            ->willReturn($integrationMock);
        $integrationMock->expects($this->once())->method('getName')->willReturn($userName);

        $this->assertEquals(
            $userName,
            $this->creator->retrieveCreatorName(UserContextInterface::USER_TYPE_INTEGRATION, $userId)
        );
    }

    /**
     * Test for retrieveCreatorName() method if user type is customer.
     *
     * @return void
     */
    public function testRetrieveCreatorNameIfUserTypeCustomer()
    {
        $userId = 1;
        $userName = 'John Doe';

        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock->expects($this->once())->method('getById')->with($userId)
            ->willReturn($customerMock);
        $this->customerNameGenerationMock->expects($this->once())->method('getCustomerName')->willReturn($userName);

        $this->assertEquals(
            $userName,
            $this->creator->retrieveCreatorName(UserContextInterface::USER_TYPE_CUSTOMER, $userId)
        );
    }

    /**
     * Test for retrieveCreatorName() method if user type is not expected.
     *
     * @return void
     */
    public function testRetrieveCreatorName()
    {
        $userId = 1;

        $this->assertEquals('', $this->creator->retrieveCreatorName(UserContextInterface::USER_TYPE_GUEST, $userId));
    }
}
