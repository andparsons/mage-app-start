<?php
namespace Magento\CustomerCustomAttributes\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesQuoteAfterSave implements ObserverInterface
{
    /**
     * @var \Magento\CustomerCustomAttributes\Model\Sales\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param \Magento\CustomerCustomAttributes\Model\Sales\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\CustomerCustomAttributes\Model\Sales\QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * After save observer for quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote instanceof \Magento\Framework\Model\AbstractModel) {
            /** @var $quoteModel \Magento\CustomerCustomAttributes\Model\Sales\Quote */
            $quoteModel = $this->quoteFactory->create();
            $quoteModel->saveAttributeData($quote);
        }
        return $this;
    }
}
