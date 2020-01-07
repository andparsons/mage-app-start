<?php
namespace Magento\Solr\SearchAdapter;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Api\Search\DocumentInterface;

/**
 * Document Factory
 */
class DocumentFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @deprecated 100.1.0
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Search\EntityMetadata
     */
    private $entityMetadata;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Search\EntityMetadata $entityMetadata
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Search\EntityMetadata $entityMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->entityMetadata = $entityMetadata;
    }

    /**
     * Create Search Document instance
     *
     * @param mixed $rawDocument
     * @return \Magento\Framework\Api\Search\Document
     */
    public function create($rawDocument)
    {
        /** @var \Magento\Framework\Api\AttributeValue[] $fields */
        $attributes = [];
        $documentId = null;
        $entityId = $this->entityMetadata->getEntityId();
        foreach ($rawDocument->getFields() as $fieldName => $value) {
            if ($fieldName === $entityId) {
                $documentId = $value;
            } elseif ($fieldName === 'score') {
                $attributes[$fieldName] = new AttributeValue(
                    [
                        AttributeInterface::ATTRIBUTE_CODE => $fieldName,
                        AttributeInterface::VALUE => $value,
                    ]
                );
            }
        }
        return new Document(
            [
                DocumentInterface::ID => $documentId,
                CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $attributes,
            ]
        );
    }
}
