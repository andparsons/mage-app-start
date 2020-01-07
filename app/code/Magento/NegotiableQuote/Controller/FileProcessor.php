<?php
namespace Magento\NegotiableQuote\Controller;

use Magento\NegotiableQuote\Api\Data\AttachmentContentInterface;

/**
 * Class convert files from request to AttachmentContentInterface.
 */
class FileProcessor
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\AttachmentContentInterfaceFactory
     */
    private $attachmentFactory;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    private $readFactory;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\NegotiableQuote\Api\Data\AttachmentContentInterfaceFactory $attachmentFactory
     * @param \Magento\Framework\Filesystem\File\ReadFactory $readFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\NegotiableQuote\Api\Data\AttachmentContentInterfaceFactory $attachmentFactory,
        \Magento\Framework\Filesystem\File\ReadFactory $readFactory
    ) {
        $this->request = $request;
        $this->attachmentFactory = $attachmentFactory;
        $this->readFactory = $readFactory;
    }

    /**
     * Get attachment content array from request.
     *
     * @return AttachmentContentInterface[]
     */
    public function getFiles()
    {
        $filesArray = (array)$this->request->getFiles('files');
        $files = [];
        foreach ($filesArray as $file) {
            if (empty($file['tmp_name'])) {
                continue;
            }
            $fileContent = $this->readFactory
                ->create($file['tmp_name'], \Magento\Framework\Filesystem\DriverPool::FILE)
                ->read($file['size']);
            $fileContent = base64_encode($fileContent);
            $files[] = $this->attachmentFactory->create(
                [
                    'data' => [
                        AttachmentContentInterface::BASE64_ENCODED_DATA => $fileContent,
                        AttachmentContentInterface::TYPE => $file['type'],
                        AttachmentContentInterface::NAME => $file['name'],
                    ]
                ]
            );
        }

        return $files;
    }
}
