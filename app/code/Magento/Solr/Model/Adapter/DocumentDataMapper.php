<?php

namespace Magento\Solr\Model\Adapter;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Solr\Model\Adapter\AbstractAdapter;
use Magento\Solr\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Solr\Model\Adapter\Document\Builder;
use Magento\Solr\Model\Adapter\FieldMapper;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class DocumentDataMapper
{
    /**
     * Defines text type fields
     *
     * @var string[]
     */
    protected $textFieldTypes = ['text', 'varchar'];

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var AttributeContainer
     */
    private $attributeContainer;

    /**
     * @var FieldMapper
     */
    private $fieldMapper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Builder $builder
     * @param AttributeContainer $attributeContainer
     * @param FieldMapper $fieldMapper
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Builder $builder,
        AttributeContainer $attributeContainer,
        FieldMapper $fieldMapper,
        DateTime $dateTime,
        TimezoneInterface $localeDate,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->builder = $builder;
        $this->attributeContainer = $attributeContainer;
        $this->fieldMapper = $fieldMapper;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare index data for using in search engine metadata.
     * Prepare fields for advanced search, navigation, sorting and fulltext fields for each search weight for
     * quick search and spell.
     *
     * @param array $productIndexData
     * @param int $productId
     * @param int $storeId
     * @param array $productPriceIndexData
     * @param array $productCategoryIndexData
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function map(
        array $productIndexData,
        $productId,
        $storeId,
        array $productPriceIndexData,
        array $productCategoryIndexData
    ) {
        // Define system data for engine internal usage
        $this->builder->addField('id', $productId);
        $this->builder->addField('store_id', $storeId);
        $this->builder->addField(AbstractAdapter::UNIQUE_KEY, $productId . '|' . $storeId);

        $fulltextData = [];
        $fulltextSpell = [];
        foreach ($productIndexData as $attributeId => $value) {
            $attributeCode = $this->attributeContainer->getAttributeCodeById($attributeId);

            if ($attributeCode === 'visibility') {
                $documentValue = $value[$productId];
                $this->builder->addField($attributeCode, $documentValue);
                continue;
            }

            if ($attributeCode === 'options') {
                $dataForIndex = is_array($value) ? implode(' ', $value) : $value;
                $fulltextData[1][] = $dataForIndex;
                $fulltextSpell[] = $dataForIndex;
            }

            // Prepare processing attribute info
            /* @var Attribute|null $attribute */
            $attribute = $this->attributeContainer->getSearchableAttribute($attributeCode);

            if (!$attribute || $attributeCode === 'price' || empty($value)) {
                continue;
            }

            $attribute->setStoreId($storeId);
            $preparedValue = '';
            // Preparing data for solr fields
            if ($attribute->getIsSearchable() ||
                $this->isAttributeUsedInAdvancedSearch($attribute) ||
                $attribute->getUsedForSortBy()
            ) {
                if ($attribute->usesSource()) {
                    $preparedValue = [];
                    if ('multiselect' === $attribute->getFrontendInput()) {
                        foreach ($value as $val) {
                            $preparedValue = array_merge($preparedValue, explode(',', $val));
                        }
                        $preparedNavValue = $preparedValue;
                    } else {
                        // safe condition
                        if (!is_array($value)) {
                            $preparedValue = [$value];
                        } else {
                            $preparedValue = array_unique($value);
                        }

                        $preparedNavValue = $preparedValue;
                        // Ensure that self product value will be saved after array_unique function for sorting purpose
                        if (isset($value[$productId]) && !isset($preparedNavValue[$productId])) {
                            $selfValueKey = array_search($value[$productId], $preparedNavValue);
                            unset($preparedNavValue[$selfValueKey]);
                            $preparedNavValue[$productId] = $value[$productId];
                        }
                    }

                    foreach ($preparedValue as $id => $val) {
                        $preparedValue[$id] = $attribute->getSource()->getIndexOptionText($val);
                    }
                } else {
                    $preparedValue = $value;
                    if ($attribute->getBackendType() === 'datetime') {
                        if (is_array($value)) {
                            $preparedValue = [];
                            foreach ($value as $id => &$val) {
                                $val = $this->formatDate($val);
                                if (!empty($val)) {
                                    $preparedValue[$id] = $val;
                                }
                            }
                            unset($val);
                            //clear link to value
                            $preparedValue = array_unique($preparedValue);
                        } else {
                            $preparedValue[$productId] = $this->formatDate($value);
                        }
                    }
                }
            }

            // Adding data for advanced search field (without additional prefix)
            if ($this->isAttributeUsedInAdvancedSearch($attribute)) {
                $fieldNameInEngine = $this->getSearchEngineFieldName($attribute);
                if ($fieldNameInEngine) {
                    if ($attribute->usesSource()) {
                        if (!empty($preparedNavValue)) {
                            $documentValue = $preparedNavValue;
                            $this->builder->addField($fieldNameInEngine, $documentValue);
                        }
                    } else {
                        if (!empty($preparedValue)) {
                            $documentValue = in_array($attribute->getBackendType(), $this->textFieldTypes)
                                ? implode(' ', (array)$preparedValue)
                                : $preparedValue;
                            $this->builder->addField($fieldNameInEngine, $documentValue);
                        }
                    }
                }
            }

            // Adding data for fulltext search field
            if ($attribute->getIsSearchable() && !empty($preparedValue)) {
                $searchWeight = $attribute->getSearchWeight();
                if ($searchWeight) {
                    $fulltextData[$searchWeight][] = is_array($preparedValue)
                        ? implode(' ', $preparedValue)
                        : $preparedValue;
                }
            }

            unset($preparedNavValue, $preparedValue, $fieldNameInEngine, $attribute);
        }

        $fulltextFieldName = $this->getAdvancedTextFieldName('fulltext');
        foreach ($fulltextData as $data) {
            $this->builder->addField($fulltextFieldName, $this->implodeIndexData($data));
            $fulltextSpell += $data;
        }
        unset($fulltextData, $fulltextFieldName);

        // Preparing field with spell info
        $fulltextSpell = array_unique($fulltextSpell);
        $fieldName = $this->getAdvancedTextFieldName('spell');
        $this->builder->addField($fieldName, $this->implodeIndexData($fulltextSpell));
        unset($fulltextSpell);

        $this->builder->addFields($this->getProductPriceData($productId, $storeId, $productPriceIndexData));
        $this->builder->addFields($this->getProductCategoryData($productId, $productCategoryIndexData));

        return $this->builder->build();
    }

    /**
     * @param Attribute $attribute
     * @return bool
     */
    private function isAttributeUsedInAdvancedSearch(Attribute $attribute)
    {
        return $attribute->getIsVisibleInAdvancedSearch()
        || $attribute->getIsFilterable()
        || $attribute->getIsFilterableInSearch();
    }

    /**
     * Retrieve date value in solr format (ISO 8601) with Z
     * Example: 1995-12-31T23:59:59Z
     *
     * @param string|null $date
     * @return string|null
     */
    protected function formatDate($date = null)
    {
        if ($this->dateTime->isEmptyDate($date)) {
            return null;
        }
        $dateObj = new \DateTime($date, new \DateTimeZone('UTC'));
        return $dateObj->format('c') . 'Z';
    }

    /**
     * Retrieve attribute field name
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param string $target
     * @return string|bool
     */
    protected function getSearchEngineFieldName(Attribute $attribute, $target = FieldMapperInterface::TYPE_FILTER)
    {
        return $this->fieldMapper->getFieldName($attribute->getAttributeCode(), ['type' => $target]);
    }

    /**
     * Prepare name for system text fields.
     *
     * @param string $field
     * @return string
     */
    protected function getAdvancedTextFieldName($field)
    {
        return substr(
            $this->fieldMapper->getFieldName(
                $field,
                [
                    'type' => FieldMapperInterface::TYPE_QUERY,
                ]
            ),
            5
        );
    }

    /**
     * Implode index array to string by separator
     * Support 2 level array gluing
     *
     * @param array $indexData
     * @param string $separator
     * @return string
     */
    protected function implodeIndexData($indexData, $separator = ' ')
    {
        if (!$indexData) {
            return '';
        }
        if (is_string($indexData)) {
            return $indexData;
        }

        $result = [];

        foreach ((array)$indexData as $value) {
            if (!is_array($value)) {
                $result[] = $value;
            } else {
                $result = array_merge($result, $value);
            }
        }
        $result = array_unique($result);

        return implode($separator, $result);
    }

    /**
     * Prepare price index for product
     *
     * @param int $productId
     * @param int $storeId
     * @param array $priceIndexData
     * @return array
     */
    protected function getProductPriceData($productId, $storeId, array $priceIndexData)
    {
        $result = [];

        if (array_key_exists($productId, $priceIndexData)) {
            $productPriceIndexData = $priceIndexData[$productId];

            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            foreach ($productPriceIndexData as $customerGroupId => $price) {
                $fieldName = $this->getPriceFieldName($customerGroupId, $websiteId);
                $result[$fieldName] = sprintf('%F', $price);
            }
        }

        return $result;
    }

    /**
     * Prepare category index data for product
     *
     * @param int $productId
     * @param array $categoryIndexData
     * @return array
     */
    protected function getProductCategoryData($productId, array $categoryIndexData)
    {
        $result = [];

        if (array_key_exists($productId, $categoryIndexData)) {
            $indexData = $categoryIndexData[$productId];

            $categoryIds = array_keys($indexData);
            if (count($categoryIds)) {
                $result = ['category_ids' => $categoryIds];

                foreach ($indexData as $categoryId => $position) {
                    $result['position_category_' . $categoryId] = $position;
                }
            }
        }

        return $result;
    }

    /**
     * Prepare price field name for search engine
     *
     * @param null|int $customerGroupId
     * @param null|int $websiteId
     * @return string
     */
    protected function getPriceFieldName($customerGroupId = null, $websiteId = null)
    {
        $context = [];
        if ($customerGroupId !== null) {
            $context['customerGroupId'] = $customerGroupId;
        }
        if ($websiteId !== null) {
            $context['websiteId'] = $websiteId;
        }

        return $this->fieldMapper->getFieldName('price', $context);
    }
}
