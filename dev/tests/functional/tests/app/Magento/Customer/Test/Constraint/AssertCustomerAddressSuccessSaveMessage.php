<?php

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerAddressSuccessSaveMessage
 */
class AssertCustomerAddressSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_MESSAGE = 'You saved the address.';

    /**
     * Asserts that success message equals to expected message
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @return void
     */
    public function processAssert(CustomerAccountIndex $customerAccountIndex)
    {
        $successMessage = $customerAccountIndex->getMessages()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $successMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $successMessage
        );
    }

    /**
     * Returns success message if equals to expected message
     *
     * @return string
     */
    public function toString()
    {
        return 'Success address save message on customer account index page is correct.';
    }
}
