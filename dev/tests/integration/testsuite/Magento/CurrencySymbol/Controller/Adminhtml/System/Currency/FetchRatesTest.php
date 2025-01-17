<?php

namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

/**
 * Fetch Rates Test
 */
class FetchRatesTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test fetch action without service
     *
     * @return void
     */
    public function testFetchRatesActionWithoutService(): void
    {
        $request = $this->getRequest();
        $request->setParam(
            'rate_services',
            null
        );
        $this->dispatch('backend/admin/system_currency/fetchRates');

        $this->assertSessionMessages(
            $this->contains('The Import Service is incorrect. Verify the service and try again.'),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test save action with nonexistent service
     *
     * @return void
     */
    public function testFetchRatesActionWithNonexistentService(): void
    {
        $request = $this->getRequest();
        $request->setParam(
            'rate_services',
            'non-existent-service'
        );
        $this->dispatch('backend/admin/system_currency/fetchRates');

        $this->assertSessionMessages(
            $this->contains("The import model can't be initialized. Verify the model and try again."),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
