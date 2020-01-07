<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing\Column\Store;

/**
 * Class OptionsTest
 */
class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\Column\Store\Options|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionsMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $groupName = 'test name';
        $id = 123; //test id
        $group = $this->createPartialMock(
            \Magento\Store\Model\Group::class,
            ['getId', 'getName']
        );
        $group->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($id);
        $group->expects($this->exactly(3))
            ->method('getName')
            ->willReturn($groupName);
        $groups = [$group];
        $website = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\WebsiteInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getGroups']
        );
        $website->expects($this->once())
            ->method('getGroups')
            ->willReturn($groups);
        $websites = [$website];
        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getWebsites']
        );
        $this->storeManager->expects($this->exactly(2))
            ->method('getWebsites')
            ->willReturn($websites);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->optionsMock = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Column\Store\Options::class,
            [
                'storeManager' => $this->storeManager,
            ]
        );
    }

    public function testToOptionArray()
    {
        $this->optionsMock->toOptionArray();
    }
}
