<?php

namespace Magento\NegotiableQuote\Model\Attachment\DownloadPermission;

/**
 * Class AllowInterface
 */
interface AllowInterface
{
    /**
     * Is download allowed
     *
     * @param int $attachmentId
     * @return bool
     */
    public function isAllowed($attachmentId);
}
