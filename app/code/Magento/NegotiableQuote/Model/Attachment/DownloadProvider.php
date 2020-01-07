<?php

namespace Magento\NegotiableQuote\Model\Attachment;

/**
 * Class DownloadProvider
 */
class DownloadProvider
{
    /**
     * Comment attachment factory
     *
     * @var \Magento\NegotiableQuote\Model\CommentAttachmentFactory
     */
    private $commentAttachmentFactory;

    /**
     * Download approve
     *
     * @var \Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowInterface
     */
    private $allowDownload;

    /**
     * File
     *
     * @var File
     */
    private $file;

    /**
     * Attachment ID
     *
     * @var string
     */
    private $attachmentId;

    /**
     * DownloadProvider constructor
     *
     * @param \Magento\NegotiableQuote\Model\CommentAttachmentFactory $commentAttachmentFactory
     * @param \Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowInterface $allowDownload
     * @param File $file
     * @param int $attachmentId
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\CommentAttachmentFactory $commentAttachmentFactory,
        \Magento\NegotiableQuote\Model\Attachment\DownloadPermission\AllowInterface $allowDownload,
        File $file,
        $attachmentId
    ) {
        $this->commentAttachmentFactory = $commentAttachmentFactory;
        $this->allowDownload = $allowDownload;
        $this->file = $file;
        $this->attachmentId = $attachmentId;
    }

    /**
     * Get attachment contents
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     * @return void
     */
    public function getAttachmentContents()
    {
        if (!$this->allowDownload->isAllowed($this->attachmentId)) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Attachment not found.'));
        }

        /** @var \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface $attachment */
        $attachment = $this->commentAttachmentFactory->create()->load($this->attachmentId);

        if ($attachment && $attachment->getAttachmentId() === null) {
            throw new \Magento\Framework\Exception\NoSuchEntityException;
        }

        $this->file->downloadContents($attachment);
    }
}
