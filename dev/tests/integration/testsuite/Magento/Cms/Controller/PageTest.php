<?php

/**
 * Test class for \Magento\Cms\Controller\Page.
 */
namespace Magento\Cms\Controller;

class PageTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testViewAction()
    {
        $this->dispatch('/enable-cookies');
        $this->assertContains('What are Cookies?', $this->getResponse()->getBody());
    }

    public function testViewRedirectWithTrailingSlash()
    {
        $this->dispatch('/enable-cookies/');
        $code = $this->getResponse()->getStatusCode();
        $location = $this->getResponse()->getHeader('Location')->getFieldValue();

        $this->assertEquals(301, $code, 'Invalid response code');
        $this->assertStringEndsWith('/enable-cookies', $location, 'Invalid location header');
    }

    /**
     * Test \Magento\Cms\Block\Page::_addBreadcrumbs
     */
    public function testAddBreadcrumbs()
    {
        $this->dispatch('/enable-cookies');
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        $breadcrumbsBlock = $layout->getBlock('breadcrumbs');
        $this->assertContains($breadcrumbsBlock->toHtml(), $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture cmsPageWithSystemRouteFixture
     */
    public function testCreatePageWithSameModuleName()
    {
        $this->dispatch('/shipping');
        $content = $this->getResponse()->getBody();
        $this->assertContains('Shipping Test Page', $content);
    }

    public static function cmsPageWithSystemRouteFixture()
    {
        /** @var $page \Magento\Cms\Model\Page */
        $page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
        $page->setTitle('Test title')
            ->setIdentifier('shipping')
            ->setStores([0])
            ->setIsActive(1)
            ->setContent('<h1>Shipping Test Page</h1>')
            ->setPageLayout('1column')
            ->save();
    }
}
