<?php

namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\SharedCatalog\Wizard\Step\Structure\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Step\Structure\Category\Tree;

/**
 * Test for Block Adminhtml\SharedCatalog\Wizard\Step\Structure\CategoryTree.
 */
class TreeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Step\Structure\Category\Tree
     * |\PHPUnit_Framework_MockObject_MockObject
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
    private $sharedCatalogRepositoryMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\UrlBuilder::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogRepositoryMock = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->tree = $this->objectManagerHelper->getObject(
            Tree::class,
            [
                'context' => $this->contextMock,
                'urlBuilder' => $this->urlBuilder,
                'sharedCatalogRepository' => $this->sharedCatalogRepositoryMock
            ]
        );
    }

    /**
     * Test for getAssignUrl().
     *
     * @return void
     */
    public function testGetAssignUrl()
    {
        $assignedUrl = 'test/url';
        $route = Tree::CATEGORY_ASSIGN_ROUTE;
        $this->urlBuilder->expects($this->exactly(1))->method('getUrl')->with($route)->willReturn($assignedUrl);

        $this->assertEquals($assignedUrl, $this->tree->getAssignUrl());
    }
}
