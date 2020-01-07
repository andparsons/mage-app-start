<?php

namespace Magento\SharedCatalog\Test\Unit\Model\SaveHandler\SharedCatalog;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\ResourceModel\SharedCatalog;
use Magento\SharedCatalog\Model\CustomerGroupManagement;

/**
 * Unit tests for SharedCatalog/Model/SaveHandler/SharedCatalog/Save.php.
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog\Save
     */
    private $save;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogResourceMock;

    /**
     * @var CustomerGroupManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupManagementMock;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContextMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->sharedCatalogResourceMock = $this->getMockBuilder(SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerGroupManagementMock = $this->getMockBuilder(CustomerGroupManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContextMock = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->save = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog\Save::class,
            [
                'sharedCatalogResource' => $this->sharedCatalogResourceMock,
                'customerGroupManagement' => $this->customerGroupManagementMock,
                'userContext' => $this->userContextMock
            ]
        );
    }

    /**
     * Test for execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogResourceMock->expects($this->once())->method('save')->with($sharedCatalog);

        $this->save->execute($sharedCatalog);
    }

    /**
     * Test for prepare() method if user type is Admin.
     *
     * @return void
     */
    public function testPrepareIfUserTypeAdmin()
    {
        $userId = 1;

        $sharedCatalog = $this->prepareSharedCatalogMockForPrepareTest();
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN);
        $this->userContextMock->expects($this->once())->method('getUserId')->willReturn($userId);
        $sharedCatalog->expects($this->once())->method('setCreatedBy')->with($userId)->willReturnSelf();

        $this->save->prepare($sharedCatalog);
    }

    /**
     * Test for prepare() method if user type is not Admin.
     *
     * @return void
     */
    public function testPrepareIfUserTypeNotAdmin()
    {
        $userId = null;

        $sharedCatalog = $this->prepareSharedCatalogMockForPrepareTest();
        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->never())->method('getUserId');
        $sharedCatalog->expects($this->once())->method('setCreatedBy')->with($userId)->willReturnSelf();

        $this->save->prepare($sharedCatalog);
    }

    /**
     * Prepare shared catalog mock for prepare() tests.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareSharedCatalogMockForPrepareTest()
    {
        $customerGroupId = 1;

        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sharedCatalog->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn(null);
        $customerGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroup->expects($this->once())->method('getId')->willReturn($customerGroupId);
        $this->customerGroupManagementMock->expects($this->once())->method('createCustomerGroupForSharedCatalog')
            ->with($sharedCatalog)->willReturn($customerGroup);
        $sharedCatalog->expects($this->once())->method('setCustomerGroupId')->with($customerGroupId)->willReturnSelf();
        $sharedCatalog->expects($this->atLeastOnce())->method('getType')
            ->willReturn(null);
        $sharedCatalog->expects($this->atLeastOnce())->method('setType')
            ->with(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM)
            ->willReturnSelf();

        return $sharedCatalog;
    }
}
