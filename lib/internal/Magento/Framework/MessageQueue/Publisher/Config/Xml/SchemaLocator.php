<?php
namespace Magento\Framework\MessageQueue\Publisher\Config\Xml;

/**
 * Schema locator for etc/queue_publisher.xml
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $perFileSchema;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->schema = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/publisher.xsd');
        $this->perFileSchema = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/publisher.xsd');
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}