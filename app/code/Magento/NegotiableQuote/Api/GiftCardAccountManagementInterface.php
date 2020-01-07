<?php

namespace Magento\NegotiableQuote\Api;

/**
 * Interface GiftCardAccountManagementInterface
 * @api
 * @since 100.0.0
 */
interface GiftCardAccountManagementInterface
{
    /**
     * Remove GiftCard Account entity
     *
     * @param int $cartId
     * @param string $giftCardCode
     * @return bool
     */
    public function deleteByQuoteId($cartId, $giftCardCode);

    /**
     * @param int $cartId
     * @param \Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface $giftCardAccountData
     * @return bool
     */
    public function saveByQuoteId(
        $cartId,
        \Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface $giftCardAccountData
    );
}
