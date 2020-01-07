<?php
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Interface for bulk exchange.
 *
 * @api
 * @since 102.0.3
 */
interface ExchangeInterface
{
    /**
     * Send messages in bulk to the queue.
     *
     * @param string $topic
     * @param \Magento\Framework\MessageQueue\EnvelopeInterface[] $envelopes
     * @return mixed
     * @since 102.0.3
     */
    public function enqueue($topic, array $envelopes);
}
