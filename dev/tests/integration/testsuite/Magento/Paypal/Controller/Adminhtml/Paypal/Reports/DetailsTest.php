<?php
namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

/**
 * @magentoAppArea adminhtml
 */
class DetailsTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Magento_Paypal::paypal_settlement_reports_view';
        $this->uri = 'backend/paypal/paypal_reports/details';
        parent::setUp();
    }
}
