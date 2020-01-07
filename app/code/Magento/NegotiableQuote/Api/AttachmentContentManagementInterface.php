<?php

namespace Magento\NegotiableQuote\Api;

/**
 * Interface for retrieving the list of negotiable quotes attachments.
 *
 * @api
 * @since 100.0.0
 */
interface AttachmentContentManagementInterface
{
    /**
     * Returns content for one or more files attached on the quote comment.
     *
     * @param int[] $attachmentIds
     * @return \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function get(array $attachmentIds);
}
