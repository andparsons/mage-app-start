<?php
namespace Magento\Cms\Block;

class BlockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Cms/_files/block.php
     * @magentoDataFixture Magento/Variable/_files/variable.php
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     * @magentoConfigFixture current_store web/unsecure/base_link_url http://example.com/
     */
    public function testToHtml()
    {
        $cmsBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Cms\Model\Block::class
        );
        $cmsBlock->load('fixture_block', 'identifier');
        /** @var $block \Magento\Cms\Block\Block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Cms\Block\Block::class
        );
        $block->setBlockId($cmsBlock->getId());
        $result = $block->toHtml();
        $this->assertContains('<a href="http://example.com/', $result);
        $this->assertContains('<p>Config value: "http://example.com/".</p>', $result);
        $this->assertContains('<p>Custom variable: "HTML Value".</p>', $result);
    }
}
