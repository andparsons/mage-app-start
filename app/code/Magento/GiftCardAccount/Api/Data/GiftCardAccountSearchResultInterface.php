<?php
namespace Magento\GiftCardAccount\Api\Data;

/**
 * Interface GiftCardAccountSearchResultInterface
 * @api
 * @since 100.0.2
 */
interface GiftCardAccountSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get GiftCard Account list
     *
     * @return \Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface[]
     */
    public function getItems();
}
