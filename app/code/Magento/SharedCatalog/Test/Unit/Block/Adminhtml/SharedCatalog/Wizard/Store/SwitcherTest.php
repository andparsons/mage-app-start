<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Wizard\Store;

use Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Store\Switcher;

/**
 * Test for Block Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Store\Switcher.
 */
class SwitcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\System\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $systemStore;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonEncoder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Store\Api\Data\GroupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeGroup;

    /**
     * @var Switcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $switcherMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getParam']
        );
        $this->sharedCatalogRepository = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['get']
        );
        $this->sharedCatalog = $this->getMockForAbstractClass(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getStoreId']
        );
        $this->systemStore = $this->createPartialMock(\Magento\Store\Model\System\Store::class, ['getGroupCollection']);
        $this->storeGroup = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\GroupInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getName']
        );
        $this->jsonEncoder = $this->createMock(\Magento\Framework\Json\EncoderInterface::class);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->switcherMock = $this->objectManager->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Store\Switcher::class,
            [
                'systemStore' => $this->systemStore,
                'jsonEncoder' => $this->jsonEncoder,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'data' => [],
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test for isOptionSelected().
     *
     * @return void
     */
    public function testIsOptionSelected()
    {
        $id = 3654;
        $sharedCatalogParam = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $this->request->expects($this->exactly(1))->method('getParam')->with($sharedCatalogParam)->willReturn($id);

        $storeId = 34;
        $this->sharedCatalog->expects($this->exactly(1))->method('getStoreId')->willReturn($storeId);

        $this->sharedCatalogRepository->expects($this->exactly(1))->method('get')->with($id)
            ->willReturn($this->sharedCatalog);

        $expects = true;
        $this->assertEquals($expects, $this->switcherMock->isOptionSelected());
    }

    /**
     * Test for getSelectedOptionLabel().
     *
     * @param int $storeGroupId
     * @param string|null $expectedResult
     * @dataProvider getSelectedOptionLabelDataProvider
     * @return void
     */
    public function testGetSelectedOptionLabel($storeGroupId, $expectedResult)
    {
        $id = 3654;
        $sharedCatalogParam = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $this->request->expects($this->exactly(1))->method('getParam')->with($sharedCatalogParam)->willReturn($id);

        $storeId = 34;
        $this->sharedCatalog->expects($this->exactly(1))->method('getStoreId')->willReturn($storeId);

        $this->sharedCatalogRepository->expects($this->exactly(1))->method('get')->with($id)
            ->willReturn($this->sharedCatalog);

        $storeGroupName = 'All Stores';
        $this->storeGroup->expects($this->exactly(1))->method('getName')->willReturn($storeGroupName);
        $this->storeGroup->expects($this->exactly(1))->method('getId')->willReturn($storeGroupId);

        $storeGroups = [$this->storeGroup];
        $this->systemStore->expects($this->exactly(1))->method('getGroupCollection')->willReturn($storeGroups);

        $actualResult = $this->switcherMock->getSelectedOptionLabel();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for getSelectedOptionLabel() test.
     *
     * @return array
     */
    public function getSelectedOptionLabelDataProvider()
    {
        return [
            [45, null],
            [34, 'All Stores']
        ];
    }
}
