<?php

namespace Magento\Bundle\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create invoice from order on backend.
 */
class CreatePartialInvoiceStep extends \Magento\Sales\Test\TestStep\CreateInvoiceStep implements TestStepInterface
{

    /**
     * {@inheritdoc}
     */
    protected function getItems()
    {
        $items = parent::getItems();
        return $items[0]->getData()['options'];
    }
}
