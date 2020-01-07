<?php
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Modify query, add custom conditions
 */
interface QueryModifierInterface
{
    /**
     * Modify query
     *
     * @param Select $select
     * @return void
     */
    public function modify(Select $select);
}
