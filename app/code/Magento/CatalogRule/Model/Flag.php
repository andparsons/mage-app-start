<?php

namespace Magento\CatalogRule\Model;

/**
 * Flag indicates that some rules have changed but changes have not been applied yet.
 */
class Flag extends \Magento\Framework\Flag
{
    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = 'catalog_rules_dirty';
}
