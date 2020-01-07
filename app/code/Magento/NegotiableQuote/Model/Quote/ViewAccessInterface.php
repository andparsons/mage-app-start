<?php

declare(strict_types=1);

namespace Magento\NegotiableQuote\Model\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Check access for viewing quotes.
 */
interface ViewAccessInterface
{
    /**
     * Whether the current user can view a quote.
     *
     * @param CartInterface $quote
     * @throws LocalizedException
     *
     * @return bool
     */
    public function canViewQuote(CartInterface $quote): bool;
}
