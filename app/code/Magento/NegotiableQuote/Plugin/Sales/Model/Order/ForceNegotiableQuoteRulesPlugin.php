<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Plugin\Sales\Model\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Forces only negotiable quote rules for order if necessary.
 */
class ForceNegotiableQuoteRulesPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var string
     */
    private $originalAppliedRuleIds = '';

    /**
     * @var float
     */
    private $originalDiscountAmount = 0;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Forces only negotiable quote rules for order if necessary.
     *
     * @param Order $subject
     * @param callable $proceed
     * @return Order
     */
    public function aroundPlace(Order $subject, callable $proceed): Order
    {
        $isDiscountChanges = $this->setRulesOnOrder($subject);

        $result = $proceed();

        if ($isDiscountChanges) {
            $subject->setAppliedRuleIds($this->originalAppliedRuleIds);
            $subject->setDiscountAmount($this->originalDiscountAmount);
        }

        return $result;
    }

    /**
     * Set negotiable quote rules on order.
     *
     * @param Order $order
     * @return bool
     */
    private function setRulesOnOrder(Order $order): bool
    {
        $this->originalAppliedRuleIds = '';
        $this->originalDiscountAmount = 0;
        if ($order && $order->getQuoteId() && $order->getDiscountAmount() == 0) {
            try {
                $quote = $this->quoteRepository->get($order->getQuoteId(), ['*']);
                $negotiableQuote = $quote->getExtensionAttributes()
                && $quote->getExtensionAttributes()->getNegotiableQuote()
                    ? $quote->getExtensionAttributes()->getNegotiableQuote()
                    : null;
                if ($negotiableQuote && $negotiableQuote->getAppliedRuleIds()) {
                    $this->originalAppliedRuleIds = $order->getAppliedRuleIds();
                    $this->originalDiscountAmount = $order->getDiscountAmount();
                    $order->setAppliedRuleIds($negotiableQuote->getAppliedRuleIds());
                    $order->setDiscountAmount(1);
                    return true;
                }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (NoSuchEntityException $e) {
                //no log exception
            }
        }

        return false;
    }
}
