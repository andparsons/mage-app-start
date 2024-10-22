<?php

/**
 * Test class for \Magento\Backend\Block\Widget\Grid\Massaction
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

use Magento\Backend\Block\Widget\Grid\Massaction\VisibilityCheckerInterface as VisibilityChecker;
use Magento\Framework\Authorization;

class MassactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Massaction
     */
    protected $_block;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \Magento\Backend\Block\Widget\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_gridMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \Magento\Backend\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var Authorization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authorizationMock;

    /**
     * @var VisibilityChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $visibilityCheckerMock;

    protected function setUp()
    {
        $this->_gridMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['getId', 'getCollection'])
            ->getMock();
        $this->_gridMock->expects($this->any())
            ->method('getId')
            ->willReturn('test_grid');

        $this->_layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['getParentName', 'getBlock', 'helper'])
            ->getMock();
        $this->_layoutMock->expects($this->any())
            ->method('getParentName')
            ->with('test_grid_massaction')
            ->willReturn('test_grid');
        $this->_layoutMock->expects($this->any())
            ->method('getBlock')
            ->with('test_grid')
            ->willReturn($this->_gridMock);

        $this->_requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->_urlModelMock = $this->getMockBuilder(\Magento\Backend\Model\Url::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $this->visibilityCheckerMock = $this->getMockBuilder(VisibilityChecker::class)
            ->getMockForAbstractClass();

        $this->_authorizationMock = $this->getMockBuilder(Authorization::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMock();

        $arguments = [
            'layout' => $this->_layoutMock,
            'request' => $this->_requestMock,
            'urlBuilder' => $this->_urlModelMock,
            'data' => ['massaction_id_field' => 'test_id', 'massaction_id_filter' => 'test_id'],
            'authorization' => $this->_authorizationMock,
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_block = $objectManagerHelper->getObject(
            \Magento\Backend\Block\Widget\Grid\Massaction::class,
            $arguments
        );
        $this->_block->setNameInLayout('test_grid_massaction');
    }

    protected function tearDown()
    {
        unset($this->_layoutMock);
        unset($this->_eventManagerMock);
        unset($this->_gridMock);
        unset($this->_urlModelMock);
        unset($this->_block);
    }

    public function testMassactionDefaultValues()
    {
        $this->assertEquals(0, $this->_block->getCount());
        $this->assertFalse($this->_block->isAvailable());

        $this->assertEquals('massaction', $this->_block->getFormFieldName());
        $this->assertEquals('internal_massaction', $this->_block->getFormFieldNameInternal());

        $this->assertEquals('test_grid_massactionJsObject', $this->_block->getJsObjectName());
        $this->assertEquals('test_gridJsObject', $this->_block->getGridJsObjectName());

        $this->assertEquals('test_grid_massaction', $this->_block->getHtmlId());
        $this->assertTrue($this->_block->getUseSelectAll());
    }

    /**
     * @param string $itemId
     * @param \Magento\Framework\DataObject $item
     * @param $expectedItem \Magento\Framework\DataObject
     * @dataProvider itemsProcessingDataProvider
     */
    public function testItemsProcessing($itemId, $item, $expectedItem)
    {
        $this->_urlModelMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('http://localhost/index.php');

        $urlReturnValueMap = [
            ['*/*/test1', [], 'http://localhost/index.php/backend/admin/test/test1'],
            ['*/*/test2', [], 'http://localhost/index.php/backend/admin/test/test2'],
        ];
        $this->_urlModelMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap($urlReturnValueMap);

        $this->_authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn(true);

        $this->_block->addItem($itemId, $item);
        $this->assertEquals(1, $this->_block->getCount());

        $actualItem = $this->_block->getItem($itemId);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $actualItem);
        $this->assertEquals($expectedItem->getData(), $actualItem->getData());

        $this->_block->removeItem($itemId);
        $this->assertEquals(0, $this->_block->getCount());
        $this->assertNull($this->_block->getItem($itemId));
    }

    /**
     * @return array
     */
    public function itemsProcessingDataProvider()
    {
        return [
            [
                'test_id1',
                ["label" => "Test Item One", "url" => "*/*/test1"],
                new \Magento\Framework\DataObject(
                    [
                        "label" => "Test Item One",
                        "url" => "http://localhost/index.php/backend/admin/test/test1",
                        "id" => 'test_id1',
                    ]
                ),
            ],
            [
                'test_id2',
                new \Magento\Framework\DataObject(["label" => "Test Item Two", "url" => "*/*/test2"]),
                new \Magento\Framework\DataObject(
                    [
                        "label" => "Test Item Two",
                        "url" => "http://localhost/index.php/backend/admin/test/test2",
                        "id" => 'test_id2',
                    ]
                )
            ],
            [
                'enabled',
                new \Magento\Framework\DataObject(["label" => "Test Item Enabled", "url" => "*/*/test2"]),
                new \Magento\Framework\DataObject(
                    [
                        "label" => "Test Item Enabled",
                        "url" => "http://localhost/index.php/backend/admin/test/test2",
                        "id" => 'enabled',
                    ]
                )
            ],
            [
                'refresh',
                new \Magento\Framework\DataObject(["label" => "Test Item Refresh", "url" => "*/*/test2"]),
                new \Magento\Framework\DataObject(
                    [
                        "label" => "Test Item Refresh",
                        "url" => "http://localhost/index.php/backend/admin/test/test2",
                        "id" => 'refresh',
                    ]
                )
            ]
        ];
    }

    /**
     * @param string $param
     * @param string $expectedJson
     * @param array $expected
     * @dataProvider selectedDataProvider
     */
    public function testSelected($param, $expectedJson, $expected)
    {
        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->_block->getFormFieldNameInternal())
            ->willReturn($param);

        $this->assertEquals($expectedJson, $this->_block->getSelectedJson());
        $this->assertEquals($expected, $this->_block->getSelected());
    }

    /**
     * @return array
     */
    public function selectedDataProvider()
    {
        return [
            ['', '', []],
            ['test_id1,test_id2', 'test_id1,test_id2', ['test_id1', 'test_id2']]
        ];
    }

    public function testUseSelectAll()
    {
        $this->_block->setUseSelectAll(false);
        $this->assertFalse($this->_block->getUseSelectAll());

        $this->_block->setUseSelectAll(true);
        $this->assertTrue($this->_block->getUseSelectAll());
    }

    public function testGetGridIdsJsonWithoutUseSelectAll()
    {
        $this->_block->setUseSelectAll(false);
        $this->assertEmpty($this->_block->getGridIdsJson());
    }

    /**
     * @param string $itemId
     * @param array|\Magento\Framework\DataObject $item
     * @param int $count
     * @param bool $withVisibilityChecker
     * @param bool $isVisible
     * @param bool $isAllowed
     *
     * @dataProvider addItemDataProvider
     */
    public function testAddItem($itemId, $item, $count, $withVisibilityChecker, $isVisible, $isAllowed)
    {
        $this->visibilityCheckerMock->expects($this->any())
            ->method('isVisible')
            ->willReturn($isVisible);

        $this->_authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn($isAllowed);

        if ($withVisibilityChecker) {
            $item['visible'] = $this->visibilityCheckerMock;
        }

        $urlReturnValueMap = [
            ['*/*/test1', [], 'http://localhost/index.php/backend/admin/test/test1'],
            ['*/*/test2', [], 'http://localhost/index.php/backend/admin/test/test2'],
        ];
        $this->_urlModelMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap($urlReturnValueMap);

        $this->_block->addItem($itemId, $item);
        $this->assertEquals($count, $this->_block->getCount(), $itemId);
    }

    /**
     * @return array
     */
    public function addItemDataProvider()
    {
        return [
            [
                'itemId' => 'test1',
                'item' => ['label' => 'Test 1', 'url' => '*/*/test1'],
                'count' => 1,
                'withVisibilityChecker' => false,
                'isVisible' => false,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'test2',
                'item' => ['label' => 'Test 2', 'url' => '*/*/test2'],
                'count' => 1,
                'withVisibilityChecker' => false,
                'isVisible' => true,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'test3',
                'item' => ['label' => 'Test 3. Hide', 'url' => '*/*/test3'],
                'count' => 0,
                'withVisibilityChecker' => true,
                'isVisible' => false,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'test4',
                'item' => ['label' => 'Test 4. Does not hide', 'url' => '*/*/test4'],
                'count' => 1,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'enable',
                'item' => ['label' => 'Test 5. Not restricted', 'url' => '*/*/test5'],
                'count' => 1,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'enable',
                'item' => ['label' => 'Test 5. restricted', 'url' => '*/*/test5'],
                'count' => 0,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => false,
            ],
            [
                'itemId' => 'refresh',
                'item' => ['label' => 'Test 6. Not Restricted', 'url' => '*/*/test6'],
                'count' => 1,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => true,
            ],
            [
                'itemId' => 'refresh',
                'item' => ['label' => 'Test 6. Restricted', 'url' => '*/*/test6'],
                'count' => 0,
                'withVisibilityChecker' => true,
                'isVisible' => true,
                'isAllowed' => false,
            ],
        ];
    }
}
