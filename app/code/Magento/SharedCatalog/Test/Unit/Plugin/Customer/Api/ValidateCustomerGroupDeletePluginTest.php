<?php
namespace Magento\SharedCatalog\Test\Unit\Plugin\Customer\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for ValidateCustomerGroupDeletePlugin.
 */
class ValidateCustomerGroupDeletePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogLocator;

    /**
     * @var \Magento\SharedCatalog\Plugin\Customer\Api\ValidateCustomerGroupDeletePlugin
     */
    private $validateCustomerGroupDeletePlugin;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->sharedCatalogLocator = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->validateCustomerGroupDeletePlugin = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Plugin\Customer\Api\ValidateCustomerGroupDeletePlugin::class,
            [
                'sharedCatalogLocator' => $this->sharedCatalogLocator,
            ]
        );
    }

    /**
     * Test for beforeDeleteById().
     *
     * @return void
     */
    public function testBeforeDeleteById()
    {
        $customerGroupId = 1;
        $this->sharedCatalogLocator->expects($this->once())
            ->method('getSharedCatalogByCustomerGroup')
            ->with($customerGroupId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Exception Message')));
        $groupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->assertEquals(
            [$customerGroupId],
            $this->validateCustomerGroupDeletePlugin->beforeDeleteById($groupRepository, $customerGroupId)
        );
    }

    /**
     * Test for beforeDeleteById() with CouldNotDeleteException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage A shared catalog is linked to this customer group.
     */
    public function testBeforeDeleteByIdWithException()
    {
        $customerGroupId = 1;
        $sharedCatalog = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogLocator->expects($this->once())
            ->method('getSharedCatalogByCustomerGroup')->with($customerGroupId)->willReturn($sharedCatalog);
        $groupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->validateCustomerGroupDeletePlugin->beforeDeleteById($groupRepository, $customerGroupId);
    }
}
