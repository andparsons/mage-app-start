<?php

namespace Magento\NegotiableQuote\Block\Adminhtml\Quote;

/**
 * Class History
 *
 * @api
 * @since 100.0.0
 */
class History extends \Magento\NegotiableQuote\Block\Quote\History
{
    /**
     * Get attachment URL
     *
     * @param int $attachmentId
     * @return string
     */
    public function getAttachmentUrl($attachmentId)
    {
        return $this->getUrl('*/*/download', ['attachmentId' => $attachmentId]);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processData(array $data)
    {
        if (isset($data['subtotal'])) {
            unset($data['subtotal']);
        }
        return $data;
    }
}
