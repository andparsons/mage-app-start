<?php
namespace Magento\NegotiableQuote\Observer;

/**
 * Recalculate taxes on quotes if Tax Calculation Based On setting was changed
 */
class TaxConfigNotificationSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate
     */
    private $taxRecalculate;

    /**
     * @param \Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate $taxRecalculate
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate $taxRecalculate
    ) {
        $this->taxRecalculate = $taxRecalculate;
    }

    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if ($observer->getDataObject()->getPath() === 'tax/calculation/based_on'
            && $observer->getDataObject()->isValueChanged()
        ) {
            $this->taxRecalculate->recalculateTax(true);
        }
    }
}
