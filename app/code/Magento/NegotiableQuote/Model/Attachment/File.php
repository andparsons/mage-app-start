<?php

namespace Magento\NegotiableQuote\Model\Attachment;

/**
 * Class File
 */
class File
{
    /**
     * Filesystem driver
     *
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $fileDriver;

    /**
     * File factory
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * Media directory
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * DownloadProvider constructor
     *
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fileDriver = $fileDriver;
        $this->fileFactory = $fileFactory;
        $this->mediaDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * Get contents
     *
     * @param \Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface $attachment
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function downloadContents(\Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface $attachment)
    {
        $fileName = $attachment->getFileName();
        $attachmentPath = $this->mediaDirectory
                ->getAbsolutePath(\Magento\NegotiableQuote\Model\CommentManagement::ATTACHMENTS_FOLDER)
            . $attachment->getFilePath();
        $fileSize = isset($this->fileDriver->stat($attachmentPath)['size'])
            ? $this->fileDriver->stat($attachmentPath)['size']
            : 0;

        $this->fileFactory->create(
            $fileName,
            $this->fileDriver->fileGetContents($attachmentPath),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream',
            $fileSize
        );
    }
}
