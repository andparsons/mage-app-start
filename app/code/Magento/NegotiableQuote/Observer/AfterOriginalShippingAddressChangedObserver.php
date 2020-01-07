<?php
namespace Magento\NegotiableQuote\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate;
use Magento\Tax\Helper\Data;

/**
 * Recalculate taxes for quote if any of Tax->Shipping Setting was changed and Tax Calculation Based equals "origin"
 */
class AfterOriginalShippingAddressChangedObserver implements ObserverInterface
{
    /**
     * @var NegotiableQuoteTaxRecalculate
     */
    protected $taxRecalculate;

    /**
     * @var Data
     */
    private $taxConfig;

    /**
     * @param NegotiableQuoteTaxRecalculate $taxRecalculate
     */
    public function __construct(
        NegotiableQuoteTaxRecalculate $taxRecalculate,
        Data $taxConfig
    ) {
        $this->taxRecalculate = $taxRecalculate;
        $this->taxConfig = $taxConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see \Magento\Tax\Model\Config\Source\Basedon
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->taxConfig->getTaxBasedOn() === 'origin') {
            $this->taxRecalculate->recalculateTax();
        }
    }
}
