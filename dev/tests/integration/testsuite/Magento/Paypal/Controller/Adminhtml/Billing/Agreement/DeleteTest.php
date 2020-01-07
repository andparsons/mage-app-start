<?php
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

/**
 * @magentoAppArea adminhtml
 */
class DeleteTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Magento_Paypal::actions_manage';
        $this->uri = 'backend/paypal/billing_agreement/delete';
        parent::setUp();
    }
}
