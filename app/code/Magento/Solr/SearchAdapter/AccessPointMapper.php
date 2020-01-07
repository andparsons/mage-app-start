<?php

namespace Magento\Solr\SearchAdapter;

use Magento\Solr\Model\Adapter\FieldMapper;

class AccessPointMapper implements AccessPointMapperInterface
{
    const BASE_MAPPING_URL = 'magento';

    /**
     * @var FieldMapper
     */
    private $fieldMapper;

    /**
     * @param FieldMapper $fieldMapper
     */
    public function __construct(FieldMapper $fieldMapper)
    {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * Get handler name for context
     *
     * @param array $context
     * @return string
     */
    public function getHandler($context = [])
    {
        return self::BASE_MAPPING_URL . '_' . $this->fieldMapper->getLanguageSuffix($context);
    }
}
