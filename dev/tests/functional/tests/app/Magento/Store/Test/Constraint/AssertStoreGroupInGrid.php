<?php

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreGroupInGrid
 * Assert that created Store Group can be found in Stores grid
 */
class AssertStoreGroupInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created Store Group can be found in Stores grid by name
     *
     * @param StoreIndex $storeIndex
     * @param StoreGroup $storeGroup
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex, StoreGroup $storeGroup)
    {
        $storeGroupName = $storeGroup->getName();
        $storeIndex->open()->getStoreGrid()->search(['group_title' => $storeGroupName]);
        \PHPUnit\Framework\Assert::assertTrue(
            $storeIndex->getStoreGrid()->isStoreExists($storeGroupName),
            'Store group \'' . $storeGroupName . '\' is not present in grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store Group is present in grid.';
    }
}
