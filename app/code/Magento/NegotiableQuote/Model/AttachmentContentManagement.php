<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\AttachmentContentManagementInterface;
use Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\CollectionFactory;
use Magento\Framework\Exception\InputException;
use Magento\NegotiableQuote\Api\Data\AttachmentContentInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\AttachmentContentInterface;
use Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem;

/**
 * Class is responsible for retrieving the list of negotiable quotes attachments by attachments ids.
 */
class AttachmentContentManagement implements AttachmentContentManagementInterface
{
    /**
     * @var CollectionFactory
     */
    private $attachmentCollectionFactory;

    /**
     * @var AttachmentContentInterfaceFactory
     */
    private $attachmentContentFactory;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @param CollectionFactory $attachmentCollectionFactory
     * @param AttachmentContentInterfaceFactory $attachmentFactory
     * @param File $fileDriver
     * @param Filesystem $filesystem
     */
    public function __construct(
        CollectionFactory $attachmentCollectionFactory,
        AttachmentContentInterfaceFactory $attachmentFactory,
        File $fileDriver,
        Filesystem $filesystem
    ) {
        $this->attachmentCollectionFactory = $attachmentCollectionFactory;
        $this->attachmentContentFactory = $attachmentFactory;
        $this->fileDriver = $fileDriver;
        $this->mediaDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * @inheritdoc
     */
    public function get(array $attachmentIds)
    {
        $attachmentContents = [];
        $exception = new InputException(
            __('Cannot obtain the requested data. You must fix the errors listed below first.')
        );
        $attachmentCollection = $this->attachmentCollectionFactory->create();
        $attachmentCollection->addFieldToFilter('attachment_id', ['in' => $attachmentIds]);
        foreach ($attachmentCollection->getItems() as $attachment) {
            $fileContent = $this->getAttachmentContents($attachment);
            $attachmentContents[] = $this->attachmentContentFactory->create(
                [
                    'data' => [
                        AttachmentContentInterface::BASE64_ENCODED_DATA => $fileContent,
                        AttachmentContentInterface::TYPE => $attachment->getFileType(),
                        AttachmentContentInterface::NAME => $attachment->getFileName(),
                    ]
                ]
            );
        }
        $nullAttachmentIds = array_diff($attachmentIds, $attachmentCollection->getAllIds());
        if (!empty($nullAttachmentIds)) {
            foreach ($nullAttachmentIds as $nullAttachmentId) {
                $exception->addError(
                    __(
                        'Requested attachment is not found. Row ID: %fieldName = %fieldValue',
                        ['fieldName' => 'AttachmentID', 'fieldValue' => $nullAttachmentId]
                    )
                );
            }
            throw $exception;
        }

        return $attachmentContents;
    }

    /**
     * Retrieves attachment file and encodes it with base64.
     *
     * @param CommentAttachmentInterface $attachment
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getAttachmentContents(CommentAttachmentInterface $attachment)
    {
        $folderPath = $this->mediaDirectory
            ->getAbsolutePath(\Magento\NegotiableQuote\Model\CommentManagement::ATTACHMENTS_FOLDER);
        $attachmentPath = $folderPath . $attachment->getFilePath();
        $fileContent = $this->fileDriver->fileGetContents($attachmentPath);
        $fileContent = base64_encode($fileContent);

        return $fileContent;
    }
}
