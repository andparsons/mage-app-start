<?php
namespace Magento\Framework\Setup\Declaration\Schema\Dto;

/**
 * Table DTO Element interface.
 *
 * This interface can be used for elements that hold tables, like constraints.
 */
interface TableElementInterface
{
    /**
     * Get table object.
     *
     * @return Table
     */
    public function getTable();
}
