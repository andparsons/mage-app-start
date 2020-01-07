<?php

namespace Magento\Solr\Model\Adapter\Document;

use Solarium\QueryType\Update\Query\Document\Document;

class Builder
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var Factory
     */
    private $documentFactory;

    /**
     * @param Factory $documentFactory
     */
    public function __construct(Factory $documentFactory)
    {
        $this->documentFactory = $documentFactory;
    }

    /**
     * @return Document
     */
    public function build()
    {
        $document = $this->documentFactory->create();
        foreach ($this->fields as $field => $value) {
            $this->addFieldToDocument($document, $field, $value);
        }
        $this->clear();
        return $document;
    }

    /**
     * @param Document $document
     * @param string $field
     * @param string|int|float $value
     * @return Document
     */
    private function addFieldToDocument(Document $document, $field, $value)
    {
        if (is_array($value)) {
            foreach ($value as $val) {
                if (!is_array($val)) {
                    $document->addField($field, $val);
                }
            }
        } else {
            $document->addField($field, $value);
        }
        return $document;
    }

    /**
     * @return void
     */
    private function clear()
    {
        $this->fields = [];
    }

    /**
     * @param string $field
     * @param string|array|int|float $value
     * @return $this
     */
    public function addField($field, $value)
    {
        $this->fields[$field] = $value;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function addFields(array $fields)
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }
}
