<?php

namespace Magento\NegotiableQuote\Model\Attachment;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Handler for uploading files.
 */
class UploadHandler
{
    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Uploader factory
     *
     * @var \Magento\NegotiableQuote\Model\Attachment\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Attachment factory
     *
     * @var \Magento\NegotiableQuote\Model\CommentAttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Comment ID
     *
     * @var int
     */
    private $commentId;

    /**
     * UploadHandler constructor.
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\NegotiableQuote\Model\Attachment\UploaderFactory $uploaderFactory
     * @param \Magento\NegotiableQuote\Model\CommentAttachmentFactory $attachmentFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param int $commentId
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\NegotiableQuote\Model\Attachment\UploaderFactory $uploaderFactory,
        \Magento\NegotiableQuote\Model\CommentAttachmentFactory $attachmentFactory,
        \Psr\Log\LoggerInterface $logger,
        $commentId
    ) {
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->attachmentFactory = $attachmentFactory;
        $this->logger = $logger;
        $this->commentId = $commentId;
    }

    /**
     * Save file and create attachment for comment.
     *
     * @param \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface $file
     * @return void
     */
    public function process(\Magento\NegotiableQuote\Api\Data\AttachmentContentInterface $file)
    {
        $fileContent = base64_decode($file->getBase64EncodedData(), true);
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $tmpFileName = substr(md5(rand()), 0, 7) . '.' . $file->getName();
        $tmpDirectory->writeFile($tmpFileName, $fileContent);

        $fileAttributes = [
            'tmp_name' => $tmpDirectory->getAbsolutePath() . $tmpFileName,
            'name' => $file->getName()
        ];

        /** @var \Magento\NegotiableQuote\Model\Attachment\Uploader $uploader */
        $uploader = $this->uploaderFactory->create();
        $uploader->processFileAttributes($fileAttributes);
        $uploader->addValidateCallback('nameLength', $uploader, 'validateNameLength');
        $uploader->addValidateCallback('size', $uploader, 'validateSize');
        $uploader->setAllowRenameFiles(true)
            ->setFilesDispersion(true)
            ->setAllowCreateFolders(true);
        $path = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath(
            \Magento\NegotiableQuote\Model\CommentManagement::ATTACHMENTS_FOLDER
        );
        $data = $uploader->save($path);

        if (isset($data['name']) && isset($data['file'])) {
            /** @var \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface $attachment */
            $attachment = $this->attachmentFactory->create();
            $attachment->setCommentId($this->commentId)
                ->setFileName($data['name'])
                ->setFilePath($data['file'])
                ->setFileType($file->getType())
                ->save();
        }
    }
}
