<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Interface for sending notifications about quotes.
 *
 * @api
 * @since 100.0.0
 */
interface EmailSenderInterface
{
    /**#@+
     * Methods for sending copies of notifications.
     */
    const EMAIL_COPY_METHOD_BCC = 'bcc';
    const EMAIL_COPY_METHOD_SEPARATE_EMAIL = 'copy';
    /**#@-*/

    /**
     * Send email to merchant.
     *
     * @param CartInterface $quote
     * @param string $emailTemplate
     * @return void
     */
    public function sendChangeQuoteEmailToMerchant(CartInterface $quote, $emailTemplate);

    /**
     * Send email to buyer.
     *
     * @param CartInterface $quote
     * @param string $emailTemplate
     * @param string $comment [optional]
     * @return void
     */
    public function sendChangeQuoteEmailToBuyer(
        CartInterface $quote,
        $emailTemplate,
        $comment = ''
    );
}
