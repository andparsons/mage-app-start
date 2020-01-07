<?php
namespace Magento\Quote\Model\Quote\Address\Total;

/**
 * Interface \Magento\Quote\Model\Quote\Address\Total\ReaderInterface
 *
 */
interface ReaderInterface
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return  []
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total);
}
