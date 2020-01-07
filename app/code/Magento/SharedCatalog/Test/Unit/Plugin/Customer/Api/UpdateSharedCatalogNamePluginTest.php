<?php
namespace Magento\SharedCatalog\Test\Unit\Plugin\Customer\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for UpdateSharedCatalogNamePlugin.
 */
class UpdateSharedCatalogNamePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogLocator;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Plugin\Customer\Api\UpdateSharedCatalogNamePlugin
     */
    private $updateSharedCatalogNamePlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogLocator = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->updateSharedCatalogNamePlugin = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Plugin\Customer\Api\UpdateSharedCatalogNamePlugin::class,
            [
                'sharedCatalogLocator' => $this->sharedCatalogLocator,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
            ]
        );
    }

    /**
     * Test for afterSave().
     *
     * @return void
     */
    public function testAfterSave()
    {
        $customerGroupId = 1;
        $customerGroupCode = 'Customer Group';
        $customerGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn($customerGroupId);
        $customerGroup->expects($this->atLeastOnce())->method('getCode')->willReturn($customerGroupCode);
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->atLeastOnce())->method('getName')->willReturn('Shared Catalog');
        $sharedCatalog->expects($this->atLeastOnce())->method('setName')->with($customerGroupCode)->willReturnSelf();
        $this->sharedCatalogLocator->expects($this->atLeastOnce())->method('getSharedCatalogByCustomerGroup')
            ->with($customerGroupId)->willReturn($sharedCatalog);
        $this->sharedCatalogRepository->expects($this->atLeastOnce())->method('save')->with($sharedCatalog)
            ->willReturn($sharedCatalog);
        $groupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertInstanceOf(
            \Magento\Customer\Api\Data\GroupInterface::class,
            $this->updateSharedCatalogNamePlugin->afterSave($groupRepository, $customerGroup)
        );
    }

    /**
     * Test for afterSave() with NoSuchEntityException.
     *
     * @return void
     */
    public function testAfterSaveWithNoSuchEntityException()
    {
        $customerGroupId = 1;
        $customerGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn($customerGroupId);
        $phrase = new \Magento\Framework\Phrase('exception');
        $exception = new \Magento\Framework\Exception\NoSuchEntityException($phrase);
        $this->sharedCatalogLocator->expects($this->atLeastOnce())->method('getSharedCatalogByCustomerGroup')
            ->with($customerGroupId)->willThrowException($exception);
        $groupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->updateSharedCatalogNamePlugin->afterSave($groupRepository, $customerGroup);
    }
}
