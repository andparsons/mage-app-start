<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Wizard;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for Block SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Wizard\State.
 */
class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $state;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getStoreId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->state = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\State::class,
            [
                'context' => $this->contextMock,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test for isCatalogConfigured().
     *
     * @return void
     */
    public function testIsCatalogConfigured()
    {
        $storeId = 3;
        $this->sharedCatalog->expects($this->exactly(1))->method('getStoreId')->willReturn($storeId);

        $sharedCatalogParam = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $catalogId = 4676;
        $this->request->expects($this->exactly(1))->method('getParam')->with($sharedCatalogParam)
            ->willReturn($catalogId);

        $this->sharedCatalogRepository->expects($this->exactly(1))->method('get')->with($catalogId)
            ->willReturn($this->sharedCatalog);

        $expects = true;
        $this->assertEquals($expects, $this->state->isCatalogConfigured());
    }
}
