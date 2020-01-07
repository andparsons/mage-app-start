<?php
declare(strict_types=1);

namespace Magento\VersionsCms\Test\Unit\Block\Adminhtml\Cms\Page\Edit\Tab;

use Magento\Cms\Model\Page;
use Magento\Framework\Escaper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Block\Template\Context;
use Magento\VersionsCms\Block\Adminhtml\Cms\Page\Edit\Tab\Hierarchy;

/**
 * Test for \Magento\VersionsCms\Block\Adminhtml\Cms\Page\Edit\Tab\Hierarchy
 */
class HierarchyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Hierarchy
     */
    private $block;

    /**
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    /**
     * @var EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonEncoderMock;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->pageMock = $this->createPartialMock(
            Page::class,
            ['getTitle', 'getId']
        );

        $this->jsonEncoderMock = $this->createPartialMock(
            EncoderInterface::class,
            ['encode']
        );
        $this->registryMock = $this->createPartialMock(
            Registry::class,
            ['registry']
        );

        $this->escaperMock = $this->createPartialMock(
            Escaper::class,
            ['escapeHtml']
        );

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getEscaper')
            ->willReturn($this->escaperMock);

        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(
            Hierarchy::class,
            [
                'context' => $contextMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'registry' => $this->registryMock,
            ]
        );
    }

    /**
     * @param string $pageTitle
     * @param string $escapedPageTitle
     * @return void
     * @dataProvider getCurrentPageJsonDataProvider
     */
    public function testGetCurrentPageJson(string $pageTitle, string $escapedPageTitle): void
    {
        $pageId = 1;
        $this->registryMock->expects($this->exactly(2))
            ->method('registry')
            ->with('cms_page')
            ->willReturn($this->pageMock);
        $this->pageMock->expects($this->once())->method('getTitle')->willReturn($pageTitle);
        $this->pageMock->expects($this->once())->method('getId')->willReturn($pageId);
        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with($pageTitle)
            ->willReturn($escapedPageTitle);
        $data = [
            'label' => $escapedPageTitle,
            'id' => $pageId,
        ];
        $encodedData = \Zend_Json::encode($data);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->with($data)->willReturn($encodedData);

        $this->assertEquals($encodedData, $this->block->getCurrentPageJson());
    }

    /**
     * @return array
     */
    public function getCurrentPageJsonDataProvider(): array
    {
        return [
            [
                'pageTitle' => 'Page Title',
                'escapedPageTitle' => 'Page Title'
            ],
            [
                'pageTitle' => 'Page "Title"',
                'escapedPageTitle' => 'Page &quot;Title&quot;'
            ],
        ];
    }
}
