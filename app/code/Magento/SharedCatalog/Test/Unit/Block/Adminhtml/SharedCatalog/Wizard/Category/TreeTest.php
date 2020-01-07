<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Wizard\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Category\Tree;

/**
 * Test for block Adminhtml\SharedCatalog\Wizard\Category\Tree.
 */
class TreeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Tree|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tree;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

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
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->urlBuilder = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::class)
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->tree = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Category\Tree::class,
            [
                'context' => $this->contextMock,
                'urlBuilder' => $this->urlBuilder,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test for getTreeUrl().
     *
     * @return void
     */
    public function testGetTreeUrl()
    {
        $sharedCatalogId = 346;

        $param = \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $this->request->expects($this->exactly(1))->method('getParam')->with($param)->willReturn($sharedCatalogId);

        $this->sharedCatalog->expects($this->exactly(1))->method('getId')->willReturn($sharedCatalogId);

        $this->sharedCatalogRepository->expects($this->exactly(1))->method('get')->with($sharedCatalogId)
            ->willReturn($this->sharedCatalog);

        $initUrl = 'test/url';
        $routeParams = [
            '_query' => [
                'filters' => [
                    'shared_catalog' => [
                        'id' => $sharedCatalogId
                    ]
                ]
            ]
        ];
        $routePath = \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Category\Tree::TREE_INIT_ROUTE;
        $this->urlBuilder->expects($this->exactly(1))->method('getUrl')->with($routePath, $routeParams)
            ->willReturn($initUrl);

        $this->assertEquals($initUrl, $this->tree->getTreeUrl());
    }
}
