<?php

namespace Magento\SalesRule\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

class OnePageCheckoutWithDiscountTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test, 3rd_party_test';
    /* end tags */

    /**
     * Runs one page checkout test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
