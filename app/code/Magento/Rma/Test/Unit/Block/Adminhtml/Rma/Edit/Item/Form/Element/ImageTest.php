<?php
namespace Magento\Rma\Test\Unit\Block\Adminhtml\Rma\Edit\Item\Form\Element;

use Magento\Framework\Escaper;

/**
 * Test class for Magento\Rma\Block\Adminhtml\Rma\Edit\Item\Form\Element\Image
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Form\Element\Image
     */
    protected $image;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendHelperMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $escaper = $objectManager->getObject(Escaper::class);
        $this->backendHelperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->image = $objectManager->getObject(
            \Magento\Customer\Block\Adminhtml\Form\Element\Image::class,
            [
                'adminhtmlData' => $this->backendHelperMock,
                '_escaper' => $escaper
            ]
        );
    }

    public function testGetHiddenInput()
    {
        $name = 'test_name';
        $formMock = $this->getMockBuilder(\Magento\Framework\Data\Form::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->image->setForm($formMock);
        $this->image->setName($name);

        $this->assertContains($name, $this->image->getElementHtml());
    }
}
