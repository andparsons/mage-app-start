<?php

declare(strict_types=1);

namespace Magento\Company\Model\Action;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Thrown when impossible to invite customer to a company without confirmation.
 */
class InviteConfirmationNeededException extends LocalizedException
{
    /**
     * @var CustomerInterface
     */
    private $forCustomer;

    /**
     * @inheritDoc
     */
    public function __construct(Phrase $phrase, CustomerInterface $customer, \Exception $cause = null, int $code = 0)
    {
        parent::__construct($phrase, $cause, $code);
        $this->forCustomer = $customer;
    }

    /**
     * Customer to be invited.
     *
     * @return CustomerInterface
     */
    public function getForCustomer(): CustomerInterface
    {
        return $this->forCustomer;
    }
}
