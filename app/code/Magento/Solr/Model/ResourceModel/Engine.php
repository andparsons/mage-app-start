<?php
namespace Magento\Solr\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;

/**
 * Search engine resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Engine implements EngineInterface
{
    const ATTRIBUTE_PREFIX = 'attr_';

    /**
     * Scope identifier
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return int[]
     */
    public function getAllowedVisibility()
    {
        return $this->catalogProductVisibility->getVisibleInSiteIds();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }

    /**
     * Prepare index array as a string glued by separator
     * Support 2 level array gluing
     *
     * @param array $index
     * @param string $separator
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $index;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return true;
    }
}
