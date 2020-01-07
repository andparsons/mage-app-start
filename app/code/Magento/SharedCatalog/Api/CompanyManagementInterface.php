<?php
namespace Magento\SharedCatalog\Api;

/**
 * Shared catalog companies actions.
 * @api
 * @since 100.0.0
 */
interface CompanyManagementInterface
{
    /**
     * Return the list of company IDs for the companies assigned to the selected catalog.
     *
     * @param int $sharedCatalogId
     * @return string
     */
    public function getCompanies($sharedCatalogId);

    /**
     * Assign companies to a shared catalog.
     *
     * @param int $sharedCatalogId
     * @param \Magento\Company\Api\Data\CompanyInterface[] $companies
     * @return bool
     * @throws \Exception
     */
    public function assignCompanies($sharedCatalogId, array $companies);

    /**
     * Unassign companies from a shared catalog.
     *
     * @param int $sharedCatalogId
     * @param \Magento\Company\Api\Data\CompanyInterface[] $companies
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function unassignCompanies($sharedCatalogId, array $companies);

    /**
     * Unassign all companies from a shared catalog without validation.
     *
     * @param int $sharedCatalogId
     * @return bool
     * @throws \Exception
     */
    public function unassignAllCompanies($sharedCatalogId);
}
