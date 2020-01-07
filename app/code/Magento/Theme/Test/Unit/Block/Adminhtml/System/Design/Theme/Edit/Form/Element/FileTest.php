<?php
namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

class FileTest extends \PHPUnit\Framework\TestCase
{
    public function testGetHtmlAttributes()
    {
        /** @var $fileBlock \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File */
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $collectionFactory = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);

        $fileBlock = $helper->getObject(
            \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File::class,
            ['factoryCollection' => $collectionFactory]
        );

        $this->assertContains('accept', $fileBlock->getHtmlAttributes());
        $this->assertContains('multiple', $fileBlock->getHtmlAttributes());
    }
}
