<?php
namespace Magento\Sales\Model\Order\Invoice\Total;

class Grand extends AbstractTotal
{
    /**
     * Collect invoice grand total
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        return $this;
    }
}
