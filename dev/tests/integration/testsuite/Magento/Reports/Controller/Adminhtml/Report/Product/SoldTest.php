<?php

namespace Magento\Reports\Controller\Adminhtml\Report\Product;

/**
 * @magentoAppArea adminhtml
 */
class SoldTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testExecute()
    {
        $this->dispatch('backend/reports/report_product/sold');
        $actual = $this->getResponse()->getBody();
        $this->assertContains('Ordered Products Report', $actual);
        //verify if SKU column is presented on grid
        $this->assertContains('SKU', $actual);
    }
}
