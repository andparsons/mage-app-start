<?php

namespace Magento\NegotiableQuote\Api;

/**
 * Interface for load quote comments with attachment.
 *
 * @api
 * @since 100.0.0
 */
interface CommentLocatorInterface
{
    /**
     * Returns comments for a specified negotiable quote.
     *
     * @param int $quoteId Negotiable Quote ID.
     * @return \Magento\NegotiableQuote\Api\Data\CommentInterface[] An array of quote comments.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getListForQuote($quoteId);
}
