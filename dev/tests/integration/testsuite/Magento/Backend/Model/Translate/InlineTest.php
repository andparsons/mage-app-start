<?php
namespace Magento\Backend\Model\Translate;

/**
 * @magentoAppArea adminhtml
 */
class InlineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $_translateInline;

    protected function setUp()
    {
        $this->_translateInline = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Translate\InlineInterface::class
        );
    }

    /**
     * @magentoAdminConfigFixture dev/translate_inline/active_admin 1
     * @covers \Magento\Framework\Translate\Inline::getAjaxUrl
     */
    public function testAjaxUrl()
    {
        $body = '<html><body>some body</body></html>';
        /** @var \Magento\Backend\Model\UrlInterface $url */
        $url = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\UrlInterface::class);
        $url->getUrl(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE . '/ajax/translate');
        $this->_translateInline->processResponseBody($body, true);
        $expected = str_replace(
            [':', '/'],
            ['\u003A', '\u002F'],
            $url->getUrl(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE . '/ajax/translate')
        );
        $this->assertContains($expected, $body);
    }
}
