<?php
namespace Magento\Backend\Block\System\Store;

/**
 * @magentoAppArea adminhtml
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    public function testGetHeaderText()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Backend\Block\System\Store\Delete */
        $block = $layout->createBlock(\Magento\Backend\Block\System\Store\Delete::class, 'block');

        $dataObject = new \Magento\Framework\DataObject();
        $form = $block->getChildBlock('form');
        $form->setDataObject($dataObject);

        $expectedValue = 'header_text_test';
        $this->assertNotContains($expectedValue, (string)$block->getHeaderText());

        $dataObject->setName($expectedValue);
        $this->assertContains($expectedValue, (string)$block->getHeaderText());
    }
}
