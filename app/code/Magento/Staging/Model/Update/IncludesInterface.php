<?php

namespace Magento\Staging\Model\Update;

/**
 * Interface IncludesInterface
 */
interface IncludesInterface
{
    /**
     * Retrieve SQL string for count statement
     *
     * @return \Zend_Db_Expr
     */
    public function getCountSql();

    /**
     * Retrieve fields for grouping entity includes
     *
     * @return array
     */
    public function getGroupByFields();
}
