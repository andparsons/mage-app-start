<?php
namespace Magento\Reports\Block\Product\Widget\Viewed;

/**
 * Reports Recently Viewed Products Widget
 *
 * @deprecated 100.3.3
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Viewed Product Index type
     *
     * @var string
     */
    protected $_indexType = \Magento\Reports\Model\Product\Index\Factory::TYPE_VIEWED;
}
