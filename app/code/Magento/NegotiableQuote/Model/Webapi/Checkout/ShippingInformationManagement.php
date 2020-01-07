<?php

namespace Magento\NegotiableQuote\Model\Webapi\Checkout;

use Magento\NegotiableQuote\Api\ShippingInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class ShippingInformationManagement
 */
class ShippingInformationManagement implements ShippingInformationManagementInterface
{
    /**
     * @var \Magento\Checkout\Api\ShippingInformationManagementInterface
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
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @param \Magento\Checkout\Api\ShippingInformationManagementInterface $originalInterface
     * @param \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param TaxHelper $taxHelper
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     */
    public function __construct(
        \Magento\Checkout\Api\ShippingInformationManagementInterface $originalInterface,
        \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator,
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        NegotiableQuoteItemManagementInterface $quoteItemManagement,
        TaxHelper $taxHelper,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
    ) {
        $this->originalInterface = $originalInterface;
        $this->validator = $validator;
        $this->quoteRepository = $quoteRepository;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->quoteItemManagement = $quoteItemManagement;
        $this->taxHelper = $taxHelper;
        $this->cartTotalsRepository = $cartTotalsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function saveAddressInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $this->validator->validate($cartId);
        $result = $this->originalInterface->saveAddressInformation($cartId, $addressInformation);
        $this->recalculateByAddressChange($cartId);
        $result->setTotals($this->cartTotalsRepository->get($cartId));

        return $result;
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
            if ($this->taxHelper->getTaxBasedOn() == 'shipping' || $this->taxHelper->getTaxBasedOn() == 'billing') {
                $isNeedRecalculate = $negotiableQuote->getNegotiatedPriceValue() === null;
                $this->quoteItemManagement
                    ->recalculateOriginalPriceTax($cartId, $isNeedRecalculate, $isNeedRecalculate);
            }
        }
    }
}
