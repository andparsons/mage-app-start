<?php
namespace Magento\Framework\View;

use \Magento\Framework\App\State;

class LayoutTestWithExceptions extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $layoutFactory = $objectManager->get(\Magento\Framework\View\LayoutFactory::class);
        $this->layout = $layoutFactory->create();
        $layoutElement = new \Magento\Framework\View\Layout\Element(
            __DIR__ . '/_files/layout_with_exceptions/layout.xml',
            0,
            true
        );

        $this->layout->setXml($layoutElement);
        $objectManager->get(\Magento\Framework\App\Cache\Type\Layout::class)->clean();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Construction problem.
     */
    public function testProcessWithExceptionsDeveloperMode()
    {
        $this->layout->generateElements();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testProcessWithExceptions()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setMode(State::MODE_DEFAULT);

        $this->layout->generateElements();

        $this->layout->addOutputElement('block.with.broken.constructor');
        $this->layout->addOutputElement('block.with.broken.layout');
        $this->layout->addOutputElement('block.with.broken.action');

        $this->assertEmpty($this->layout->getOutput());
    }
}
