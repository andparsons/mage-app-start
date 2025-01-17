<?php
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Resolver field name for position attribute.
 */
class Position implements ResolverInterface
{
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param StoreManager $storeManager
     * @param Registry $coreRegistry
     */
    public function __construct(
        StoreManager $storeManager = null,
        Registry $coreRegistry = null
    ) {
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManager::class);
        $this->coreRegistry = $coreRegistry ?: ObjectManager::getInstance()
            ->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if ($attribute->getAttributeCode() === 'position') {
            return 'position_category_' . $this->resolveCategoryId($context);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    private function resolveCategoryId($context)
    {
        if (isset($context['categoryId'])) {
            $id = $context['categoryId'];
        } else {
            $id = $this->coreRegistry->registry('current_category')
                ? $this->coreRegistry->registry('current_category')->getId()
                : $this->storeManager->getStore()->getRootCategoryId();
        }

        return $id;
    }
}
