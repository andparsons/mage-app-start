<?php
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

/**
 * Provides auto_increment flag for column.
 */
interface ColumnIdentityAwareInterface
{
    /**
     * Check whether element is auto incremental or not.
     *
     * @return array
     */
    public function isIdentity();
}
