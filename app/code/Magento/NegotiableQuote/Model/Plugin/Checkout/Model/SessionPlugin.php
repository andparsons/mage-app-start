<?php

namespace Magento\NegotiableQuote\Model\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Plugin for changing current quote id in checkout session.
 */
class SessionPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $restriction
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
        $this->restriction = $restriction;
    }

    /**
     * Changing current quote id in checkout session.
     * Change quote id to negotiable quote if checkout from quote is processing.
     * Change quote id to null if current quote is negotiable and checkout from quote is't processing.
     *
     * @param Session $subject
     * @param int $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetQuoteId(Session $subject, $result)
    {
        $id = $this->request->getParam('negotiableQuoteId');
        if ($id) {
            $quote = $this->quoteRepository->get($id, ['*']);
            $restriction = $this->restriction->setQuote($quote);
            if ($restriction->canProceedToCheckout()) {
                $quote->setIsActive(true);
                $result = $id;
            }
        } elseif (!empty($result)) {
            try {
                $quote = $this->quoteRepository->get($result, ['*']);
                if ($quote
                    && $quote->getExtensionAttributes()
                    && $quote->getExtensionAttributes()->getNegotiableQuote()
                    && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()
                ) {
                    $result = null;
                }
            } catch (NoSuchEntityException $e) {
                return $result;
            }
        }

        return $result;
    }
}
