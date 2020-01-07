<?php
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

/**
 * @magentoAppArea adminhtml
 */
class CancelTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Magento_Paypal::actions_manage';
        $this->uri = 'backend/paypal/billing_agreement/cancel';
        parent::setUp();
    }
}
