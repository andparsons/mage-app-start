<?php

namespace Magento\NegotiableQuote\Model\Validator;

/**
 * Validator for attached files.
 */
class Files implements ValidatorInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\Config
     */
    private $config;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @param \Magento\NegotiableQuote\Model\Config $config
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\Config $config,
        \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
    ) {
        $this->config = $config;
        $this->validatorResultFactory = $validatorResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $data)
    {
        $result = $this->validatorResultFactory->create();
        if (empty($data['files'])) {
            return $result;
        }

        if (count($data['files']) > 10) {
            $result->addMessage(
                __("Cannot create the B2B quote. You cannot attach more than ten files per one request.")
            );
            return $result;
        }

        $allowedExtensions = $this->config->getAllowedExtensions()
            ? explode(',', $this->config->getAllowedExtensions())
            : [];
        $allowedSize = $this->config->getMaxFileSize();
        $allowedSizeInByte = $allowedSize * 1048576;
        $pattern = '/^[^~`!@#$%^&*()+={}[\]|;:"\',.?><\/\\\\\s]+[.]{1}[^~`!@#$%^&*()+={}[\]|;:"\',.?><\/\\\\\\s]+$/';
        /** @var \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface $file */
        foreach ($data['files'] as $file) {
            $fileExtension = pathinfo($file->getName(), PATHINFO_EXTENSION);
            if (!in_array($fileExtension, $allowedExtensions)) {
                $result->addMessage(
                    __(
                        "%fileType is not an allowed file type. Please select a different file.",
                        ['fileType' => $fileExtension]
                    )
                );
            }
            if (strlen($file->getName()) > 20 || !preg_match($pattern, $file->getName())) {
                $result->addMessage(
                    __(
                        "The maximum file name length is 20 characters. "
                        . "The only special symbols that are allowed in the file name are: dash and underscore. "
                        . "Row ID: %fieldName = %fieldValue",
                        [
                            'fieldName' => \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface::NAME,
                            'fieldValue' => $file->getName()
                        ]
                    )
                );
            }

            $content = base64_decode($file->getBase64EncodedData(), true);
            if (strlen($content) > $allowedSizeInByte) {
                $result->addMessage(
                    __(
                        "Cannot attach the file %filename. The maximum allowed file size is %size Mb.",
                        ['filename' => $file->getName(), 'size' => $allowedSize]
                    )
                );
            }
        }

        return $result;
    }
}
