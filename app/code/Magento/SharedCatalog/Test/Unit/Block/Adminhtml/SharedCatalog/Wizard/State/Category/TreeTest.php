<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Wizard\State\Category;

use Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\State\Category\Tree;
use \Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Test for Block Adminhtml\SharedCatalog\Wizard\State\Category\Tree.
 */
class TreeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var Tree
     */
    private $tree;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

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
        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->urlBuilder = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalog = $this->getMockBuilder(SharedCatalogInterface::class)
            ->setMethods(['getStoreId', 'getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->tree = $this->objectManager->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\State\Category\Tree::class,
            [
                'context' => $this->context,
                'urlBuilder' => $this->urlBuilder,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'data' => [],
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
        $routePath = Tree::TREE_INIT_ROUTE;
        $storeId = 'test store id';
        $url = 'test/url';
        $sharedCatalogUrlParam = SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM;
        $sharedCatalogId = 234;
        $routeParams = [
            '_query' => [
                'filters' => [
                    'store' => [
                        'id' => $storeId
                    ],
                    'shared_catalog' => [
                        'id' => $sharedCatalogId
                    ]
                ]
            ]
        ];
        $catalogId = '234';

        $this->request->expects($this->exactly(1))->method('getParam')->with($sharedCatalogUrlParam)
            ->willReturn($catalogId);

        $this->sharedCatalogRepository->expects($this->exactly(1))->method('get')->with($catalogId)
            ->willReturn($this->sharedCatalog);

        $this->sharedCatalog->expects($this->exactly(1))->method('getStoreId')->willReturn($storeId);

        $this->urlBuilder->expects($this->exactly(1))->method('getUrl')->with($routePath, $routeParams)
            ->willReturn($url);

        $this->assertEquals($url, $this->tree->getTreeUrl());
    }
}
