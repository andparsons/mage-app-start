<?php

declare(strict_types=1);

namespace Magento\Company\Api;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Manage customers assigned to companies.
 */
interface CompanyUserManagerInterface
{
    /**
     * Accept invitation to a company.
     *
     * @param string $invitationCode
     * @param CompanyCustomerInterface $customer
     * @param string|null $roleId If not explicit role provided a default one will be assigned.
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @return void
     */
    public function acceptInvitation(string $invitationCode, CompanyCustomerInterface $customer, ?string $roleId): void;

    /**
     * Send invitation to existing customer to join a company.
     *
     * @param CompanyCustomerInterface $forCustomer
     * @param string|null $roleId If not explicit role provided a default one will be assigned.
     * @throws LocalizedException
     * @return void
     */
    public function sendInvitation(CompanyCustomerInterface $forCustomer, ?string $roleId): void;
}
