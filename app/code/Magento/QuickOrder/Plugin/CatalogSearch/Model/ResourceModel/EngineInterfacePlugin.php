<?php
namespace Magento\QuickOrder\Plugin\CatalogSearch\Model\ResourceModel;

use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;

/**
 * Plugin class for CatalogSearch Index Engine Interface.
 * @see \Magento\CatalogSearch\Model\ResourceModel\EngineInterface
 */
class EngineInterfacePlugin
{
    /**
     * Adds to allowed visibility array value-identifier of not visible individually products.
     * This will add not visible individually products to catalog search fulltext index during reindex run,
     * thus products which has Visibility attribute value set to "Not visible individually" can be found using
     * product fulltext search.
     *
     * @param EngineInterface $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllowedVisibility(
        EngineInterface $subject,
        array $result
    ) {
        if (!in_array(Visibility::VISIBILITY_NOT_VISIBLE, $result, true)) {
            $result[] = Visibility::VISIBILITY_NOT_VISIBLE;
        }

        return $result;
    }
}
