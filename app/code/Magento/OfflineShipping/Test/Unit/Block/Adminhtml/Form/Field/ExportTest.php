<?php
namespace Magento\OfflineShipping\Test\Unit\Block\Adminhtml\Form\Field;

class ExportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflineShipping\Block\Adminhtml\Form\Field\Export
     */
    protected $_object;

    protected function setUp()
    {
        $backendUrl = $this->createMock(\Magento\Backend\Model\UrlInterface::class);
        $backendUrl->expects($this->once())->method('getUrl')->with("*/*/exportTablerates", ['website' => 1]);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $objectManager->getObject(
            \Magento\OfflineShipping\Block\Adminhtml\Form\Field\Export::class,
            ['backendUrl' => $backendUrl]
        );
    }

    public function testGetElementHtml()
    {
        $expected = 'some test data';

        $form = $this->createPartialMock(\Magento\Framework\Data\Form::class, ['getParent']);
        $parentObjectMock = $this->createPartialMock(\Magento\Backend\Block\Template::class, ['getLayout']);
        $layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);

        $blockMock = $this->createMock(\Magento\Backend\Block\Widget\Button::class);

        $requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $requestMock->expects($this->once())->method('getParam')->with('website')->will($this->returnValue(1));

        $mockData = $this->createPartialMock(\stdClass::class, ['toHtml']);
        $mockData->expects($this->once())->method('toHtml')->will($this->returnValue($expected));

        $blockMock->expects($this->once())->method('getRequest')->will($this->returnValue($requestMock));
        $blockMock->expects($this->any())->method('setData')->will($this->returnValue($mockData));

        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));
        $parentObjectMock->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));
        $form->expects($this->once())->method('getParent')->will($this->returnValue($parentObjectMock));

        $this->_object->setForm($form);
        $this->assertEquals($expected, $this->_object->getElementHtml());
    }
}
