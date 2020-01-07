<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing\Column\Configure;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Unit tests for websites grid column.
 */
class WebsitesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\Websites
     */
    private $websites;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepositoryMock;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Columns\Websites|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogWebsitesColumnMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiComponentFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogRepositoryMock = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogWebsitesColumnMock = $this->getMockBuilder(
            \Magento\Catalog\Ui\Component\Listing\Columns\Websites::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->websites = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Column\Configure\Websites::class,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'sharedCatalogRepository' => $this->sharedCatalogRepositoryMock,
                'catalogWebsitesColumn' => $this->catalogWebsitesColumnMock
            ]
        );
    }

    /**
     * Test for prepareDataSource() method.
     *
     * @return void
     */
    public function testPrepareDataSource()
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        $dataSource = [
            'items' => [
                'website1'
            ],
        ];
        $this->catalogWebsitesColumnMock->expects($this->once())->method('setData');
        $this->catalogWebsitesColumnMock->expects($this->once())->method('prepareDataSource')->willReturn($dataSource);

        $this->assertEquals($dataSource, $this->websites->prepareDataSource($dataSource));
    }

    /**
     * Test for prepare() method when there single store mode is false in store manager.
     *
     * @param array $result
     * @param int|null $sharedCatalogStoreId
     * @dataProvider prepareForMultipleStoresDataProvider
     * @return void
     */
    public function testPrepareForMultipleStores(array $result, $sharedCatalogStoreId)
    {
        $sharedCatalogRequestId = 1;
        $this->storeManagerMock->expects($this->once())->method('isSingleStoreMode')->willReturn(false);
        $processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        $this->contextMock->expects($this->any())->method('getRequestParam')
            ->withConsecutive(
                ['sorting'],
                [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM]
            )
            ->willReturnOnConsecutiveCalls(null, $sharedCatalogRequestId);
        $sharedCatalogMock = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogRepositoryMock->expects($this->any())->method('get')->with($sharedCatalogRequestId)
            ->willReturn($sharedCatalogMock);
        $sharedCatalogMock->expects($this->any())->method('getStoreId')->willReturn($sharedCatalogStoreId);

        $this->websites->prepare();

        $configData = $this->websites->getData('config');
        $this->assertEquals($result, $configData);
    }

    /**
     * Test for prepare() method when there single store mode is true in store manager.
     *
     * @return void
     */
    public function testPrepareForSingleStore()
    {
        $processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        $this->storeManagerMock->expects($this->once())->method('isSingleStoreMode')
            ->willReturn(true);
        $this->websites->prepare();
        $configData = $this->websites->getData('config');
        $this->assertEquals(true, $configData['componentDisabled']);
    }

    /**
     * Data provider for testPrepareForMultipleStores() test.
     *
     * @return array
     */
    public function prepareForMultipleStoresDataProvider()
    {
        return [
            [[], null],
            [['componentDisabled' => true], 1]
        ];
    }
}
