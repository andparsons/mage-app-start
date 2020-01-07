<?php

namespace Magento\NegotiableQuote\Model\Attachment;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\NegotiableQuote\Model\Config as NegotiableQuoteConfig;

/**
 * Class for uploading comment attachments.
 */
class Uploader extends \Magento\Framework\Api\Uploader
{
    /**
     * @var NegotiableQuoteConfig
     */
    protected $negotiableQuoteConfig;

    /**
     * This number is used to convert Mbs in bytes.
     *
     *
     * @var int
     */
    private $defaultSizeMultiplier = 1048576;

    /**
     * Default file name length.
     *
     * @var int
     */
    private $defaultNameLength = 20;

    /**
     * @param NegotiableQuoteConfig $negotiableQuoteConfig
     * @throws \Exception
     */
    public function __construct(
        NegotiableQuoteConfig $negotiableQuoteConfig
    ) {
        parent::__construct();
        $this->negotiableQuoteConfig = $negotiableQuoteConfig;
    }

    /**
     * Validate size of file.
     *
     * @return bool
     */
    public function validateSize()
    {
        return isset($this->_file['size'])
        && $this->_file['size'] < $this->negotiableQuoteConfig->getMaxFileSize() * $this->defaultSizeMultiplier;
    }

    /**
     * Validate name length of file.
     *
     * @return bool
     */
    public function validateNameLength()
    {
        return mb_strlen($this->_file['name']) <= $this->defaultNameLength;
    }

    /**
     * Check is file has allowed extension.
     *
     * @inheritdoc
     */
    public function checkAllowedExtension($extension)
    {
        if (empty($this->_allowedExtensions)) {
            $configData = $this->negotiableQuoteConfig->getAllowedExtensions();
            $allowedExtensions = $configData ? explode(',', $configData) : [];
            $this->_allowedExtensions = $allowedExtensions;
        }
        return parent::checkAllowedExtension($extension);
    }

    /**
     * @inheritDoc
     */
    public static function getNewFileName($destinationFile)
    {
        /** @var Random $random */
        $random = ObjectManager::getInstance()->get(Random::class);

        return $random->getRandomString(32);
    }
}
