<?php
namespace Magento\Analytics\Model;

/**
 * Contain information about encrypted data.
 */
class EncodedContext
{
    /**
     * Encrypted string.
     *
     * @var string
     */
    private $content;

    /**
     * Initialization vector that was used for encryption.
     *
     * @var string
     */
    private $initializationVector;

    /**
     * @param string $content
     * @param string $initializationVector
     */
    public function __construct($content, $initializationVector = '')
    {
        $this->content = $content;
        $this->initializationVector = $initializationVector;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }
}
