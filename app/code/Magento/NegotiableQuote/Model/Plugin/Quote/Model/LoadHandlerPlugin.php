<?php

namespace Magento\NegotiableQuote\Model\Plugin\Quote\Model;

use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\Quote\Model\QuoteRepository\LoadHandler;
use Magento\Framework\App\RequestInterface;

/**
 * Class for apply negotiable extension attributes to quote after load if possible.
 */
class LoadHandlerPlugin
{
    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var RestrictionInterface
     */
    private $restriction;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CartExtensionFactory $cartExtensionFactory
     * @param RestrictionInterface $restriction
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param RequestInterface $request
     */
    public function __construct(
        CartExtensionFactory $cartExtensionFactory,
        RestrictionInterface $restriction,
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        RequestInterface $request
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->restriction = $restriction;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->request = $request;
    }

    /**
     * Plugin to apply negotiable attributes to quote before quote load.
     *
     * @param LoadHandler $subject
     * @param CartInterface $quote
     * @return array
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeLoad(LoadHandler $subject, CartInterface $quote)
    {
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()) {
            return [$quote];
        }
        try {
            /** @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface $negotiableQuote */
            $negotiableQuote = $this->negotiableQuoteRepository->getById($quote->getId());
            if ($negotiableQuote && $negotiableQuote->getIsRegularQuote()) {
                $this->restriction->setQuote($quote);
            }
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Negotiated quote not found.'));
        }
        if ($negotiableQuote->getIsRegularQuote()
            && $quote->getCustomerGroupId() != $quote->getCustomer()->getGroupId()
        ) {
            $quote->unsetData('customer_group_id');
        }
        $quoteExtension = $quote->getExtensionAttributes() ?: $this->cartExtensionFactory->create();
        $quote->setExtensionAttributes($quoteExtension->setNegotiableQuote($negotiableQuote));
        $this->activateQuote($quote);
        return [$quote];
    }

    /**
     * Set negotiable quote as active to pass validation on checkout.
     *
     * @param CartInterface $quote
     * @return void
     */
    private function activateQuote(CartInterface $quote)
    {
        if ($quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote() !== null) {
            $quote->setIsActive(true);
        }
    }
}
