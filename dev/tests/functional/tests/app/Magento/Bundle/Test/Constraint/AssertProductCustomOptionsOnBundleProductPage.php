<?php

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductCustomOptionsOnProductPage;

/**
 * Assertion that commodity options are displayed correctly on bundle product page.
 */
class AssertProductCustomOptionsOnBundleProductPage extends AssertProductCustomOptionsOnProductPage
{
    /**
     * Flag for verify price data
     *
     * @var bool
     */
    protected $isPrice = false;
}
