<?php

namespace Magento\NegotiableQuote\Model\Webapi\Quote;

use Magento\NegotiableQuote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class BillingAddressManagement
 */
class BillingAddressManagement implements BillingAddressManagementInterface
{
    /**
     * @var \Magento\Quote\Api\BillingAddressManagementInterface
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator
     */
    private $validator;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @param \Magento\Quote\Api\BillingAddressManagementInterface $originalInterface
     * @param \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        \Magento\Quote\Api\BillingAddressManagementInterface $originalInterface,
        \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        NegotiableQuoteItemManagementInterface $quoteItemManagement,
        TaxHelper $taxHelper
    ) {
        $this->originalInterface = $originalInterface;
        $this->validator = $validator;
        $this->quoteRepository = $quoteRepository;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->quoteItemManagement = $quoteItemManagement;
        $this->taxHelper = $taxHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address, $useForShipping = false)
    {
        $this->validator->validate($cartId);
        $result = $this->originalInterface->assign($cartId, $address, $useForShipping);
        $this->recalculateByAddressChange($cartId);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        $this->validator->validate($cartId);
        return $this->originalInterface->get($cartId);
    }

    /**
     * @param int $cartId
     * @return void
     */
    private function recalculateByAddressChange($cartId)
    {
        $quote = $this->quoteRepository->get($cartId, ['*']);
        $quoteExtensionAttributes = $quote->getExtensionAttributes();
        if ($quoteExtensionAttributes
            && $quoteExtensionAttributes->getNegotiableQuote()
            && $quoteExtensionAttributes->getNegotiableQuote()->getIsRegularQuote()
        ) {
            $negotiableQuote = $quoteExtensionAttributes->getNegotiableQuote();
            $negotiableQuote->setIsAddressDraft(true);
            if ($this->taxHelper->getTaxBasedOn() == 'billing'
                || $this->taxHelper->getTaxBasedOn() == 'shipping'
                && $quote->getIsVirtual()
            ) {
                $isNeedRecalculate = $negotiableQuote->getNegotiatedPriceValue() === null;
                $this->quoteItemManagement
                    ->recalculateOriginalPriceTax($cartId, $isNeedRecalculate, $isNeedRecalculate);
            }
        }
    }
}
