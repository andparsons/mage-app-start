<?php

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link
     */
    protected $linkRenderer;

    protected function setUp()
    {
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->willReturn('http://magento.loc/linkurl');
        $this->contextMock = $this->createPartialMock(
            \Magento\Backend\Block\Context::class,
            ['getEscaper', 'getUrlBuilder']
        );
        $this->contextMock->expects($this->any())->method('getEscaper')->will($this->returnValue($this->escaperMock));
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilderMock));

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->linkRenderer = $this->objectManagerHelper->getObject(
            \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link::class,
            ['context' => $this->contextMock]
        );
    }

    /**
     * Test the basic render action.
     */
    public function testRender()
    {
        $expectedResult = '<a href="http://magento.loc/linkurl" title="Link Caption">Link Caption</a>';
        $column = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCaption', 'getId'])
            ->getMock();
        $column->expects($this->any())
            ->method('getCaption')
            ->will($this->returnValue('Link Caption'));
        $column->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $this->escaperMock->expects($this->at(0))->method('escapeHtmlAttr')->willReturn('Link Caption');
        $this->linkRenderer->setColumn($column);
        $object = new \Magento\Framework\DataObject(['id' => '1']);
        $actualResult = $this->linkRenderer->render($object);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
