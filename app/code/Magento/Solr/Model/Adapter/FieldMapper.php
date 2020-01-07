<?php
namespace Magento\Solr\Model\Adapter;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Customer\Model\Session as CustomerSession;

/**
 * Class FieldMapper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FieldMapper implements FieldMapperInterface
{
    const ATTRIBUTE_PREFIX = 'attr_';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var array
     */
    protected $localesMapping = [
        'nn_NO' => 'nb',
    ];

    /**
     * @var array
     */
    protected $supportedLang = [
        'da',
        'nl',
        'en',
        'fi',
        'fr',
        'de',
        'it',
        'nb',
        'pt',
        'ro',
        'ru',
        'es',
        'sv',
        'tr',
        'cs',
        'el',
        'th',
        'zh',
        'ja',
        'ko'
    ];

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var FieldType
     */
    private $fieldType;

    /**
     * @param Config $eavConfig
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ResolverInterface $localeResolver
     * @param CustomerSession $customerSession
     * @param FieldType $fieldType
     * @param array $localesMapping
     * @param array $supportedLang
     */
    public function __construct(
        Config $eavConfig,
        Registry $registry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ResolverInterface $localeResolver,
        CustomerSession $customerSession,
        FieldType $fieldType,
        array $localesMapping = [],
        array $supportedLang = []
    ) {
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->localesSuffix = array_merge($this->localesMapping, $localesMapping);
        $this->supportedLang = array_merge($this->supportedLang, $supportedLang);
        $this->customerSession = $customerSession;
        $this->fieldType = $fieldType;
    }

    /**
     * Prepare language suffix for text fields.
     * For not supported languages prefix _def will be returned.
     *
     * @param array $context
     * @return string
     */
    public function getLanguageSuffix($context)
    {
        $localeCode = isset($context['localeCode'])
            ? $context['localeCode']
            : $this->scopeConfig->getValue(
                $this->localeResolver->getDefaultLocalePath(),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        if (isset($this->localesMapping[$localeCode])) {
            return $this->localesMapping[$localeCode];
        }
        $localeCode = substr($localeCode, 0, 2);
        if (in_array($localeCode, $this->supportedLang, true)) {
            return $localeCode;
        }
        return 'def';
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getFieldName($attributeCode, $context = [])
    {
        if (in_array($attributeCode, ['id', 'sku', 'visibility', 'store_id', 'category_ids'], true)) {
            return $attributeCode;
        }

        if ($attributeCode === 'price') {
            return $this->getPriceFieldName($context);
        }
        if ($attributeCode === 'position') {
            return $this->getPositionFiledName($context);
        }

        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);

        $fieldType = $this->fieldType->getFieldType($attribute);
        if (empty($context['type']) || $context['type'] === FieldMapperInterface::TYPE_FILTER) {
            if ($fieldType === 'string') {
                return $this->getFieldName(
                    $attributeCode,
                    array_merge($context, ['type' => FieldMapperInterface::TYPE_QUERY])
                );
            }
            $fieldName = self::ATTRIBUTE_PREFIX . $fieldType . '_' . $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_QUERY) {
            $languageSuffix = $this->getLanguageSuffix($context);
            if ($attributeCode === '*') {
                $fieldName = "fulltext_{$languageSuffix}";
            } else {
                $fieldName = self::ATTRIBUTE_PREFIX . $attributeCode . '_' . $languageSuffix;
            }
        } else {
            $fieldName = self::ATTRIBUTE_PREFIX . 'sort_' . $fieldType . '_' . $attributeCode;
        }

        return $fieldName;
    }

    /**
     * Get "position" field name
     *
     * @param array $context
     * @return string
     */
    private function getPositionFiledName($context)
    {
        if (isset($context['categoryId'])) {
            $category = $context['categoryId'];
        } else {
            $category = $this->coreRegistry->registry('current_category')
                ? $this->coreRegistry->registry('current_category')->getId()
                : $this->storeManager->getStore()->getRootCategoryId();
        }
        return 'position_category_' . $category;
    }

    /**
     * Prepare price field name for search engine
     *
     * @param array $context
     * @return string
     */
    private function getPriceFieldName($context)
    {
        $customerGroupId = !empty($context['customerGroupId'])
            ? $context['customerGroupId']
            : $this->customerSession->getCustomerGroupId();
        $websiteId = !empty($context['websiteId'])
            ? $context['websiteId']
            : $this->storeManager->getStore()->getWebsiteId();
        return 'price_' . $customerGroupId . '_' . $websiteId;
    }
}
